<?php

namespace App\Security;

use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;

class EmailVerifier
{
    public function __construct(private VerifyEmailHelperInterface $helper, private MailerInterface $mailer, private UrlGeneratorInterface $urlGenerator, private RequestStack $requestStack)
    {}

    public function sendEmailConfirmation(string $verifyRouteName, User $user, Address $from, string $templatePath): void
    {
        $signatureComponents = $this->helper->generateSignature(
            $verifyRouteName,
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

        $email = (new TemplatedEmail())
            ->from($from)
            ->to($user->getEmail())
            ->subject('Activez votre compte Knowledge Learning')
            ->htmlTemplate($templatePath)
            ->context([
                'signedUrl' => $signatureComponents->getSignedUrl(),
                'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
                'expiresAtMessageData' => $signatureComponents->getExpirationMessageData(),
                'user' => $user
            ]);

        $this->mailer->send($email);
    }

    public function handleEmailConfirmation(Request $request, User $user): void
    {
        $this->helper->validateEmailConfirmationFromRequest(
            $request,
            $user->getId(),
            $user->getEmail()
        );
    }
}