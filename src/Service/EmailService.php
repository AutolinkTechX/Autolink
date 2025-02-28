<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function envoyerRefusEmail(string $destinataire)
    {
    error_log('Envoi de l\'email à : ' . $destinataire); // ✅ Vérifie si la fonction est bien appelée

    $email = (new Email())
        ->from('farahbaklouti007@gmail.com')
        ->to($destinataire)
        ->subject('Accord refusé')
        ->text('Votre demande d’accord a été refusée.');

    $this->mailer->send($email);

    error_log('Email envoyé avec succès'); // ✅ Vérifie si l'email a été bien envoyé
}

}
