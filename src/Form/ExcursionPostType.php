<?php

namespace App\Form;

use App\Entity\Excursion;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExcursionPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'    => 'Nom de la sortie : ',
            ])
            ->add('site', EntityType::class, [
                    'class' => Site::class,
                    'label'    => 'Site : ',
                    'choice_label' => 'name'
                ]
            )
            ->add('place', PlaceFormType::class, [
                'label' => false
            ])
            ->add('description', TextType::class, [
                'label' => 'Description de la sortie : ',
                'help' => 'Karting, piscine, cinéma, marche au bord du lac etc.'
            ])
            ->add('date', DateType::class, [
                'label' => 'Date de la sortie : '
            ])
            ->add('limitDate', DateType::class, [
                'label' => 'Date limite pour les inscriptions : '
            ])
            ->add('duration', DateIntervalType::class, [
                'label' => 'Durée de la sortie : ',
                'with_years' => false,
                'with_months'   => false,
                'with_minutes' => true,
                'with_hours'   => true
            ])
            ->add('visibility', CheckboxType::class, [
                'label' => 'Visible pour tout le monde'
            ])
            ->add('participantLimit', NumberType::class, [
                'label' => 'Nombre maximum de participants : '
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
