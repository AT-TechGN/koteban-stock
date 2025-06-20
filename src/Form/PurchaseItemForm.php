<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class PurchaseItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('purchase', EntityType::class, [
                'class' => Purchase::class,
                'choice_label' => 'id',
                'constraints' => [
                    new NotBlank(['message' => 'L\'achat est requis']),
                ],
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Aucun produit',
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
            'data_class' => PurchaseItem::class,
        ]);
    }
}