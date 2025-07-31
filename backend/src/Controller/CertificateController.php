<?php

namespace App\Controller;

use App\Repository\CertificateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class CertificateController extends AbstractController
{
    #[Route('/api/user/certificates', name: 'app_user_certificates', methods: ['GET'])]
    public function getUserCertificates(CertificateRepository $certificateRepository, Security $security): JsonResponse 
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $certificates = $certificateRepository->findBy(['user' => $user]);

        $data = [];

        foreach ($certificates as $certificate) {
            $topic = $certificate->getTopic();
            $courses = $topic->getCourses();

            $courseData = [];

            foreach ($courses as $course) {
                $lessons = $course->getLessons();
                $lessonTitles = [];

                foreach ($lessons as $lesson) {
                    $lessonTitles[] = $lesson->getTitle();
                }

                $courseData[] = [
                    'courseTitle' => $course->getTitle(), 
                    'lessons' => $lessonTitles,
                ];
            }

            $data[] = [
                'certificateId' => $certificate->getId(),
                'topicTitle' => $topic->getName(), 
                'obtainedAt' => $certificate->getCreatedAt()?->format('d-m-Y'),
                'courses' => $courseData,
            ];
        }

        return $this->json($data);
    }
}