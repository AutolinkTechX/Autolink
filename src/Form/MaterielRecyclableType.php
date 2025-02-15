<?php
namespace App\Form;

use App\Entity\MaterielRecyclable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Entreprise;
use App\Repository\EntrepriseRepository;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\DateTime;

class MaterielRecyclableType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['class' => 'form-control'],
                
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control'],
               'constraints' => [
                 new NotBlank(['message' => 'La description est obligatoire']),
                  ],
            ])
            ->add('datecreation', DateType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text', 
                'html5' => false, // Désactiver le rendu HTML5
                'format' => 'dd-MM-yyyy', // Format désiré
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'JJ-MM-AAAA',
                    'pattern' => '\d{2}-\d{2}-\d{4}' // Validation HTML basique
                ],
               
            ])
            ->add('typemateriel', ChoiceType::class, [
                'label' => 'Type de matériau',
                'choices' => [
                    'Recyclage du verre' => 'verre',
                    'Recyclage du plastique' => 'plastique',
                    'Recyclage électronique' => 'electronique',
                ],
                'placeholder' => 'Sélectionnez un type',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                   
                    new Choice([
                        'choices' => ['verre', 'plastique', 'electronique'],
                        'message' => 'Veuillez choisir un type valide'
                    ])
                ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du produit',
                'mapped' => false,
                'required' => true,
                'constraints' => [
        
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/jpg'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG/PNG)',
                    ])
                ],
                'attr' => ['class' => 'form-control-file']
            ])
            ->add('Entreprise', EntityType::class, [
                'class' => Entreprise::class,
                'choice_label' => 'company_name',
                'constraints' => [
                    new NotBlank(['message' => 'La sélection d\'une entreprise est obligatoire'])
                ],
                'placeholder' => 'Sélectionnez une entreprise',
                'query_builder' => function (EntrepriseRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.company_name', 'ASC');
                },
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MaterielRecyclable::class,
            'attr' => ['novalidate' => 'novalidate'] // Désactivation globale HTML5
        ]);
    }
}