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
                    'choice_label' => 'name',
                    ]
            )
            ->add('excursion_content', TextType::class, [
                'attr' => [
                    'placeholder' => 'Recherche...',
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('from_date', DateType::class, [
                'attr'    => ['class' => 'form-inline'],
                'widget' => 'single_text',
                'data' => (new \DateTime())->sub(new \DateInterval('P5Y'))
            ])
            ->add('to_date', DateType::class, [
                'attr'    => ['class' => 'form-inline'],
                'widget' => 'single_text',
                'data' => (new \DateTime())->add(new \DateInterval('P5Y'))
            ])
            ->add('owned_excursions', CheckboxType::class, [
                'label'    => 'Sorties initiées',
                'required' => false,
                'data' => true
            ])
            ->add('subscribed_excursions', CheckboxType::class, [
                'label'    => 'Sorties souscrites',
                'required' => false,
                'data' => true
            ])
            ->add('not_subscribed_excursions', CheckboxType::class, [
                'label'    => 'Sorties non-souscrites',
                'required' => false,
                'data' => true
            ])
            ->add('past_excursions', CheckboxType::class, [
                'label'    => 'Sorties passées',
                'required' => false,
                'data' => true
            ])
        ;
    }
}
