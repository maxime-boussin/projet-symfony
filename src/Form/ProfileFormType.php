<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                "label" => false,
                'attr'    => [
                    'class' => 'form-inline',
                    'placeholder' => 'Prénom'
                    ],
                ])
            ->add('lastName', TextType::class, [
                "label" => false,
                'attr'    => [
                    'class' => 'form-inline',
                    'placeholder' => 'Nom'
                ],
            ])
            ->add('nickname', TextType::class, [
                "label" => false,
                'attr'    => [
                    'class' => 'form-inline',
                    'placeholder' => 'Pseudonyme'
                ],
            ])
            ->add('phone', TextType::class, [
                "label" => false,
                'attr'    => [
                    'class' => 'form-inline',
                    'placeholder' => 'Téléphone'
                ],
            ])
            ->add('avatar', FileType::class, [
                'mapped' => false,
                "label" => false,
                'required' => false,
                'attr'    => [
                    'placeholder' => 'Avatar'
                ],
            ])
            ->add('oldPassword', PasswordType::class, array(
                "mapped" => false,
                "label" => false,
                "attr" => [
                    'placeholder' => "Mot de passe"
                ],
                "constraints" => [new SecurityAssert\UserPassword([
                   'message' => "Wrong password"
                ])]
            ))
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => [
                    'label' => false,
                    "attr" => [
                    'placeholder' => "Nouveau mot de passe"
                    ],
                ],
                'second_options' => [
                    'label' => false,
                    "attr" => [
                        'placeholder' => "Confirmer"
                    ],
                ],
                'mapped' => false,
                'required'   => false,
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
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
