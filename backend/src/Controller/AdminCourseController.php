<?php

namespace App\Controller;

use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;

class AdminCourseController extends AbstractController
{
    #[Route('/api/admin/course', name: 'api_admin_add_course', methods: ['POST'])]
    public function addCourse(Request $request, TopicRepository $topicRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['title']) || empty($data['title'])) {
            return $this->json(['error' => 'Titre manquant'], 400);
        }

        if (!isset($data['price']) || !is_numeric($data['price'])) {
            return $this->json(['error' => 'Prix manquant ou invalide'], 400);
        }

        if (!isset($data['topicId'])) {
            return $this->json(['error' => 'Thème manquant'], 400);
        }

        $topic = $topicRepository->find($data['topicId']);

        if (!$topic) {
            return $this->json(['error' => 'Thème introuvable'], 404);
        }

        $course = new Course();
        $course->setTitle($data['title']);
        $course->setPrice((float) $data['price']);
        $course->setTopic($topic);
        $course->setCreatedAt(new \DateTimeImmutable());
        $course->setUpdatedAt(new \DateTimeImmutable());
        $course->setCreatedBy($user);
        $course->setUpdatedBy($user);

        $entityManager->persist($course);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/admin/course/{id}', name: 'api_admin_update_course', methods: ['PUT'])]
    public function updateCourse(int $id, Request $request, CourseRepository $courseRepository, TopicRepository $topicRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $course = $courseRepository->find($id);

        if (!$course) {
            return $this->json(['error' => 'Cursus introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['title']) || empty($data['title'])) {
            return $this->json(['error' => 'Titre manquant'], 400);
        }

        if (!isset($data['price']) || !is_numeric($data['price'])) {
            return $this->json(['error' => 'Prix manquant ou invalide'], 400);
        }

        if (!isset($data['topicId'])) {
            return $this->json(['error' => 'Thème manquant'], 400);
        }

        $newTopic = $topicRepository->find($data['topicId']);
        if (!$newTopic) {
            return $this->json(['error' => 'Thème introuvable'], 404);
        }

        $course->setTitle($data['title']);
        $course->setPrice((float) $data['price']);
        $course->setTopic($newTopic);
        $course->setUpdatedAt(new \DateTimeImmutable());
        $course->setUpdatedBy($user);

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $course->getId(),
            'title' => $course->getTitle(),
            'price' => $course->getPrice(),
            'topic' => [
                'id' => $course->getTopic()?->getId(),
                'name' => $course->getTopic()?->getName()
            ]
        ]);
    }

    #[Route('/api/admin/course/{id}', name: 'api_admin_delete_course', methods: ['DELETE'])]
    public function deleteCourse(int $id, CourseRepository $courseRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $course = $courseRepository->find($id);

        if (!$course) {
            return $this->json(['error' => 'Cursus introuvable'], 404);
        }

        $entityManager->remove($course);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
}