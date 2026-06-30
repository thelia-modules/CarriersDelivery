<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Controller\Back;


use CarriersDelivery\Form\CarrierCreateForm;
use CarriersDelivery\Model\CarriersdeliveryCarrier;
use CarriersDelivery\Model\CarriersdeliveryCarrierQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Translation\Translator;

class CarrierController extends BaseAdminController
{

    /**
     * @param int $id
     * @return null|\CarriersDelivery\Model\CarriersdeliveryCarrier|mixed
     */
    protected function getExistingObject($id = 0)
    {
        $id = intval($id);

        if (!empty($id) && $id > 0) {
            /** @var CarriersdeliveryCarrier $carrier */
            $carrier = CarriersdeliveryCarrierQuery::create()->findPk($id);

            return $carrier;
        }

        return null;
    }

    /**
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function listAction()
    {
        if (null !== $response = $this->checkAuth([], ['CarriersDelivery'], AccessManager::VIEW)) {
            return $response;
        }

        $createForm = $this->createForm(CarrierCreateForm::getName());

        $carriers = CarriersdeliveryCarrierQuery::create()->orderByName()->find();
        $carrierRows = [];
        foreach ($carriers as $carrier) {
            $carrierRows[] = [
                'id' => $carrier->getId(),
                'name' => $carrier->getName(),
                'country_id' => $carrier->getCountryId(),
                'diesel_tax_percent' => (float) $carrier->getDieselTaxPercent(),
                'fees_cost' => (float) $carrier->getFeesCost(),
                'unit_per_kg' => (float) $carrier->getUnitPerKg(),
            ];
        }

        return $this->render('carriersdelivery-carriers-list', [
            'carriers' => $carrierRows,
            'create_form' => $createForm->getForm()->createView(),
        ]);
    }

    /**
     * @return mixed|\Thelia\Core\HttpFoundation\Response
     */
    public function createAction()
    {
        if (null !== $response = $this->checkAuth([], ['CarriersDelivery'], AccessManager::CREATE)) {
            return $response;
        }

        $form = $this->createForm('carriersdelivery_carrier_create');

        try {
            $dataForm = $this->validateForm($form, 'POST');

            /** @var CarriersdeliveryCarrier() $carrier */
            $carrier = new CarriersdeliveryCarrier();

            $carrier
                ->setName($dataForm->get('name')->getData())
                ->setCountryId($dataForm->get('country_id')->getData())
                ->setDieselTaxPercent($dataForm->get('diesel_tax_percent')->getData())
                ->setFeesCost($dataForm->get('fees_cost')->getData())
                ->setUnitPerKg($dataForm->get('unit_per_kg')->getData())
                ->save();

            $this->getSession()->getFlashBag()->add('success', Translator::getInstance()->trans('Carrier added!', [], 'carriersdelivery.bo.default'));

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

        $baseForm = null;

        if ($this->getRequest()->isMethod('POST')) {
            $form = $this->createForm('carriersdelivery_carrier_edit');
            $baseForm = $form;

            try {
                $editForm = $this->validateForm($form, 'POST');

                $carrier_id = $editForm->get('id')->getData();

                if (null === $carrier = $this->getExistingObject($carrier_id)) {
                    throw new \LogicException(sprintf("carrier id *%d* does not exist", $carrier_id));
                }

                $carrier
                    ->setName($editForm->get('name')->getData())
                    ->setCountryId($editForm->get('country_id')->getData())
                    ->setDieselTaxPercent($editForm->get('diesel_tax_percent')->getData())
                    ->setFeesCost($editForm->get('fees_cost')->getData())
                    ->setUnitPerKg($editForm->get('unit_per_kg')->getData())
                    ->save();

                $request = $this->getRequest();
                if ($request->attributes->get('save_mode', $request->query->get('save_mode', $request->request->get('save_mode'))) != 'stay') {
                    return $this->generateSuccessRedirect($form);
                }

                $this->getParserContext()->addForm($form);
            } catch (\Exception $e) {
                $this->setupFormErrorContext(get_class($form), $e->getMessage(), $form, $e);

                $carrier_id = $form->getForm()->get('id')->getData();
            }
        } else {
            $carrier_id = $this->getRequest()->query->getInt('id');

            if (null === $carrier = $this->getExistingObject($carrier_id)) {
                throw new \LogicException(sprintf("carrier id *%d* does not exist", $carrier_id));
            }

            $data = [
                'id'                    => $carrier->getId(),
                'name'                  => $carrier->getName(),
                'country_id'            => $carrier->getCountryId(),
                'diesel_tax_percent'    => $carrier->getDieselTaxPercent(),
                'fees_cost'             => $carrier->getFeesCost(),
                'unit_per_kg'           => $carrier->getUnitPerKg(),
            ];

            $editForm = $this->createForm('carriersdelivery_carrier_edit', 'form', $data);
            $baseForm = $editForm;

            $this->getParserContext()->addForm($editForm);
        }

        $args = [
            'carrier_id' => $carrier_id,
            'edit_form' => $baseForm !== null ? $baseForm->getForm()->createView() : null,
        ];

        return $this->render('carriersdelivery-carrier-edit', $args);
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
            $carrier_id = $this->getRequest()->request->get('carrier_id');

            if (null !== $carrier = $this->getExistingObject($carrier_id)) {
                $carrier->delete();

                $this->getSession()->getFlashBag()->add('success', Translator::getInstance()->trans('Carrier deleted!', [], 'carriersdelivery.bo.default'));
            } else {
                $this->getSession()->getFlashBag()->add('danger', Translator::getInstance()->trans('Carrier not found!', [], 'carriersdelivery.bo.default'));
            }
        } catch (\Exception $e) {
            $this->getSession()->getFlashBag()->add('danger', $e->getMessage());
        }

        $url = $this->getRouteFromRouter('router.carriersdelivery', 'carriersdelivery.admin.carriers');

        return $this->generateRedirect($url);
    }
}