<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est requis']),
                    new Length(['max' => 150]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Aucune catégorie',
            ])
            ->add('priceBuy', NumberType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le prix d\'achat est requis']),
                    new Positive(['message' => 'Le prix doit être positif']),
                ],
            ])
            ->add('priceSell', NumberType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le prix de vente est requis']),
                    new Positive(['message' => 'Le prix doit être positif']),
                ],
            ])
            ->add('image', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Length(['max' => 255]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}