<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Repository\LessonRepository;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;

class AdminLessonController extends AbstractController
{
    #[Route('/api/admin/lesson', name: 'api_admin_add_lesson', methods: ['POST'])]
    public function addLesson(Request $request, CourseRepository $courseRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
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

        if (!isset($data['content']) || empty($data['content'])) {
            return $this->json(['error' => 'Contenu manquant'], 400);
        }

        if (!isset($data['videoUrl']) || empty($data['videoUrl'])) {
            return $this->json(['error' => 'URL vidéo manquante'], 400);
        }

        if (!isset($data['courseId'])) {
            return $this->json(['error' => 'Cursus manquant'], 400);
        }

        $course = $courseRepository->find($data['courseId']);

        if (!$course) {
            return $this->json(['error' => 'Cursus introuvable'], 404);
        }

        $lesson = new Lesson();
        $lesson->setTitle($data['title']);
        $lesson->setPrice((float) $data['price']);
        $lesson->setContent($data['content']);
        $lesson->setVideoUrl($data['videoUrl']);
        $lesson->setCourse($course);
        $lesson->setCreatedAt(new \DateTimeImmutable());
        $lesson->setUpdatedAt(new \DateTimeImmutable());
        $lesson->setCreatedBy($user);
        $lesson->setUpdatedBy($user);

        $entityManager->persist($lesson);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/admin/lesson/{id}', name: 'api_admin_update_lesson', methods: ['PUT'])]
    public function updateLesson(int $id, Request $request, LessonRepository $lessonRepository, CourseRepository $courseRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $lesson = $lessonRepository->find($id);

        if (!$lesson) {
            return $this->json(['error' => 'Leçon introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['title']) || empty($data['title'])) {
            return $this->json(['error' => 'Titre manquant'], 400);
        }

        if (!isset($data['price']) || !is_numeric($data['price'])) {
            return $this->json(['error' => 'Prix manquant ou invalide'], 400);
        }

        if (!isset($data['content']) || empty($data['content'])) {
            return $this->json(['error' => 'Contenu manquant'], 400);
        }

        if (!isset($data['videoUrl']) || empty($data['videoUrl'])) {
            return $this->json(['error' => 'URL vidéo manquante'], 400);
        }

        if (!isset($data['courseId'])) {
            return $this->json(['error' => 'Cursus manquant'], 400);
        }

        $course = $courseRepository->find($data['courseId']);
        if (!$course) {
            return $this->json(['error' => 'Cursus introuvable'], 404);
        }

        $lesson->setTitle($data['title']);
        $lesson->setPrice((float) $data['price']);
        $lesson->setContent($data['content']);
        $lesson->setVideoUrl($data['videoUrl']);
        $lesson->setCourse($course);
        $lesson->setUpdatedAt(new \DateTimeImmutable());
        $lesson->setUpdatedBy($user);

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $lesson->getId(),
            'title' => $lesson->getTitle(),
            'price' => $lesson->getPrice(),
            'content' => $lesson->getContent(),
            'videoUrl' => $lesson->getVideoUrl(),
            'course' => [
                'id' => $course->getId(),
                'title' => $course->getTitle()
            ]
        ]);
    }

    #[Route('/api/admin/lesson/{id}', name: 'api_admin_delete_lesson', methods: ['DELETE'])]
    public function deleteLesson(int $id, LessonRepository $lessonRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $lesson = $lessonRepository->find($id);

        if (!$lesson) {
            return $this->json(['error' => 'Leçon introuvable'], 404);
        }

        $entityManager->remove($lesson);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
}