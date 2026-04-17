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

class AreacostkgCreateForm extends BaseForm
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'carriersdelivery_areacostkg_create';
    }

    /**
     * @inheritdoc
     */
    protected function buildForm()
    {
        $translator = Translator::getInstance();

        $this->formBuilder
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
                'weight_max',
                MyNumberType::class,
                [
                    'attr' => [
                        'min'   => 0,
                        'step'  => 0.01,
                    ],
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\GreaterThanOrEqual(['value' => 0]),
                        new Constraints\Type(['type' => 'numeric']),
                    ],
                    'label'     => $translator->trans('Enter the weight to add', [], 'carriersdelivery.bo.default'),
                    'required'  => true,
                ]
            )
        ;
    }

}