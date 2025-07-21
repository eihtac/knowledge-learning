<?php

namespace App\Controller;

use App\Repository\TopicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class CatalogController extends AbstractController
{
    #[Route('/api/catalog', name: 'api_catalog', methods: ['GET'])]
    public function index(TopicRepository $topicRepository): JsonResponse
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
                    ];
                }

                $topicData['courses'][] = $courseData;
            }

            $data[] = $topicData;
        }

        return $this->json($data);
    }
}