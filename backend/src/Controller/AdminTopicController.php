<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;

class AdminTopicController extends AbstractController
{
    #[Route('/api/admin/lessons', name: 'api_admin_lessons', methods: ['GET'])]
    public function getLessons(TopicRepository $topicRepository): JsonResponse
    {
        $topics = $topicRepository->findAll();
        $data = [];

        foreach ($topics as $topic) {
            $topicData = [
                'id' => $topic->getId(),
                'name' => $topic->getName(),
                'courses' => [],
            ];

            foreach ($topic->getCourses() as $course) {
                $courseData = [
                    'id' => $course->getId(),
                    'title' => $course->getTitle(),
                    'price' => $course->getPrice(),
                    'lessons' => [], 
                ];

                foreach ($course->getLessons() as $lesson) {
                    $courseData['lessons'][] = [
                        'id' => $lesson->getId(),
                        'title' => $lesson->getTitle(),
                        'price' => $lesson->getPrice(),
                        'content' => $lesson->getContent(),
                        'videoUrl' => $lesson->getVideoUrl(),
                    ];
                }

                $topicData['courses'][] = $courseData;
            }

            $data[] = $topicData;
        }

        return $this->json($data);
    }

    #[Route('/api/admin/topic', name: 'api_admin_add_topic', methods: ['POST'])]
    public function addTopic(Request $request, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || empty($data['name'])) {
            return $this->json(['error' => 'Nom manquant'], 400);
        }

        $topic = new Topic();
        $topic->setName($data['name']);
        $topic->setCreatedAt(new \DateTimeImmutable());
        $topic->setUpdatedAt(new \DateTimeImmutable());
        $topic->setCreatedBy($user);
        $topic->setUpdatedBy($user);

        $entityManager->persist($topic);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/admin/topic/{id}', name: 'api_admin_update_topic', methods: ['PUT'])]
    public function updateTopic(int $id, Request $request, TopicRepository $topicRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $topic = $topicRepository->find($id);

        if (!$topic) {
            return $this->json(['error' => 'Thème introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || empty($data['name'])) {
            return $this->json(['error' => 'Nom manquant'], 400);
        }

        $topic->setName($data['name']);
        $topic->setUpdatedAt(new \DateTimeImmutable());
        $topic->setUpdatedBy($user);

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $topic->getId(),
            'name' => $topic->getName(),
        ]);
    }

    #[Route('/api/admin/topic/{id}', name: 'api_admin_delete_topic', methods: ['DELETE'])]
    public function deleteTopic(int $id, TopicRepository $topicRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $topic = $topicRepository->find($id);

        if (!$topic) {
            return $this->json(['error' => 'Thème introuvable'], 404);
        }

        $entityManager->remove($topic);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
}