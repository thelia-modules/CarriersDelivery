<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Controller\Back;


use CarriersDelivery\Form\PackingcostCreateForm;
use CarriersDelivery\Form\PackingcostEditForm;
use CarriersDelivery\Model\CarriersdeliveryPackingcosts;
use CarriersDelivery\Model\CarriersdeliveryPackingcostsQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Currency;

class PackingcostController extends BaseAdminController
{
    /**
     * @param int $id
     * @return null|\CarriersDelivery\Model\CarriersdeliveryPackingcosts|mixed
     */
    protected function getExistingObject($id = 0)
    {
        $id = intval($id);

        if (!empty($id) && $id > 0) {
            $packingcost = CarriersdeliveryPackingcostsQuery::create()->findPk($id);

            return $packingcost;
        }

        return false;
    }

    /**
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function listAction()
    {
        if (null !== $response = $this->checkAuth([], ['CarriersDelivery'], AccessManager::VIEW)) {
            return $response;
        }

        $createForm = $this->createForm(PackingcostCreateForm::getName());
        $editForm = $this->createForm(PackingcostEditForm::getName());

        $packingcosts = CarriersdeliveryPackingcostsQuery::create()->orderByWeightMax()->find();
        $packingcostRows = [];
        foreach ($packingcosts as $pc) {
            $packingcostRows[] = [
                'id' => $pc->getId(),
                'weight_max' => (float) $pc->getWeightMax(),
                'cost' => (float) $pc->getCost(),
            ];
        }

        $currencySymbol = Currency::getDefaultCurrency()?->getSymbol() ?? '';

        return $this->render('carriersdelivery-packingcosts-list', [
            'packingcosts' => $packingcostRows,
            'currency_symbol' => $currencySymbol,
            'create_form' => $createForm->getForm()->createView(),
            'edit_form' => $editForm->getForm()->createView(),
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

        $this->checkXmlHttpRequest();

        $responseData = [
            'success' => false,
            'message' => ''
        ];

        $form = $this->createForm('carriersdelivery_packingcost_create');

        try {
            $dataForm = $this->validateForm($form, 'POST');

            /** @var CarriersdeliveryPackingcosts $packingcost */
            $packingcost = new CarriersdeliveryPackingcosts();

            $packingcost
                ->setWeightMax($dataForm->get('weight_max')->getData())
                ->setCost($dataForm->get('cost')->getData())
                ->save();

            $responseData['success'] = true;

            $responseData['message'] = Translator::getInstance()->trans('Packing cost added!', [], 'carriersdelivery.bo.default');
        } catch (\Exception $e) {
            $responseData['message'] = $e->getMessage();
        }

        return $this->jsonResponse(json_encode($responseData));
    }

    /**
     * @return mixed|\Thelia\Core\HttpFoundation\Response
     */
    public function editAction()
    {
        if (null !== $response = $this->checkAuth([], ['CarriersDelivery'], AccessManager::UPDATE)) {
            return $response;
        }

        $this->checkXmlHttpRequest();

        $responseData = [
            'success' => false,
            'message' => ''
        ];

        $form = $this->createForm('carriersdelivery_packingcost_edit');

        try {
            $dataForm = $this->validateForm($form, 'POST');

            if (null !== $packingcost = $this->getExistingObject($dataForm->get('id')->getData())) {
                $packingcost
                    ->setWeightMax($dataForm->get('weight_max')->getData())
                    ->setCost($dataForm->get('cost')->getData())
                    ->save();

                $responseData['success'] = true;

                $responseData['message'] = Translator::getInstance()->trans('Packing cost updated!', [], 'carriersdelivery.bo.default');
            } else {
                $responseData['message'] =  Translator::getInstance()->trans('Packing cost not found!', [], 'carriersdelivery.bo.default');
            }
        } catch (\Exception $e) {
            $responseData['message'] = $e->getMessage();
        }

        return $this->jsonResponse(json_encode($responseData));
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
            $packingcost_id = $this->getRequest()->request->get('packingcost_id');

            if (null !== $packingcost = $this->getExistingObject($packingcost_id)) {
                $packingcost->delete();

                // $this->adminLogAppend('carriersdelivery', AccessManager::DELETE, sprintf('packingcost %d deleted', $packingcost_id));

                $this->getSession()->getFlashBag()->add('success', Translator::getInstance()->trans('Packing cost deleted!', [], 'carriersdelivery.bo.default'));
            } else {
                $this->getSession()->getFlashBag()->add('danger', Translator::getInstance()->trans('Packing cost not found!', [], 'carriersdelivery.bo.default'));
            }
        } catch (\Exception $e) {
            $this->getSession()->getFlashBag()->add('danger', $e->getMessage());
        }

        $url = $this->getRouteFromRouter('router.carriersdelivery', 'carriersdelivery.admin.packingcosts');

        return $this->generateRedirect($url);
    }
}