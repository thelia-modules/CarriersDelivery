<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Form;

use CarriersDelivery\Form\Type\MyNumberType;
use Symfony\Component\Validator\Constraints;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\CountryQuery;

class CarrierCreateForm extends BaseForm
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'carriersdelivery_carrier_create';
    }

    /**
     * @inheritdoc
     */
    protected function buildForm()
    {
        $translator = Translator::getInstance();

        $countriesList = CountryQuery::create()
            ->joinWithI18n($translator->getLocale())
            ->find()
            ->toKeyValue('Id', 'Title')
        ;

        $this->formBuilder
            ->add(
                'name',
                'text',
                [
                    'constraints' => [
                        new Constraints\NotBlank(),
                    ],
                    'label' => $translator->trans('Name'),
                    'required' => true,
                ]
            )
            ->add(
                'country_id',
                'choice',
                [
                    'choices'   => $countriesList,
                    'label'     => $translator->trans('Country'),
                    'required'  => true,
                ]
            )
            ->add(
                'diesel_tax_percent',
                MyNumberType::class,
                [
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\GreaterThanOrEqual(['value' => 0]),
                        new Constraints\Type(['type' => 'numeric']),
                    ],
                    'label'     => $translator->trans('Diesel tax percent', [], 'carriersdelivery.bo.default'),
                    'required'  => true,
                ]
            )
            ->add(
                'fees_cost',
                MyNumberType::class,
                [
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\GreaterThanOrEqual(['value' => 0]),
                        new Constraints\Type(['type' => 'numeric']),
                    ],
                    'label'     => $translator->trans('Fees cost', [], 'carriersdelivery.bo.default'),
                    'required'  => true,
                ]
            )
            ->add(
                'unit_per_kg',
                'integer',
                [
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\GreaterThanOrEqual(['value' => 0]),
                        new Constraints\LessThan(['value' => 32767]),
                    ],
                    'label'     => $translator->trans('Unit per Kg price', [], 'carriersdelivery.bo.default'),
                    'required'  => true,
                ]
            )
        ;
    }

}