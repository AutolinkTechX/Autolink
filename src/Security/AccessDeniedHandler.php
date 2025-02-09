<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\AccessDenied\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface as AuthorizationAccessDeniedHandlerInterface;

class AccessDeniedHandler implements AuthorizationAccessDeniedHandlerInterface
{
    public function handle(Request $request, AccessDeniedException $exception): Response
    {
        // You can customize this response to redirect the user or display a custom error message
        return new Response('Vous n\'avez pas les droits pour accéder à cette page.', 403);
    }
}