<?php

namespace App\Form;

use App\Entity\MaterielRecyclable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Enum\StatutEnum;
use App\Entity\Entreprise;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\File;

use Symfony\Component\Validator\Constraints as Assert;





class MaterielRecyclableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
           

            ->add('typemateriel', ChoiceType::class, [
                'label' => 'Type of Material',
                'choices' => [
                    'Plastique' => 'plastique',
                    'Électronique' => 'electronique',
                    'Caoutchouc' => 'caoutchouc',
                    'Aluminium' => 'aluminium',
                    'Verre' => 'verre',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'expanded' => false, // false pour un menu déroulant, true pour des boutons radio
                'multiple' => false, // false pour une seule sélection, true pour une sélection multiple
                'attr' => ['class' => 'form-select', 'id' => 'typemateriel'],
                'placeholder' => 'Sélectionnez un type'
            ])
            
            ->add('image', FileType::class, [
                'label' => 'image',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid JPEG or PNG file',
                    ]),
                ],
                
            ])
            ->add('entreprise', EntityType::class, [
                'class' => Entreprise::class,
                'choice_label' => 'company_name',
                'label' => 'Enterprise',
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.supplier = :supplier')
                        ->setParameter('supplier', true);
                },
                'placeholder' => 'Sélectionnez un type'
            ])
            ->add('submit', SubmitType::class, ['label' => 'Save'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MaterielRecyclable::class,
        ]);
    }
}