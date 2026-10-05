<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Controller\Back;


use CarriersDelivery\CarriersDelivery;
use CarriersDelivery\Model\CarriersdeliveryAreascostskg;
use CarriersDelivery\Model\CarriersdeliveryAreascostskgQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Translation\Translator;

class AreacostkgController extends BaseAdminController
{
    /**
     * @param int $id
     * @return null|\CarriersDelivery\Model\CarriersdeliveryAreascostskg|mixed
     */
    protected function getExistingObject($id = 0)
    {
        $id = intval($id);

        if (!empty($id) && $id > 0) {
            $areacost = CarriersdeliveryAreascostskgQuery::create()->findPk($id);

            return $areacost;
        }

        return false;
    }

    /**
     * @return mixed|\Thelia\Core\HttpFoundation\Response
     */
    public function createAction()
    {
        if (null !== $response = $this->checkAuth([], ['CarriersDelivery'], AccessManager::CREATE)) {
            return $response;
        }

        $form = $this->createForm('carriersdelivery_areacostkg_create');

        try {
            $dataForm = $this->validateForm($form, 'POST');

            $carrier_id = intval($dataForm->get('carrier_id')->getData());

            CarriersDelivery::checkCarrierId($carrier_id);

            $weight_max = (string)$dataForm->get('weight_max')->getData();

            $costskgByWeight = CarriersdeliveryAreascostskgQuery::getCostskgByWeightForCarrier($carrier_id);

            foreach ($costskgByWeight['carrier_areas'] as $carrierarea_id => $carrier_area_name) {
                if (array_key_exists($carrierarea_id, $costskgByWeight['areasWeights'])) {
                    if (array_key_exists($weight_max, $costskgByWeight['areasWeights'][$carrierarea_id])) {
                        // cost already exist for this carrier area and weight
                        continue;
                    }
                }

                $areacostkg = new CarriersdeliveryAreascostskg();

                $areacostkg
                    ->setCarrierareaId($carrierarea_id)
                    ->setWeightMax($weight_max)
                    ->setCost(0)
                    ->save();
            }

            $this->getSession()->getFlashBag()->add('success', Translator::getInstance()->trans('Added!', [], 'carriersdelivery.bo.default'));
        } catch (\Exception $e) {
            $this->getSession()->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->generateSuccessRedirect($form);
    }

    /**
     * @return mixed|\Thelia\Core\HttpFoundation\Response
     */
    public function deleteAction()
    {
        if (null !== $response = $this->checkAuth([], ['CarriersDelivery'], AccessManager::DELETE)) {
            return $response;
        }

        $this->getTokenProvider()->checkToken((string) $this->getRequest()->request->get('_token'));

        $carrier_id = $this->getRequest()->request->getInt('carrier_id');

        try {
            $weight = $this->getRequest()->request->get('weight');

            if (CarriersDelivery::checkCarrierId($carrier_id)) {
                $toDelete = CarriersdeliveryAreascostskgQuery::create()
                    ->filterByWeightMax($weight)
                    ->useCarriersdeliveryAreasQuery()
                        ->filterByCarrierId($carrier_id)
                    ->endUse()
                    ->find();

                if ($toDelete->count() >= 1) {
                    $toDelete->delete();
                }

                $this->getSession()->getFlashBag()->add('success', Translator::getInstance()->trans('%nbr Area cost deleted!', ['%nbr' => $toDelete->count()], 'carriersdelivery.bo.default'));
            } else {
                $this->getSession()->getFlashBag()->add('danger', Translator::getInstance()->trans('Carrier not found!', [], 'carriersdelivery.bo.default'));
            }
        } catch (\Exception $e) {
            $this->getSession()->getFlashBag()->add('danger', $e->getMessage());
        }

        $url = $this->getRouteFromRouter('router.carriersdelivery', 'carriersdelivery.admin.carrier.areas', ['carrier_id' => $carrier_id]);

        return $this->generateRedirect($url);
    }

    /**
     * @return mixed|\Thelia\Core\HttpFoundation\Response
     * @throws \Exception
     */
    public function updateAction()
    {
        if (null !== $response = $this->checkAuth([], ['CarriersDelivery'], AccessManager::UPDATE)) {
            return $response;
        }

        $this->getTokenProvider()->checkToken((string) $this->getRequest()->request->get('_token'));

        $carrier_id = $this->getRequest()->request->getInt('carrier_id');

        $areascostsJson = $this->getRequest()->request->get('areascostskg');

        $areascosts = json_decode($areascostsJson);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON DECODE ERROR!');
        }

        try {
            $count = 0;

            foreach ($areascosts as $areacost) {
                CarriersdeliveryAreascostskgQuery::create()
                    ->filterById($areacost->id)
                    ->update([
                        'Cost' => CarriersDelivery::sanitizeNumber($areacost->cost),
                    ]);

                $count++;
            }

            $this->getSession()->getFlashBag()->add('success', Translator::getInstance()->trans('%nbr Area cost by kg updated!', ['%nbr' => $count], 'carriersdelivery.bo.default'));
        } catch (\Exception $e) {
            $this->getSession()->getFlashBag()->add('danger', $e->getMessage());
        }

        $url = $this->getRouteFromRouter('router.carriersdelivery', 'carriersdelivery.admin.carrier.areas', ['carrier_id' => $carrier_id]);

        return $this->generateRedirect($url);
    }
}