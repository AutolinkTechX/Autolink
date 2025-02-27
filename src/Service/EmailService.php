<?php

namespace App\Service;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use GuzzleHttp\Client;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Uid\Uuid;
    

class EmailService
{
    private $urlGenerator;
    private $emailApi;

    public function __construct(UrlGeneratorInterface $urlGenerator, string $emailApi)
    {
        $this->urlGenerator = $urlGenerator;
        $this->emailApi = $emailApi;
    }

    public function sendVerificationEmail(User $user, string $token)
    {
        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', $this->emailApi)
            ->setApiKey('partner-key', $this->emailApi);

        $apiInstance = new TransactionalEmailsApi(
            new Client(),
            $config
        );

        $verificationUrl = $this->urlGenerator->generate('verify_account', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $sendSmtpEmail = new \Brevo\Client\Model\SendSmtpEmail([
            'subject' => 'Verify Your Account',
            'sender' => ['name' => 'Autolink', 'email' => 'youssef.baizigue@outlook.fr'],
            'to' => [['name' => $user->getName(), 'email' => $user->getEmail()]],
            'htmlContent' => "<html><body><p>Hello " . $user->getName() . ",</p><p>Click the link below to verify your account:</p><p><a href=\"$verificationUrl\">Verify Your Account</a></p></body></html>"
        ]);

        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            return new \Symfony\Component\HttpFoundation\JsonResponse(['message' => 'Email sent successfully', 'result' => $result], 200);
        } catch (\Exception $e) {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'Exception when calling TransactionalEmailsApi->sendTransacEmail: ' . $e->getMessage()], 500);
        }
    }

    public function sendResetEmail(User $user, string $token)
    {
        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', $this->emailApi)
            ->setApiKey('partner-key', $this->emailApi);

        $apiInstance = new TransactionalEmailsApi(
            new Client(),
            $config
        );

        $resetUrl = $this->urlGenerator->generate('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $sendSmtpEmail = new \Brevo\Client\Model\SendSmtpEmail([
            'subject' => 'Password Reset Request',
            'sender' => ['name' => 'Autolink', 'email' => 'youssef.baizigue@outlook.fr'],
            'to' => [['name' => $user->getName(), 'email' => $user->getEmail()]],
            'htmlContent' => "<html><body><p>Hello " . $user->getName() . ",</p><p>Click the link below to reset your password:</p><p><a href=\"$resetUrl\">Reset Your Password</a></p></body></html>"
        ]);

        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            return new JsonResponse(['message' => 'Email sent successfully', 'result' => $result], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Exception when calling TransactionalEmailsApi->sendTransacEmail: ' . $e->getMessage()], 500);
        }
    }
}