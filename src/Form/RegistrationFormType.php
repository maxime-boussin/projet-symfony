<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                "label" => false,
                'attr' => [
                    'placeholder' => 'Pseudonyme',
                    'class' => 'form-control'
                ]
            ])
            ->add('nickname', TextType::class, [
                "label" => false,
                    'attr' => [
                        'placeholder' => 'Pseudonyme',
                        'class' => 'form-control'
                    ]
            ])
            ->add('firstName', TextType::class, [
                "label" => false,
                'attr' => [
                    'placeholder' => 'Prénom',
                    'class' => 'form-control'
                ]
            ])
            ->add('lastName', TextType::class, [
                "label" => false,
                'attr' => [
                    'placeholder' => 'Nom',
                    'class' => 'form-control'
                ]
            ])
            ->add('site', EntityType::class, [
                    'class' => Site::class,
                    "label" => false,
                    'choice_label' => 'name',
                ]
            )
            ->add('phone', TextType::class, [
                "label" => false,
                'attr' => [
                    'placeholder' => 'Téléphone',
                    'class' => 'form-control'
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
                'first_options'  => [
                    "label" => false,
                    'attr' => [
                        'placeholder' => 'Mot de passe',
                        'class' => 'form-control'
                    ]
                ],
                'second_options'  => [
                    "label" => false,
                    'attr' => [
                        'placeholder' => 'Confirmation',
                        'class' => 'form-control'
                    ]
                ]
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
