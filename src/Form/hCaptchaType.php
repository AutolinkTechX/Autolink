<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class hCaptchaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('response', HiddenType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'h-captcha',
                    'data-sitekey' => $options['site_key'],
                ],
                'constraints' => [
                    new Assert\Callback([$this, 'validateCaptcha']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'site_key' => '%env(HCAPTCHA_SITE_KEY)%',
            'secret_key' => '%env(HCAPTCHA_SECRET_KEY)%',
        ]);
    }

    public function validateCaptcha($value, ExecutionContextInterface $context, $payload): void
    {
        $options = $context->getRoot()->getConfig()->getOptions();
        $secretKey = $options['secret_key'];

        if (!$this->isCaptchaValid($value, $secretKey)) {
            $context->buildViolation('CAPTCHA validation failed. Please try again.')
                    ->addViolation();
        }
    }

    private function isCaptchaValid(string $response, string $secretKey): bool
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://hcaptcha.com/siteverify");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'secret' => $secretKey,
            'response' => $response,
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        return $result['success'] ?? false;
    }
}