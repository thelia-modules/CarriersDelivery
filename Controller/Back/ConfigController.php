<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Controller\Back;


use CarriersDelivery\CarriersDelivery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Translation\Translator;
use Thelia\Model\TaxRuleQuery;

class ConfigController extends BaseAdminController
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response|\Thelia\Core\HttpFoundation\Response
     */
    public function configAction()
    {
        if (null !== $response = $this->checkAuth([], ['CarriersDelivery'], AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm('carriersdelivery_config', 'form');

        try {
            if ($this->getRequest()->isMethod('POST')) {
                $dataForm = $this->validateForm($form, 'POST');

                CarriersDelivery::setConfigValue(CarriersDelivery::CONFIG_TAX_RULE_ID, $dataForm->get('tax')->getData());

                $this->getSession()->getFlashBag()->add(
                    'success',
                    Translator::getInstance()->trans('Configuration updated!', [], 'carriersdelivery.bo.default')
                );

                return $this->generateSuccessRedirect($form);
            }
        } catch (\Exception $e) {
            $this->setupFormErrorContext(get_class($form), $e->getMessage(), $form, $e);
        }

        $locale = $this->getRequest()->getSession()->getLang()->getLocale();

        $taxRules = [];
        $taxRuleList = TaxRuleQuery::create()->orderById()->find();

        foreach ($taxRuleList as $taxRule) {
            $taxRule->setLocale($locale);
            $taxRules[] = [
                'id' => $taxRule->getId(),
                'title' => $taxRule->getTitle(),
            ];
        }

        $config = CarriersDelivery::getConfig();

        return $this->render('carriersdelivery-config', [
            'config_form' => $form->getForm()->createView(),
            'tax_rules' => $taxRules,
            'current_tax' => $config['tax'],
        ]);
    }

}