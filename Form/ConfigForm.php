<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Form;

use CarriersDelivery\CarriersDelivery;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Thelia\Form\BaseForm;
use Thelia\Model\TaxRuleQuery;

class ConfigForm extends BaseForm
{
    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'carriersdelivery_config';
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function checkTaxRuleId($value, ExecutionContextInterface $context)
    {
        if (0 !== intval($value)) {
            if (null === TaxRuleQuery::create()->findPk($value)) {
                $context->addViolation(
                    $this->trans('The tax rule id \'%id\' doesn\'t exist', ['%id' => $value])
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function buildForm()
    {
        $config = CarriersDelivery::getConfig();

        $this->formBuilder
            ->add(
                'tax',
                IntegerType::class,
                [
                    'constraints' => [
                        new Callback($this->checkTaxRuleId(...)),
                    ],
                    'data' => $config['tax'],
                    'label' => $this->trans('Tax rule'),
                    'label_attr' => [
                        'for' => 'method',
                        'help' => $this->trans('The tax rule used to calculate postage taxes.')
                    ],
                    'required' => true,
                ]
            );
    }

    /**
     * @param $id
     * @param array $parameters
     * @return string
     */
    protected function trans($id, array $parameters = [])
    {
        return $this->translator->trans($id, $parameters, 'carriersdelivery.bo.default');
    }
}