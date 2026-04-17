<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\EventListeners;

use CarriersDelivery\CarriersDelivery;
use CarriersDelivery\Model\CarriersdeliveryOrder;
use CarriersDelivery\Model\CarriersdeliveryProductQuery;
use CarriersDelivery\Model\CarriersdeliveryCarrierQuery;
use CarriersDelivery\Model\CarriersdeliveryProduct;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\Product\ProductEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\TheliaFormEvent;
use Thelia\Core\Translation\Translator;

class CarriersDeliveryListener implements EventSubscriberInterface
{

    protected $requestStack;

    /**
     * CarriersDeliveryListener constructor.
     * @param Translator $translator
     * @param RequestStack $requestStack
     */
    public function __construct(Translator $translator, RequestStack $requestStack)
    {
        $this->translator = $translator;

        $this->requestStack = $requestStack;
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            // TheliaEvents::FORM_BEFORE_BUILD . '.thelia_product_creation'        => ['addFieldToForm', 128],
            TheliaEvents::FORM_BEFORE_BUILD . '.thelia_product_modification'    => ['addFieldToForm', 128],

            // TheliaEvents::PRODUCT_CREATE         => ['processProductField', 100],
            TheliaEvents::PRODUCT_UPDATE            => ['processProductField', 100],

            TheliaEvents::ORDER_BEFORE_PAYMENT      => 'savePostageLogInTable',
        ];
    }

    /**
     * Add carrier_id field to form
     * @param TheliaFormEvent $event
     */
    public function addFieldToForm(TheliaFormEvent $event)
    {
        $formData = $event->getForm()->getFormBuilder()->getData();

        $data = [];

        if (isset($formData['id'])) {
            $product_id = intval($formData['id']);

            if (!empty($product_id)) {
                $productCarriersList = CarriersdeliveryProductQuery::create()
                    ->filterByProductId($product_id)
                    ->find()
                    ->toKeyValue('CarrierId', 'ProductId');

                if (!empty($productCarriersList)) {
                    $data = array_keys($productCarriersList);
                }
            }
        }

        $carriersList = CarriersdeliveryCarrierQuery::create()
            ->orderByName()
            ->find()
            ->toKeyValue('Id', 'Name');

        $event->getForm()->getFormBuilder()->add(
            'carrier_id',
            'choice',
            [
                'choices'   => $carriersList,
                'data'      => $data,
                'label'     => Translator::getInstance()->trans('Choose a carrier', [], 'carriersdelivery.bo.default'),
                'multiple'  => true,
                'required'  => false,
            ]
        );
    }

    /**
     * set carrier_id for product
     * @param ProductEvent $event
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function processProductField(ProductEvent $event)
    {
        if ($event->hasProduct() && is_array($event->carrier_id)) {
            $carrierListTodo = $event->carrier_id;

            $carrierListDone = [];

            $rows = CarriersdeliveryProductQuery::create()
                ->filterByProductId($event->getProduct()->getId())
                ->find()
                ->toArray();

            foreach ($rows as $carriersdelivery_product) {
                $alreadyExist = false;

                foreach ($carrierListTodo as $carrier_id) {
                    if ($carrier_id == $carriersdelivery_product['CarrierId']) {
                        $alreadyExist = true;

                        break;
                    }
                }

                if (!$alreadyExist) {
                    CarriersdeliveryProductQuery::create()
                        ->filterByProductId($event->getProduct()->getId())
                        ->filterByCarrierId($carriersdelivery_product['CarrierId'])
                        ->delete();
                } else {
                    $carrierListDone[] = $carriersdelivery_product['CarrierId'];
                }
            }

            foreach ($carrierListTodo as $carrier_id) {
                if (!in_array($carrier_id, $carrierListDone)) {
                    $carriersdelivery_product = new CarriersdeliveryProduct();

                    $carriersdelivery_product
                        ->setProductId($event->getProduct()->getId())
                        ->setCarrierId($carrier_id)
                        ->save();
                }
            }
        }
    }

    /**
     * @param OrderEvent $orderEvent
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function savePostageLogInTable(OrderEvent $orderEvent)
    {
        if ($orderEvent->getOrder()->getDeliveryModuleId() === CarriersDelivery::getModuleId()) {
            $postageResult = $this->requestStack->getCurrentRequest()->getSession()->get('CarrierDeliveryPostageResult', null);

            $orderId = $orderEvent->getOrder()->getId();

            if (isset($postageResult['debug']) && !empty($orderId)) {
                $carrierDeliveryOrder = new CarriersdeliveryOrder();

                $carrierDeliveryOrder
                    ->setOrderId($orderId)
                    ->setPostageLog(print_r($postageResult['debug'], true))
                    ->save();

                $this->requestStack->getCurrentRequest()->getSession()->set('CarrierDeliveryPostageResult', null);
            }
        }
    }
}