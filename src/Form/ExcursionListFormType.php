<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ExcursionListFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('site', EntityType::class, [
                    'class' => Site::class,
                    'label'    => 'Site:',
                    'choice_label' => 'name',
                    ]
            )
            ->add('excursion_content', TextType::class, [
                'label'    => 'Le nom de la sortie contient',
            ])
            ->add('from_date', DateType::class, [
                'label'    => 'Entre',
            ])
            ->add('to_date', DateType::class, [
                'label'    => 'et',
            ])
            ->add('owned_excursions', CheckboxType::class, [
                'label'    => 'Sorties dont je suis l\'organisateur/trice',
            ])
            ->add('subscribed_excursions', CheckboxType::class, [
                'label'    => 'Sorties auxquelles je suis inscrit/e',
            ])
            ->add('not_subscribed_excursions', CheckboxType::class, [
                'label'    => 'Sorties auxquelles je ne suis pas inscrit/e',
            ])
            ->add('passed_excursions', CheckboxType::class, [
                'label'    => 'Sorties passées',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
