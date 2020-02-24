<?php

namespace App\Form;

use App\Entity\Excursion;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExcursionPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Libéllé de la sortie'
                ]
            ])
            ->add('site', EntityType::class, [
                'label'    => false,
                'class' => Site::class,
                'attr' => [
                    'choice_label' => 'name'
                ]
            ])
            ->add('place', PlaceFormType::class, [
                'label' => false
            ])
            ->add('description', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'help' => 'Karting, piscine, cinéma, marche au bord du lac etc.',
                    'placeholder' => 'Description de l\'activité',
                    'class' => 'form-control'
                ]
            ])
            ->add('date', DateTimeType::class, [
                'label' => false,
                'widget' => 'single_text'
            ])
            ->add('limitDate', DateTimeType::class, [
                'label' => false,
                'widget' => 'single_text'
            ])
            ->add('duration', DateIntervalType::class, [
                'label' => false,
                'labels' => [
                    'years' => false,
                    'months' => false,
                    'days' => false,
                    'hours' => false,
                    'minutes' => false
                ],
                'placeholder' => ['years' => 'Année', 'months' => 'Mois', 'days' => 'Jours', 'hours' => 'Heures', 'minutes' => 'Minutes'],
                'with_years' => false,
                'with_months'   => false,
                'with_minutes' => true,
                'with_hours'   => true,
            ])
            ->add('participantLimit', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Limite de participants',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Excursion::class,
        ]);
    }
}
