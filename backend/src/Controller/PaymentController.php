<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Entity\User;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    #[Route('/api/payment/{type}/{id}', name: 'create_payment', methods: ['POST'])]
    public function createPayment(Request $request, string $type, int $id, StripeService $stripeService): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 403);
        }

        $stripeSession = $stripeService->createCheckoutSession($type, $id, $user->getId());

        if (!$stripeSession) {
            return $this->json(['error' => 'Paramètres invalides'], 400);
        }

        return $this->json([
            'id' => $stripeSession->id,
            'url' => $stripeSession->url,
        ]);
    }

    #[Route('/api/payment/confirm', name: 'payment_confirm', methods: ['POST'])]
    public function confirmPurchase(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $type = $data['type'] ?? null;
        $id = $data['id'] ?? null;

        if (!$type || !$id) {
            return $this->json(['error' => 'Paramètres manquants'], 400);
        }

        $purchase = new Purchase();
        $purchase->setCustomer($user);
        $purchase->setCreatedAt(new \DateTimeImmutable());
        $purchase->setUpdatedAt(new \DateTimeImmutable());
        $purchase->setCreatedBy($user);
        $purchase->setUpdatedBy($user);

        if ($type === 'lesson') {
            $lesson = $entityManager->getRepository(Lesson::class)->find($id);
            if (!$lesson) return $this-json(['error' => 'Leçon introuvable'], 404);
            $purchase->setLesson($lesson);
        } elseif ($type === 'course') {
            $course = $entityManager->getRepository(Course::class)->find($id);
            if (!$course) return $this-json(['error' => 'Cursus introuvable'], 404);
            $purchase->setCourse($course);
        } else {
            return $this->json(['error' => 'Type invalide'], 404);
        }

        $entityManager->persist($purchase);
        $entityManager->flush();

        return $this->json(['message' => 'Achat confirmé']);
    }

    #[Route('/payment/success', name: 'payment_success', methods: ['GET'])]
    public function paymentSuccess(): JsonResponse
    {
        return $this->json(['message' => 'Paiement réussi'], 200);
    }

    #[Route('/payment/cancel', name: 'payment_cancel', methods: ['GET'])]
    public function paymentCancel(): JsonResponse
    {
        return $this->json(['message' => 'Paiement annulé']);
    }
}