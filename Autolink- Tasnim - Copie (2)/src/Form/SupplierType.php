<?php

namespace App\Form;

use App\Entity\Supplier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;

class SupplierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'entreprise est obligatoire.']),
                ]
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'email est obligatoire.']),
                    new Assert\Email(['message' => 'L\'email n\'est pas valide.']),
                    new Assert\Regex([
                        'pattern' => "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/",
                        'message' => "L'email doit être au format valide (ex: exemple@domaine.com)."
                    ]),
                ]
            ])
            ->add('phone', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le numéro de téléphone est obligatoire.']),
                    new Assert\Regex([
                        'pattern' => "/^\+?[0-9]{8,15}$/",
                        'message' => 'Le numéro de téléphone doit contenir entre 8 et 15 chiffres.'
                    ])
                ]
            ])
            ->add('address', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'adresse est obligatoire.']),
                ]
            ])
            ->add('taxcode', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le code fiscal est obligatoire.']),
                    new Assert\Length([
                        'min' => 8,
                        'max' => 15,
                        'minMessage' => 'Le code fiscal doit contenir au moins 8 caractères.',
                        'maxMessage' => 'Le code fiscal ne doit pas dépasser 15 caractères.'
                    ])
                ]
            ])
            ->add('typeRecyclage', ChoiceType::class, [
                'choices' => [
                    'Plastique' => 'Plastique',
                    'Verre' => 'Verre',
                    'Cartouche' => 'Cartouche'
                ],
                'placeholder' => 'Sélectionnez un type de recyclage',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un type de recyclage.'])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Supplier::class,
        ]);
    }
}
