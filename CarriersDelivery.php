<?php
/**
 * @author InformatiqueProg (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery;

use CarriersDelivery\Model\CarriersdeliveryAreascostskgQuery;
use CarriersDelivery\Model\CarriersdeliveryAreascostsQuery;
use CarriersDelivery\Model\CarriersdeliveryAreasQuery;
use CarriersDelivery\Model\CarriersdeliveryCarrier;
use CarriersDelivery\Model\CarriersdeliveryCarrierQuery;
use CarriersDelivery\Model\CarriersdeliveryPackingcostsQuery;
use CarriersDelivery\Model\CarriersdeliveryProductQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;
use Thelia\Core\Install\Database;
use Thelia\Log\Tlog;
use Thelia\Model\Address;
use Thelia\Model\AddressQuery;
use Thelia\Model\Admin;
use Thelia\Model\Cart;
use Thelia\Model\Country;
use Thelia\Model\Customer;
use Thelia\Model\ModuleQuery;
use Thelia\Model\OrderPostage;
use Thelia\Model\TaxRuleQuery;
use Thelia\Module\BaseModule;
use Thelia\Module\DeliveryModuleInterface;
use Thelia\Module\Exception\DeliveryException;
use Thelia\TaxEngine\Calculator;
use Thelia\Tools\I18n;
use ZipCode\ZipCode;

class CarriersDelivery extends BaseModule implements DeliveryModuleInterface
{
    const DOMAIN_NAME = 'carriersdelivery';

    const CONFIG_TRACKING_URL = 'carriersdelivery_tracking_url';
    const CONFIG_TAX_RULE_ID = 'carriersdelivery_taxe_rule';
    const DEFAULT_TRACKING_URL = '%ID%';
    const DEFAULT_TAX_RULE_ID = 0;

    protected $logIsActive = true;

    /**
     * @param $infos
     * @return bool
     */
    protected function saveLog($infos)
    {
        if ($this->logIsActive) {
            $log = Tlog::getNewInstance();

            $logFilePath = $this->getLogFilePath();

            $log->setPrefix("#LEVEL: #DATE #HOUR: ");
            $log->setDestinations("\\Thelia\\Log\\Destination\\TlogDestinationFile");
            $log->setConfig("\\Thelia\\Log\\Destination\\TlogDestinationFile", 0, $logFilePath);
            $log->setLevel(Tlog::INFO);
            $log->info(print_r($infos, true));

            return true;
        }

        return false;
    }

    /**
     * @return string The path to the module's log file.
     */
    protected function getLogFilePath()
    {
        return sprintf(THELIA_ROOT . 'log' . DS . '%s.log', strtolower($this->getModuleCode()));
    }

    /**
     * @param ConnectionInterface|null $con
     */
    public function postActivation(ConnectionInterface $con = null): void
    {
        try {
            if (null === self::getConfigValue(self::CONFIG_TRACKING_URL, null)) {
                self::setConfigValue(self::CONFIG_TRACKING_URL, self::DEFAULT_TRACKING_URL);
            }
            if (null === self::getConfigValue(self::CONFIG_TAX_RULE_ID, null)) {
                self::setConfigValue(self::CONFIG_TAX_RULE_ID, self::DEFAULT_TAX_RULE_ID);
            }

            CarriersdeliveryAreasQuery::create()->findOne();
        } catch (\Exception $e) {
            $database = new Database($con);

            $database->insertSql(null, [__DIR__ . DS . 'Config' . DS . 'thelia.sql']);
        }
    }

    /**
     * @param Country $country
     * @throws DeliveryException
     * @return float
     */
    public function getPostage(Country $country): \Thelia\Model\OrderPostage|float
    {
        $cart = $this->getRequest()->getSession()->getSessionCart($this->getDispatcher());

        $result = $this->getSlicePostage($cart);

        $this->getRequest()->getSession()->set('CarrierDeliveryPostageResult', $result);

        if (!isset($result['orderPostage'])) {
            throw new DeliveryException();
        }

        return $result['orderPostage'];
    }

    /**
     * @return bool
     */
    public function handleVirtualProductDelivery(): bool
    {
        return true;
    }

    /**
     * @param Country $country
     * @return bool
     */
    public function isValidDelivery(Country $country): bool
    {
        $cart = $this->getRequest()->getSession()->getSessionCart($this->getDispatcher());

        $result = $this->getSlicePostage($cart);

        if (!isset($result['orderPostage'])) {
            return false;
        }

        return true;
    }

    /**
     * @param $number
     * @return float
     */
    public static function sanitizeNumber($number)
    {
        $number = str_replace(' ', '', $number);
        $number = str_replace(',', '.', $number);
        // $number = floatval($number);

        return $number;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public static function checkCarrierId($id = 0)
    {
        $id = intval($id);

        if (!empty($id) && $id > 0) {
            $count = CarriersdeliveryCarrierQuery::create()->filterById($id)->count();

            if ($count === 1) {
                return true;
            }
        }

        throw new \Exception(
            Translator::getInstance()->trans('Carrier not found!', [], 'carriersdelivery.bo.default')
        );
    }

    /**
     * @param $carrier_area_id
     * @param $weight
     * @param $unit_per_kg
     * @return float|false
     */
    public static function getCostForArea($carrier_area_id, $weight, $unit_per_kg)
    {
        $areacost = CarriersdeliveryAreascostsQuery::create()
            ->filterByCarrierareaId($carrier_area_id)
            ->filterByWeightMax($weight, Criteria::GREATER_EQUAL)
            ->orderByWeightMax()
            ->findOne();

        if ($areacost) {
            $price = $areacost->getCost();

            return floatval($price);
        }

        $areacostkg = CarriersdeliveryAreascostskgQuery::create()
            ->filterByCarrierareaId($carrier_area_id)
            ->filterByWeightMax($weight, Criteria::GREATER_EQUAL)
            ->orderByWeightMax()
            ->findOne();

        if ($areacostkg) {
            if (empty($unit_per_kg)) {
                return false;
            }

            $price = floatval($areacostkg->getCost()) / $unit_per_kg * $weight;

            return $price;
        }

        return false;
    }

    /**
     * @param Request $request
     * @return null|Address
     */
    public static function getCartDeliveryAddress(Request $request)
    {
        $address = null;

        $session = $request->getSession();

        /** @var Customer|null $customerUser */
        $customerUser = $session->getCustomerUser();

        /** @var Admin|null $adminUser */
        $adminUser = $session->getAdminUser();

        if ($adminUser || ($customerUser && $customerUser->getReseller() == 1)) {
            $country_id = $request->query->getInt('country');

            $zipcode = $request->query->getDigits('zipcode');

            if (!empty($country_id) && !empty($zipcode)) {
                $address = new Address();

                $address
                    ->setCountryId($country_id)
                    ->setZipcode($zipcode);
            }
        }

        if (null === $address && null !== $customerUser) {
            if (null !== $session->getOrder()
                && null !== $session->getOrder()->getChoosenDeliveryAddress()
                && null !== $currentDeliveryAddress = AddressQuery::create()->findPk($session->getOrder()->getChoosenDeliveryAddress())
            ) {
                $address = $currentDeliveryAddress;
            } else {
                $address = AddressQuery::create()
                    ->filterByCustomerId($customerUser->getId())
                    ->filterByIsDefault(1)
                    ->findOne();
            }
        }

        return $address;
    }

    /**
     * @return array
     */
    public static function getConfig()
    {
        $config = [
            'url' => self::getConfigValue(self::CONFIG_TRACKING_URL, self::DEFAULT_TRACKING_URL),
            'tax' => intval(self::getConfigValue(self::CONFIG_TAX_RULE_ID, self::DEFAULT_TAX_RULE_ID)),
        ];

        return $config;
    }

    /**
     * @param Address $address
     * @return string $department
     * @throws PropelException
     * @throws \Exception
     */
    public function getDepartmentForAddress(Address $address)
    {
        $countryIsoalpha2 = $address->getCountry()->getIsoalpha2();

        $data = [
            'country' => $countryIsoalpha2,
            'maxRows' => 10,
            'postalcode' => $address->getZipcode(),
            'username' => ZipCode::getConfigValue('geonames_username'),
        ];

        $baseUrl = 'https://secure.geonames.org/postalCodeSearchJSON?' . http_build_query($data);

        $jsonResponse = file_get_contents($baseUrl);

        // Tlog::getInstance()->error(print_r($jsonResponse, true));

        $decodeJson = json_decode($jsonResponse);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(__FUNCTION__ . ' / Error Json decode');
        }

        if (!isset($decodeJson->postalCodes[0]->adminCode2)) {
            throw new \Exception(__FUNCTION__ . ' / Error No result');
        }

        /* Carriers don't use admin code in Belgium */
        if ($countryIsoalpha2 == 'BE') {
            $department = substr($decodeJson->postalCodes[0]->postalCode, 0, 2);
        } else {
            $department = $decodeJson->postalCodes[0]->adminCode2;
        }

        return $department;
    }

    /**
     * @return mixed
     */
    public static function getModuleId(): int
    {
        $module = ModuleQuery::create()->findOneByCode('CarriersDelivery');

        if ($module) {
            return $module->getId();
        }

        return null;
    }

    /**
     * @param Cart $cart
     * @return array[debug, orderPostage]
     */
    public function getSlicePostage(Cart $cart)
    {
        try {
            $debug = ['**START**'];

            $config = self::getConfig();

            if ($cart->countCartItems() == 0) {
                throw new \Exception('Cart EMPTY!');
            }

            if (null === $address = $this->getCartDeliveryAddress($this->getRequest())) {
                throw new \Exception('No Address FOUNDED!');
            }

            $debug[] = 'Country=' . $address->getCountryId() . ':ZipCode=' . $address->getZipcode();

            $department = $this->getDepartmentForAddress($address);

            if (empty($department)) {
                throw new \Exception('Department EMPTY!');
            }

            $debug[] = '$department:' . $department;

            $carrierIdsAreaIds = CarriersdeliveryAreasQuery::create()
                ->filterByDepartment($department)
                ->useCarriersdeliveryCarrierQuery()
                ->filterByCountryId($address->getCountryId())
                ->endUse()
                ->find()
                ->toKeyValue('CarrierId', 'Id');

            if (empty($carrierIdsAreaIds)) {
                throw new \Exception('No carrierIdsAreaIds FOUNDED!');
            }

            $debug[] = '$carrierIdsAreaIds:' . print_r($carrierIdsAreaIds, true);

            $carriers = CarriersdeliveryCarrierQuery::create()
                ->filterById(array_keys($carrierIdsAreaIds))
                ->find()
                ->toArray('Id');

            $debug[] = '$carriers:' . print_r($carriers, true);

            $weightsByProductId = [];

            foreach ($cart->getCartItems() as $cartItem) {
                if (!array_key_exists($cartItem->getProductId(), $weightsByProductId)) {
                    $weightsByProductId[$cartItem->getProductId()] = 0;
                }

                $itemWeight = $cartItem->getProductSaleElements()->getWeight();
                $itemWeight *= $cartItem->getQuantity();

                $weightsByProductId[$cartItem->getProductId()] += $itemWeight;
            }

            ksort($weightsByProductId);

            $debug[] = '$weightsByProductId:' . print_r($weightsByProductId, true);

            $productsIds = array_keys($weightsByProductId);

            $resultProductsCarriers = CarriersdeliveryProductQuery::create()
                ->filterByProductId($productsIds)
                ->filterByCarrierId(array_keys($carrierIdsAreaIds))
                ->orderByProductId()
                ->find()
                ->toArray();

            // $debug[] = '$resultProductsCarriers:' . print_r($resultProductsCarriers, true);

            $productsCarriers = [];

            foreach ($resultProductsCarriers as $productCarrier) {
                $carrierId = $productCarrier['CarrierId'];
                $productId = $productCarrier['ProductId'];

                if (!array_key_exists($productId, $productsCarriers)) {
                    $productsCarriers[$productId] = [];
                }

                $productsCarriers[$productId][] = $carrierId;
            }

            $debug[] = '$productsCarriers:' . print_r($productsCarriers, true);

            $cases = [
                0 => [],
            ];

            foreach ($productsIds as $productId) {
                if (!array_key_exists($productId, $productsCarriers)) {
                    throw new \Exception('ProductId:' . $productId . ': has no Carrier!');
                }

                $prevCases = $cases;

                $cases = [];

                foreach ($productsCarriers[$productId] as $carrierId) {
                    foreach ($prevCases as $prevCase) {
                        $tmpCase = $prevCase;

                        if (!array_key_exists($carrierId, $tmpCase)) {
                            $tmpCase[$carrierId] = [$productId];
                        } else {
                            $tmpCase[$carrierId][] = $productId;
                        }

                        $cases[] = $tmpCase;
                    }
                }
            }

            // $debug[] = '$cases:' . print_r($cases, true);

            $casesPostages = [];

            foreach ($cases as $case) {
                $postage = 0;

                $combination = '';

                foreach ($case as $carriedId => $productsIds) {
                    $carrierProductsWeight = 0;

                    foreach ($productsIds as $productId) {
                        $carrierProductsWeight += $weightsByProductId[$productId];
                    }

                    $combination .= ' Carr_' . $carriedId . ':Weight_' . $carrierProductsWeight . 'Kg:Products_' . implode('_', $productsIds);

                    $cost = CarriersDelivery::getCostForArea($carrierIdsAreaIds[$carriedId], $carrierProductsWeight, $carriers[$carriedId]['UnitPerKg']);

                    if (false !== $cost) {
                        $carrierPostage = CarriersdeliveryCarrier::calculatePrice($cost, $carriers[$carriedId]['FeesCost'], $carriers[$carriedId]['DieselTaxPercent']);

                        $postage += $carrierPostage + CarriersdeliveryPackingcostsQuery::getCostForWeight($carrierProductsWeight);
                    } else {
                        $postage = null;

                        break;
                    }

                }

                $casesPostages[$combination] = $postage;
            }

            $debug[] = '$casesPostages:' . print_r($casesPostages, true);

            $minPostage = null;

            $minPostageCombination = '';

            foreach ($casesPostages as $combination => $postage) {
                if (!empty($postage) && (is_null($minPostage) || $minPostage > $postage)) {
                    $minPostage = $postage;

                    $minPostageCombination = $combination;
                }
            }

            $debug[] = '$minPostageCombination:' . $minPostageCombination . '=>' . $minPostage;

            $orderPostage = new OrderPostage();
            $orderPostage->setAmount($minPostage);
            $orderPostage->setAmountTax(0);

            if (0 !== $config['tax']) {
                $taxRuleI18N = I18n::forceI18nRetrieving(
                    $this->getRequest()->getSession()->getLang()->getLocale(),
                    'TaxRule',
                    $config['tax']
                );

                $taxRule = TaxRuleQuery::create()->findPk($config['tax']);

                if (null !== $taxRule) {
                    $taxCalculator = new Calculator();
                    $taxCalculator->loadTaxRuleWithoutProduct($taxRule, $address->getCountry());
                    $orderPostage->setAmount(
                        round($taxCalculator->getTaxedPrice($minPostage), 2)
                    );
                    $orderPostage->setAmountTax($orderPostage->getAmount() - $minPostage);
                    $orderPostage->setTaxRuleTitle($taxRuleI18N->getTitle());
                }
            }

            /* RESULT */
            $result = [
                'debug' => $debug,
                'orderPostage' => $orderPostage,
            ];
        } catch (\Exception $e) {
            $debug[] = $e->getMessage() . '  **Exit**';

            $result = [
                'debug' => $debug,
                'orderPostage' => null,
            ];
        }

        $this->saveLog($result);

        return $result;
    }
}
