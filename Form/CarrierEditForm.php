<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Form;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints;

class CarrierEditForm extends CarrierCreateForm
{
    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'carriersdelivery_carrier_edit';
    }

    /**
     * @inheritdoc
     */
    protected function buildForm()
    {
        parent::buildForm();

        $this->formBuilder
            ->add(
                'id',
                HiddenType::class,
                [
                    'constraints' => [
                        new Constraints\GreaterThanOrEqual(value: 0),
                    ],
                    'required' => true,
                ]
            )
        ;
    }

}