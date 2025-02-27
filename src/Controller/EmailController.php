<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use GuzzleHttp\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use App\Service\EmailService;

class EmailController extends AbstractController
{
    #[Route('/send-email/{to}', name: 'send_email')]
    public function sendEmail(string $to)
    {
        // Load the Brevo configuration
        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', $_ENV['EMAIL_API'])
            ->setApiKey('partner-key', $_ENV['EMAIL_API']);

        // Create the API instance
        $apiInstance = new TransactionalEmailsApi(
            new Client(),
            $config
        );

        // Define the email data
        $sendSmtpEmail = new \Brevo\Client\Model\SendSmtpEmail([
            'subject' => 'Email from the PHP SDK!',
            'sender' => ['email' => 'youssef.baizigue@outlook.fr'],
            'to' => [['email' => $to]],
            'htmlContent' => '<html><body><h1>This is a transactional email {{params.bodyMessage}}</h1></body></html>',
            'params' => ['bodyMessage' => 'made just for you!']
        ]);

        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            return new JsonResponse(['message' => 'Email sent successfully', 'result' => $result], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Exception when calling TransactionalEmailsApi->sendTransacEmail: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/forgot-password', name: 'forgot_password')]
    public function requestResetPassword(Request $request, EntityManagerInterface $entityManager, EmailService $emailService): Response
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $form->addError(new FormError('No user found with this email address.'));
            } else {
                $token = Uuid::v4()->__toString();
                $user->setResetToken($token);
                $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));
                $entityManager->flush();
                $emailService->sendResetEmail($user, $token);
                $this->addFlash('success', 'A reset link has been sent to your email.');
                return $this->render('user/reset_password/request.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->render('user/reset_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function resetPassword(Request $request, string $token, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('No user found with this reset token.');
        }

        if ($user->getResetTokenExpiresAt() < new \DateTime('now')) {
            $this->addFlash('error', 'The reset token has expired. Please request a new one.');
            return $this->redirectToRoute('forgot_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);
            $entityManager->flush();
            $this->addFlash('success', 'Your password has been reset successfully.');
            return $this->redirectToRoute('login');
        }

        return $this->render('user/reset_password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verify-account/{token}', name: 'verify_account')]
    public function verifyAccount(EntityManagerInterface $entityManager, string $token): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('No user found with this verification token.');
        }

        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $entityManager->flush();

        $this->addFlash('success', 'Your account has been verified successfully. You can now log in.');
        return $this->redirectToRoute('login');
    }

}