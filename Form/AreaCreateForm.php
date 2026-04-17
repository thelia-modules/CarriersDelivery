<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Form;

use CarriersDelivery\Form\Type\DepartmentType;
use Symfony\Component\Validator\Constraints;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

class AreaCreateForm extends BaseForm
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'carriersdelivery_area_create';
    }

    /**
     * @inheritdoc
     */
    protected function buildForm()
    {
        $translator = Translator::getInstance();

        $this->formBuilder
            ->add(
                'name',
                'text',
                [
                    'constraints'   => [
                        new Constraints\NotBlank(),
                    ],
                    'label'         => $translator->trans('Name'),
                    'required'      => true,
                ]
            )
            ->add(
                'carrier_id',
                'hidden',
                [
                    'constraints'   => [
                        new Constraints\GreaterThanOrEqual(['value' => 0]),
                    ],
                    'label'         => 'carrier_id',
                    'required'      => true,
                ]
            )
            ->add(
                'departments',
                DepartmentType::class,
                [
                    'constraints'   => [
                        new Constraints\NotBlank(),
                    ],
                    'label'         => $translator->trans('Departments', [], 'carriersdelivery.bo.default'),
                    'label_attr'    => [
                        'help' => $translator->trans('Departments-help', [], 'carriersdelivery.bo.default')
                            . '<br/>' . $translator->trans('Departments-help1', [], 'carriersdelivery.bo.default')
                            . '<br/>' . $translator->trans('Departments-help2', [], 'carriersdelivery.bo.default')
                    ],
                    'required' => true,
                ]
            )
        ;
    }

}