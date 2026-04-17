<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;

class DepartmentType extends AbstractType
{
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName(): string
    {
        return 'department';
    }

    /**
     * @return null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return 'text';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new CallbackTransformer(
            function ($original) {
                return $original ? implode(',', $original) : '';
            },
            function ($submitted) {
                if (!$submitted) {
                    return [];
                }

                $submitted = str_replace([';', '/', '-', ' '], ',', $submitted);

                $submitted = array_map(function($tag) {
                    return trim($tag);
                }, explode(',', $submitted));

                return $submitted;
            }
        ));
    }
}