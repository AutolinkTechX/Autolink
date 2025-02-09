<?php

namespace App\Form;

use App\Entity\Supplier;
use App\Entity\SupplierContract;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierContractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', null, [
                'widget' => 'single_text',
            ])
            ->add('endDate', null, [
                'widget' => 'single_text',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Actif' => 'actif',
                    'Inactif' => 'inactif',
                    'En attente' => 'en_attente',
                ],
                'label' => 'Statut du contrat',
            ])
            ->add('terms')
            ->add('documentUrl') // Vérifie le nom exact du champ dans l'entité
            ->add('supplier', EntityType::class, [
                'class' => Supplier::class,
                'choice_label' => 'id',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupplierContract::class,
        ]);
    }
}
