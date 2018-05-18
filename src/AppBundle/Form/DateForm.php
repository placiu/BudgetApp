<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class DateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('year', ChoiceType::class, ['label' => false, 'attr' => ['class' => 'form-control select2', 'style' => 'width: 300px']])
            ->add('month', ChoiceType::class, ['label' => false, 'attr' => ['class' => 'form-control select2', 'style' => 'width: 300px']])
            ->add('monthName', HiddenType::class)
        ;

        $builder->get('year')->resetViewTransformers();
        $builder->get('month')->resetViewTransformers();
    }
}