<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Variant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class VariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank(['message' => 'Le produit est requis']),
                ],
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
            ->add('stockQuantity', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La quantité est requise']),
                    new PositiveOrZero(['message' => 'La quantité doit être positive ou zéro']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Variant::class,
        ]);
    }
}