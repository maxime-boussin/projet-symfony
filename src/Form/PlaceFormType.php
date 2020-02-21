<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Place;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('city', EntityType::class, [
                'class' => City::class,
                'label'    => false,
                'choice_label' => 'name',
            ])
            ->add('address', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Voie',
                    'class' => 'form-control'
                ]
            ])
            ->add('longitude', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Longitude',
                    'class' => 'form-control'
                ]
            ])
            ->add('latitude', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Latitude',
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
        ]);
    }
}
