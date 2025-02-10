<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VlogController extends AbstractController
{
    #[Route('/vlog', name: 'app_vlog')]
    public function index(): Response
    {
        return $this->render('vlog/index.html.twig', [
            'controller_name' => 'VlogController',
        ]);
    }
}
