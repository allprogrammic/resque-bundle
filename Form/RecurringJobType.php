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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class RecurringJobType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'task_name',
                ],
            ])
            ->add('description', TextType::class, [
                'attr' => [
                    'placeholder' => 'Description of your own task',
                ],
            ])
            ->add('cron', TextType::class, [
                'attr' => [
                    'placeholder' => '*/5 * * * *',
                ],
            ])
            ->add('class', TextType::class, [
                'attr' => [
                    'placeholder' => 'ClassName',
                ],
            ])
            ->add('queue', TextType::class, [
                'attr' => [
                    'placeholder' => 'tasks',
                ],
            ])
            ->add('args', TextareaType::class, [
                'data' => '{}'
            ])
            ->add('start', CheckboxType::class, [
                'label' => 'Start now',
            ]);
    }
}
