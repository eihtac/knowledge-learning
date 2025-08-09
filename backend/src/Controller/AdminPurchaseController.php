<?php

namespace App\Controller;

use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use App\Repository\UserRepository;
use App\Repository\LessonRepository;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;

class AdminPurchaseController extends AbstractController
{
    #[Route('/api/admin/purchases', name: 'api_admin_purchases', methods: ['GET'])]
    public function getPurchases(PurchaseRepository $purchaseRepository): JsonResponse
    {
        $purchases = $purchaseRepository->findAll();
        $data = [];

        foreach ($purchases as $purchase) {
            $data[] = [
                'id' => $purchase->getId(),
                'lesson' => $purchase->getLesson() ? [
                    'id' => $purchase->getLesson()->getId(),
                    'title' => $purchase->getLesson()->getTitle(),
                ] : null,
                'course' => $purchase->getCourse() ? [
                    'id' => $purchase->getCourse()->getId(),
                    'title' => $purchase->getCourse()->getTitle(),
                ] : null,
                'customer' => [
                    'id' => $purchase->getCustomer()->getId(),
                    'name' => $purchase->getCustomer()->getName(),
                ],
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/admin/purchase', name: 'api_admin_add_purchase', methods: ['POST'])]
    public function addPurchase(Request $request, UserRepository $userRepository, LessonRepository $lessonRepository, CourseRepository $courseRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $admin = $security->getUser();

        if (!$admin) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['customerId']) || empty($data['customerId'])) {
            return $this->json(['error' => 'Client manquant'], 400);
        }

        if ((!isset($data['lessonId']) || empty($data['lessonId'])) && (!isset($data['courseId']) || empty($data['courseId']))) {
            return $this->json(['error' => 'Leçon ou cursus manquant'], 400);
        }

        $customer = $userRepository->find($data['customerId']);

        if (!$customer) {
            return $this->json(['error' => 'Client introuvable'], 404);
        }

        $lesson = null;
        $course = null;

        if (isset($data['lessonId']) && !empty($data['lessonId'])) {
            $lesson = $lessonRepository->find($data['lessonId']);

            if (!$lesson) {
                return $this->json(['error' => 'Leçon introuvable'], 404);
            }
        }

        if (isset($data['courseId']) && !empty($data['courseId'])) {
            $course = $courseRepository->find($data['courseId']);

            if (!$course) {
                return $this->json(['error' => 'Cursus introuvable'], 404);
            }
        }

        $purchase = new Purchase();
        $purchase->setCustomer($customer);
        $purchase->setLesson($lesson);
        $purchase->setCourse($course);
        $purchase->setCreatedAt(new \DateTimeImmutable());
        $purchase->setUpdatedAt(new \DateTimeImmutable());
        $purchase->setCreatedBy($admin);
        $purchase->setUpdatedBy($admin);

        $entityManager->persist($purchase);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/admin/purchase/{id}', name: 'api_admin_update_purchase', methods: ['PUT'])]
    public function updatePurchase(int $id, Request $request, PurchaseRepository $purchaseRepository, UserRepository $userRepository, LessonRepository $lessonRepository, CourseRepository $courseRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $admin = $security->getUser();

        if (!$admin) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $purchase = $purchaseRepository->find($id);

        if (!$purchase) {
            return $this->json(['error' => 'Achat introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['customerId']) || empty($data['customerId'])) {
            return $this->json(['error' => 'Client manquant'], 400);
        }

        if ((!isset($data['lessonId']) || empty($data['lessonId'])) && (!isset($data['courseId']) || empty($data['courseId']))) {
            return $this->json(['error' => 'Leçon ou cursus manquant'], 400);
        }

        $customer = $userRepository->find($data['customerId']);

        if (!$customer) {
            return $this->json(['error' => 'Client introuvable'], 404);
        }

        $lesson = null;
        $course = null;

        if (isset($data['lessonId']) && !empty($data['lessonId'])) {
            $lesson = $lessonRepository->find($data['lessonId']);

            if (!$lesson) {
                return $this->json(['error' => 'Leçon introuvable'], 404);
            }
        }

        if (isset($data['courseId']) && !empty($data['courseId'])) {
            $course = $courseRepository->find($data['courseId']);

            if (!$course) {
                return $this->json(['error' => 'Cursus introuvable'], 404);
            }
        }

        $purchase->setCustomer($customer);
        $purchase->setLesson($lesson);
        $purchase->setCourse($course);
        $purchase->setUpdatedAt(new \DateTimeImmutable());
        $purchase->setUpdatedBy($admin);

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $purchase->getId(),
            'lesson' => $purchase->getLesson() ? [
                'id' => $purchase->getLesson()->getId(),
                'title' => $purchase->getLesson()->getTitle(),
            ] : null,
            'course' => $purchase->getCourse() ? [
                'id' => $purchase->getCourse()->getId(),
                'title' => $purchase->getCourse()->getTitle(),
            ] : null,
            'customer' => [
                'id' => $purchase->getCustomer()->getId(),
                'name' => $purchase->getCustomer()->getName(),
            ],
        ]);
    }

    #[Route('/api/admin/purchase/{id}', name: 'api_admin_delete_purchase', methods: ['DELETE'])]
    public function deletePurchase(int $id, PurchaseRepository $purchaseRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $admin = $security->getUser();

        if (!$admin) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $purchase = $purchaseRepository->find($id);

        if (!$purchase) {
            return $this->json(['error' => 'Achat introuvable'], 404);
        }

        $entityManager->remove($purchase);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
}