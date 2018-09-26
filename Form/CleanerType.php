<?php

/*
 * This file is part of the AllProgrammic ResqueBunde package.
 *
 * (c) AllProgrammic SAS <contact@allprogrammic.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllProgrammic\Bundle\ResqueBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class CleanerType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('class', TextType::class, [
                'attr' => [
                    'autocomplete' => 'off',
                    'placeholder' => 'ClassName',
                ],
            ])
            ->add('exception', TextType::class, [
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off',
                    'placeholder' => 'Define exception class or leave empty to catch all exceptions',
                ],
            ])
            ->add('attempts', NumberType::class, [
                'attr' => [
                    'autocomplete' => 'off',
                    'placeholder' => 'Define the number of attempts',
                ],
            ]);
    }
}
