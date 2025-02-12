<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Form\AdminType;
use App\Form\CreateAccountType;
use App\Form\LoginType;
use App\Form\AdminLoginType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends AbstractController
{
    #[Route('/create-account', name: 'create_account')]
    public function createAccount(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(CreateAccountType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $this->addFlash('error', 'A user with this email address already exists.');
                return $this->redirectToRoute('create_account');
            }
            // Hash the password
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            $user->setRole(Role::Client);
            $user->setDateCreation(new \DateTime());
            try {
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Account created successfully!');
                return $this->redirectToRoute('login');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'A user with this email address already exists.');
                return $this->redirectToRoute('create_account');
            }
        }
        return $this->render('user/client/create_account.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('error', 'Email ou mot de passe invalide. Veuillez réessayer.');
        }
        $lastUsername = $authenticationUtils->getLastUsername();
        $form = $this->createForm(LoginType::class);
        return $this->render('user/login.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/admin-login', name: 'admin_login')]
    public function loginAdmin(Request $request, AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, UserPasswordHasherInterface $passwordHasher): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('error', 'Email ou mot de passe invalide. Veuillez réessayer.');
        }

        $lastUsername = $authenticationUtils->getLastUsername();
        $form = $this->createForm(AdminLoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->get('_username')->getData();
            $password = $form->get('_password')->getData();

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $username]);
            if (!$user) {
                $this->addFlash('error', 'Email ou mot de passe invalide. Veuillez réessayer.');
                return $this->redirectToRoute('admin_login');
            }

            // Check if the user has the ROLE_ADMIN role
            if (!in_array(Role::Admin->value, $user->getRoles())) {
                $this->addFlash('error', 'Admin non existant');
                return $this->redirectToRoute('admin_login');
            }

            if (!$passwordHasher->isPasswordValid($user, $password)) {
                $this->addFlash('error', 'Email ou mot de passe invalide. Veuillez réessayer.');
                return $this->redirectToRoute('admin_login');
            }

            // Clear any existing token
            $tokenStorage->setToken(null);

            // Create a new authentication token
            $token = new UsernamePasswordToken($user, 'admin', $user->getRoles());
            $tokenStorage->setToken($token);

            // Ensure the user is authenticated
            $this->denyAccessUnlessGranted('ROLE_ADMIN');

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('user/admin/login.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/admin/logoutAdmin', name: 'admin_logout')]
    public function logoutAdmin(): void
    {
        // This method can remain empty - it will be intercepted by the logout handler
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('user/admin/dashboard.html.twig', [
            // Pass any data you need to the template
        ]);
    }

    #[Route('/admin/listAdmins', name: 'list_admin')]
    public function listAdmins(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $admins = $entityManager->getRepository(User::class)->findBy(['role' => Role::Admin]);
        $admin = new User();
        $form = $this->createForm(AdminType::class, $admin);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $admin->getEmail()]);
            if ($existingUser) {
                $this->addFlash('error', 'A user with this email address already exists.');
                return $this->redirectToRoute('list_admin');
            }
            // Hash password
            $hashedPassword = $passwordHasher->hashPassword($admin, $admin->getPassword());
            $admin->setPassword($hashedPassword);
            $admin->setRole(Role::Admin);
            $admin->setDateCreation(new \DateTime());
            $entityManager->persist($admin);
            $entityManager->flush();
            $this->addFlash('success', 'Admin created successfully!');
            return $this->redirectToRoute('list_admin');
        }
        return $this->render('user/admin/list_Admins.html.twig', [
            'formx' => $form->createView(),
            'admins' => $admins,
        ]);
    }

    #[Route('/admin/deleteAdmin/{id}', name: 'delete_admin')]
    public function deleteAdmin(EntityManagerInterface $entityManager, int $id): Response
    {
        $admin = $entityManager->getRepository(User::class)->find($id);
        if (!$admin) {
            throw $this->createNotFoundException('User non existant');
        }
        $entityManager->remove($admin);
        $entityManager->flush();
        return $this->redirectToRoute('list_admin');
    }

    #[Route('/admin/edit/{id}', name: 'edit_admin')]
    public function editAdmin(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, int $id): Response
    {
        $admin = $entityManager->getRepository(User::class)->find($id);
        if (!$admin) {
            throw $this->createNotFoundException('Admin not found');
        }
        $form = $this->createForm(AdminType::class, $admin);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData()) {
                $hashedPassword = $passwordHasher->hashPassword($admin, $admin->getPassword());
                $admin->setPassword($hashedPassword);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Admin updated successfully!');
            return $this->redirectToRoute('list_admin');
        }
        return $this->render('user/admin/edit_admin.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}