<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom d\'utilisateur est requis']),
                    new Length(['min' => 3, 'max' => 50]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est requis']),
                ],
            ]);

        if (!$options['is_edit']) {
            $builder->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Le mot de passe est requis']),
                    new Length(['min' => 6, 'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères']),
                ],
            ]);
        }

        if ($options['is_admin']) {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'multiple' => true,
                'expanded' => true,
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
            'is_admin' => false,
        ]);
    }
}
