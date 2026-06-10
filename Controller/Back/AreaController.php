<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Controller\Back;


use CarriersDelivery\CarriersDelivery;
use CarriersDelivery\Form\AreacostCreateForm;
use CarriersDelivery\Form\AreacostkgCreateForm;
use CarriersDelivery\Form\AreaCreateForm;
use CarriersDelivery\Model\CarriersdeliveryAreas;
use CarriersDelivery\Model\CarriersdeliveryAreascostskgQuery;
use CarriersDelivery\Model\CarriersdeliveryAreascostsQuery;
use CarriersDelivery\Model\CarriersdeliveryAreasQuery;
use CarriersDelivery\Model\CarriersdeliveryCarrierQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Translation\Translator;

class AreaController extends BaseAdminController
{

    /**
     * @param int $id
     * @return null|\CarriersDelivery\Model\CarriersdeliveryAreas|mixed
     */
    protected function getExistingObject($id = 0)
    {
        $id = intval($id);

        if (!empty($id) && $id > 0) {
            /** @var CarriersdeliveryAreas $area */
            $area = CarriersdeliveryAreasQuery::create()->findPk($id);

            return $area;
        }

        return null;
    }

    /**
     * @param $carrier_id
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function listAction($carrier_id)
    {
        if (null !== $response = $this->checkAuth([], ['CarriersDelivery'], AccessManager::VIEW)) {
            return $response;
        }

        $costsByWeight = CarriersdeliveryAreascostsQuery::getCostsByWeightForCarrier($carrier_id);

        $costskgByWeight = CarriersdeliveryAreascostskgQuery::getCostskgByWeightForCarrier($carrier_id);

        $carrier = CarriersdeliveryCarrierQuery::create()->findPk($carrier_id);

        $areaCreateForm = $this->createForm(AreaCreateForm::getName(), 'form', ['carrier_id' => $carrier_id]);
        $areacostCreateForm = $this->createForm(AreacostCreateForm::getName(), 'form', ['carrier_id' => $carrier_id]);
        $areacostkgCreateForm = $this->createForm(AreacostkgCreateForm::getName(), 'form', ['carrier_id' => $carrier_id]);

        $args = [
            'carrier_id'            => $carrier_id,
            'carrier_name'          => $carrier ? $carrier->getName() : '',
            'carrier_diesel_tax'    => $carrier ? (float) $carrier->getDieselTaxPercent() : 0.0,
            'carrier_fees_cost'     => $carrier ? (float) $carrier->getFeesCost() : 0.0,
            'carrier_unit_per_kg'   => $carrier ? (float) $carrier->getUnitPerKg() : 0.0,
            'carrier_areas'         => $costsByWeight['carrier_areas'],
            'costAreasWeights'      => $costsByWeight['areasWeights'],
            'costDistinctWeights'   => $costsByWeight['distinctWeights'],
            'costkgAreasWeights'    => $costskgByWeight['areasWeights'],
            'costkgDistinctWeights' => $costskgByWeight['distinctWeights'],
            'area_create_form'      => $areaCreateForm->getForm()->createView(),
            'areacost_create_form'  => $areacostCreateForm->getForm()->createView(),
            'areacostkg_create_form' => $areacostkgCreateForm->getForm()->createView(),
        ];

        return $this->render('carriersdelivery-areas-list', $args);
    }

    /**
     * @return mixed|\Thelia\Core\HttpFoundation\Response
     */
    public function createAction()
    {
        if (null !== $response = $this->checkAuth([], ['CarriersDelivery'], AccessManager::CREATE)) {
            return $response;
        }

        $form = $this->createForm('carriersdelivery_area_create');

        try {
            $dataForm = $this->validateForm($form, 'POST');

            /** @var CarriersdeliveryAreas() $area */
            $area = new CarriersdeliveryAreas();

            $area
                ->setName($dataForm->get('name')->getData())
                ->setCarrierId($dataForm->get('carrier_id')->getData())
                ->setDepartments($dataForm->get('departments')->getData())
                ->save();

            $this->getSession()->getFlashBag()->add('success', Translator::getInstance()->trans('Area added!', [], 'carriersdelivery.bo.default'));

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(get_class($form), $e->getMessage(), $form, $e);
        }

        return $this->listAction();
    }

