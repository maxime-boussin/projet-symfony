<?php

namespace App\Form;

use App\Entity\Place;
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
            ->add('city', CityFormType::class, [
                'label' => false
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse de l\'évènement : '
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude : '
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude : '
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
