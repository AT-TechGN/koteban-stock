<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\OrderItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class OrderItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('order', EntityType::class, [
                'class' => Order::class,
                'choice_label' => 'id',
                'constraints' => [
                    new NotBlank(['message' => 'La commande est requise']),
                ],
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Aucun produit',
            ])
            ->add('size', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Length(['max' => 10]),
                ],
            ])
            ->add('color', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Length(['max' => 30]),
                ],
            ])
            ->add('quantity', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La quantité est requise']),
                    new Positive(['message' => 'La quantité doit être positive']),
                ],
            ])
            ->add('price', NumberType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le prix est requis']),
                    new Positive(['message' => 'Le prix doit être positif']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderItem::class,
        ]);
    }
}