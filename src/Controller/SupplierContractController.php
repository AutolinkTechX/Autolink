<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\SupplierContract;
use App\Form\SupplierContractType;

final class SupplierContractController extends AbstractController
{
    #[Route('/contract', name: 'app_contract')]
    public function index(): Response
    {
        return $this->render('contract/ajout.html.twig', [
            'controller_name' => 'ContractController',
        ]);
    }

    #[Route('/contract/new', name: 'contract_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contract = new SupplierContract();
        $form = $this->createForm(SupplierContractType::class, $contract);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contract);
            $entityManager->flush();

            return $this->redirectToRoute('contract_list');
        }

        return $this->render('supplier_contract/ajout.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/contract/list', name: 'contract_list')]
    public function listContracts(EntityManagerInterface $entityManager): Response
    {
        $contracts = $entityManager->getRepository(SupplierContract::class)->findAll();

        if (empty($contracts)) {
            dd("Aucun contrat trouvé !");
        }

        return $this->render('supplier_contract/list.html.twig', [
            'contracts' => $contracts,
        ]);
    }

    #[Route('/contract/edit/{id}', name: 'contract_edit')]
    public function editContract(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $contract = $entityManager->getRepository(Contract::class)->find($id);

        if (!$contract) {
            throw $this->createNotFoundException('Contrat non trouvé.');
        }

        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le contrat a été modifié avec succès.');
            return $this->redirectToRoute('contract_list');
        }

        return $this->render('contract/edit.html.twig', [
            'form' => $form->createView(),
            'contract' => $contract,
        ]);
    }

    #[Route('/contract/delete/{id}', name: 'contract_delete', methods: ['POST', 'GET'])]
    public function deleteContract(int $id, EntityManagerInterface $entityManager): Response
    {
        $contract = $entityManager->getRepository(Contract::class)->find($id);

        if (!$contract) {
            throw $this->createNotFoundException('Contrat non trouvé.');
        }

        $entityManager->remove($contract);
        $entityManager->flush();

        $this->addFlash('success', 'Le contrat a été supprimé avec succès.');
        return $this->redirectToRoute('contract_list');
    }
}