    /**
     * @return mixed|\Thelia\Core\HttpFoundation\Response
     */
    public function editAction()
    {
        if (null !== $response = $this->checkAuth([], ['CarriersDelivery'], AccessManager::UPDATE)) {
            return $response;
        }

        $area = null;
        $baseForm = null;

        if ($this->getRequest()->isMethod('POST')) {
            $form = $this->createForm('carriersdelivery_area_edit');
            $baseForm = $form;

            try {
                $editForm = $this->validateForm($form);

                $area_id = $editForm->get('id')->getData();

                if (null === $area = $this->getExistingObject($area_id)) {
                    throw new \LogicException(sprintf("area id *%d* does not exist", $area_id));
                }

                $area
                    ->setName($editForm->get('name')->getData())
                    ->setDepartments($editForm->get('departments')->getData())
                    ->save();

                if ($this->getRequest()->get('save_mode') != 'stay') {
                    return $this->generateSuccessRedirect($form);
                }

                $this->getParserContext()->addForm($form);
            } catch (\Exception $e) {
                $this->setupFormErrorContext(get_class($form), $e->getMessage(), $form, $e);

                $area_id = $form->getForm()->get('id')->getData();
            }
        } else {
            $area_id = $this->getRequest()->query->getInt('id');

            if (null === $area = $this->getExistingObject($area_id)) {
                throw new \LogicException(sprintf("area id *%d* does not exist", $area_id));
            }

            $data = [
                'id'            => $area->getId(),
                'name'          => $area->getName(),
                'carrier_id'    => $area->getCarrierId(),
                'departments'   => $area->getDepartments(),
            ];

            $editForm = $this->createForm('carriersdelivery_area_edit', 'form', $data);
            $baseForm = $editForm;

            $this->getParserContext()->addForm($editForm);
        }

        $args = [
            'area_id'       => $area_id,
            'carrier_id'    => $area ? $area->getCarrierId() : null,
            'edit_form'     => $baseForm !== null ? $baseForm->getForm()->createView() : null,
        ];

        return $this->render('carriersdelivery-area-edit', $args);
    }

    /**
     * @return mixed|\Thelia\Core\HttpFoundation\Response
     */
    public function deleteAction()
    {
        if (null !== $response = $this->checkAuth([], ['CarriersDelivery'], AccessManager::DELETE)) {
            return $response;
        }

        try {
            $area_id = $this->getRequest()->request->get('area_id');

            if (null !== $area = $this->getExistingObject($area_id)) {
                $area->delete();

                $this->getSession()->getFlashBag()->add('success', Translator::getInstance()->trans('Area deleted!', [], 'carriersdelivery.bo.default'));
            } else {
                $this->getSession()->getFlashBag()->add('danger', Translator::getInstance()->trans('Area not found!', [], 'carriersdelivery.bo.default'));
            }
        } catch (\Exception $e) {
            $this->getSession()->getFlashBag()->add('danger', $e->getMessage());
        }

        $url = $this->getRouteFromRouter(
            'router.carriersdelivery',
            'carriersdelivery.admin.carrier.areas',
            ['carrier_id' => $area->getCarrierId()]
        );

        return $this->generateRedirect($url);
    }

    /**
     * @return mixed|\Thelia\Core\HttpFoundation\Response
     */
    public function priceAction()
    {
        if (null !== $response = $this->checkAuth([], ['CarriersDelivery'], AccessManager::DELETE)) {
            return $response;
        }

        $this->checkXmlHttpRequest();

        $responseData = [
            'success' => false,
            'message' => ''
        ];

        $area_id = $this->getRequest()->request->getInt('area_id');

        $weight = CarriersDelivery::sanitizeNumber($this->getRequest()->request->get('weight'));

        try {
            if (!is_numeric($weight)) {
                throw new \Exception(Translator::getInstance()->trans('Entry error on weight!', [], 'carriersdelivery.bo.default'));
            }

            if (null === $area = $this->getExistingObject($area_id)) {
                throw new \LogicException(sprintf("area id *%d* does not exist", $area_id));
            }

            $carrier = $area->getCarriersdeliveryCarrier();

            $cost = CarriersDelivery::getCostForArea($area_id, $weight, $carrier->getUnitPerKg());

            if (false === $cost) {
                throw new \Exception(Translator::getInstance()->trans('No price for this area/weight!', [], 'carriersdelivery.bo.default'));
            }

            $price = $carrier->calculatePrice($cost, $carrier->getFeesCost(), $carrier->getDieselTaxPercent());

            $responseData['success'] = true;

            $responseData['message'] = $price;
        } catch (\Exception $e) {
            $responseData['message'] = $e->getMessage();
        }

        return $this->jsonResponse(json_encode($responseData));
    }
}