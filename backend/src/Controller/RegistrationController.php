<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {}

    #[Route('/api/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Requête invalide'], 400);
        }

        $constraint = new Assert\Collection([
            'name' => [new Assert\NotBlank(), new Assert\Length(min: 3)], 
            'email' => [new Assert\NotBlank(), new Assert\Email()],
            'password' => [new Assert\NotBlank(), new Assert\Length(min: 8)],
        ]);

        $violations = $validator->validate($data, $constraint);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return $this->json(['errors' => $errors], 400);
        }

        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['error' => 'Un utilisateur existe déjà avec cet email'], 400);
        }

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_CUSTOMER']);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new DateTimeImmutable());
        $user->setCreatedBy($user);
        $user->setUpdatedBy($user);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email', 
            $user, 
            new Address('noreply@knowledgelearning.com', 'Knowledge Learning'),
            'emails/confirmation_email.html.twig'
        );

        return $this->json(['message' => 'Inscription réussie ! Un mail de vérification vient de vous être envoyé'], 201);
    }

    #[Route('/verify', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $userId = $request->query->get('id');

        if (!$userId) {
            return $this->redirect('http://localhost:4200/');
        }

        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return $this->redirect('http://localhost:4200/');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $e) {
            return $this->redirect('http://localhost:4200/login?error= Le lien de confirmation est invalide ou expiré');
        }

        $user->setIsVerified(true);
        $entityManager->flush();

        return $this->redirect('http://localhost:4200/login?message= Votre compte a bien été activé ! Merci');
    }

    #[Route('/api/resend-verification', name: 'app_resend_verification', methods: ['POST'])]
    public function resendVerification(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'])) {
            return $this->json(['error' => 'Email requis'], 400);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (!$user) {
            return $this->json(['error' => 'Aucun compte ne correspond à cet email'], 404);
        }

        if ($user->isVerified()) {
            return $this->json(['message' => 'Votre compte est déjà activé'], 200);
        }

        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email', 
            $user, 
            new Address('noreply@knowledgelearning.com', 'Knowledge Learning'),
            'emails/confirmation_email.html.twig'
        );

        return $this->json(['message' => 'Un nouvel email de vérification vous a été envoyé'], 200);
    }
}