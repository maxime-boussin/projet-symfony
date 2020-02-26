<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddGroupMemberFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class,[
                'attr' => [
                    'placeholder' => 'Mail de l\'utilisateur à ajouter',
                    'list' => 'usersEmails'
                ]
            ])
            ->add('nickname', TextType::class,[
                'label' => 'Pseudo',
                'attr' => [
                    'placeholder' => 'Pseudo de l\'utilisateur à ajouter',
                    'list' => 'usersNicknames'
                ]
            ]);
    }
}
