<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'required' => false,
                'placeholder' => 'Aucun utilisateur',
            ])
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La date est requise']),
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'en_attente',
                    'Expédiée' => 'expédiée',
                    'Livrée' => 'livrée',
                    'Annulée' => 'annulée',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le statut est requis']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}