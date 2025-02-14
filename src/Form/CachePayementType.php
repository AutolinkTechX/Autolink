<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\Callback;

class CachePayementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('full_name', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire']),
                    new Length(['min' => 3, 'minMessage' => 'Le nom doit contenir au moins 3 caractères']),
                ],
                'attr' => ['placeholder' => 'Nom Prénom'],
            ])
            ->add('phone_number', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de téléphone est obligatoire']),
                    new Regex([
                        'pattern' => '/^\d{10}$/',
                        'message' => 'Le numéro de téléphone doit être valide (format : 06 XX XX XX XX)',
                    ]),
                ],
                'attr' => ['placeholder' => '06 XX XX XX XX'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Confirmer le Paiement',
            ])
            ->addModelTransformer(new Callback(function ($data, ExecutionContextInterface $context) {
                // Validation personnalisée pour vérifier si le nom et le téléphone existent déjà dans la base de données
                $userRepository = $context->getObjectManager()->getRepository(User::class);

                // Vérifier si le nom existe déjà
                $existingUser = $userRepository->findOneBy(['fullName' => $data['full_name']]);
                if ($existingUser) {
                    $context->buildViolation('Le nom complet existe déjà.')
                        ->atPath('full_name')
                        ->addViolation();
                }

                // Vérifier si le numéro de téléphone existe déjà
                $existingPhone = $userRepository->findOneBy(['phoneNumber' => $data['phone_number']]);
                if ($existingPhone) {
                    $context->buildViolation('Le numéro de téléphone existe déjà.')
                        ->atPath('phone_number')
                        ->addViolation();
                }
            }));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Aucune entité n'est associée à ce formulaire, donc pas de data_class nécessaire
        ]);
    }
}
