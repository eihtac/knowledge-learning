<?php

namespace App\Controller;

use App\Entity\CompletedLesson;
use App\Entity\CompletedCourse;
use App\Entity\Certificate;
use App\Repository\CompletedLessonRepository;
use App\Repository\CompletedCourseRepository;
use App\Repository\CertificateRepository;
use App\Repository\LessonRepository;
use App\Repository\PurchaseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;

class UserLessonsController extends AbstractController
{
    #[Route('/api/user/lessons', name: 'app_user_lessons', methods: ['GET'])]
    public function getUserLessons(PurchaseRepository $purchaseRepository, CompletedLessonRepository $completedLessonRepository, CompletedCourseRepository $completedCourseRepository, LessonRepository $lessonRepository, Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $purchases = $purchaseRepository->findBy(['customer' => $user]);
        $grouped = [];

        foreach ($purchases as $purchase) {
            $lessons = [];

            if ($purchase->getLesson()) {
                $lessons[] = $purchase->getLesson();
            }

            if ($purchase->getCourse()) {
                foreach ($purchase->getCourse()->getLessons() as $lesson) {
                    $lessons[] = $lesson;
                }
            }

            foreach ($lessons as $lesson) {
                $lessonId = $lesson->getId();
                if (!$lessonId) continue;

                $course = $lesson->getCourse();
                if (!$course) continue;

                $topic = $course->getTopic();
                if (!$topic) continue;

                $topicTitle = $topic->getName();
                $courseTitle = $course->getTitle();

                if (!isset($grouped[$topicTitle])) {
                    $grouped[$topicTitle] = [];
                }

                if (!isset($grouped[$topicTitle][$courseTitle])) {
                    $grouped[$topicTitle][$courseTitle] = [];
                }

                if (!isset($grouped[$topicTitle][$courseTitle][$lessonId])) {
                    $grouped[$topicTitle][$courseTitle][$lessonId] = [
                        'id' => $lessonId,
                        'title' => $lesson->getTitle(),
                        'completed' => $completedLessonRepository->findOneBy([
                            'user' => $user,
                            'lesson' => $lesson
                        ]) !== null
                    ];
                }
            }
        }

        $final = [];

        foreach ($grouped as $topicTitle => $courses) {
            $formattedCourses = [];

            foreach ($courses as $courseTitle => $lessons) {
                $firstLesson = reset($lessons);
                $course = $lessonRepository->find($firstLesson['id'])?->getCourse();
                $courseCompleted = false;

                if ($course) {
                    $courseCompleted = $completedCourseRepository->findOneBy(['user' => $user, 'course' => $course]) !== null;
                }

                $formattedCourses[] = ['course' => $courseTitle, 'completed' => $courseCompleted, 'lessons' => array_values($lessons)];
            }

            $final[] = ['topic' => $topicTitle, 'courses' => $formattedCourses];
        }

        return $this->json($final);
    }

    #[Route('/api/user/lesson/{id}', name: 'app_user_lesson', methods: ['GET'])]
    public function getUserLesson(int $id, Security $security, LessonRepository $lessonRepository, PurchaseRepository $purchaseRepository, CompletedLessonRepository $completedLessonRepository): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $lesson = $lessonRepository->find($id);

        if (!$lesson) {
            return $this->json(['error' => 'Leçon introuvable'], 404);
        }

        $hasAccess = $purchaseRepository->findOneBy(['customer' => $user, 'lesson' => $lesson]) !== null;
        
        if (!$hasAccess && $lesson->getCourse()) {
            $hasAccess = $purchaseRepository->findOneBy(['customer' => $user, 'course' => $lesson->getCourse()]) !== null;
        }

        if (!$hasAccess) {
            return $this->json(['error' => 'Accès interdit à cette leçon'], 403);
        }

        return $this->json([
            'id' => $lesson->getId(),
            'title' => $lesson->getTitle(), 
            'content' => $lesson->getContent(), 
            'videoUrl' => $lesson->getVideoUrl(),
            'course' => $lesson->getCourse()?->getTitle(),
            'completed' => $completedLessonRepository->findOneBy([
                'user' => $user,
                'lesson' => $lesson
            ]) !== null
        ]);
    }

    #[Route('/api/user/lesson/{id}/complete', name: 'app_user_lesson_complete', methods: ['POST'])]
    public function completeLesson(int $id, Security $security, LessonRepository $lessonRepository, PurchaseRepository $purchaseRepository, CompletedLessonRepository $completedLessonRepository, CompletedCourseRepository $completedCourseRepository, CertificateRepository $certificateRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $lesson = $lessonRepository->find($id);

        if (!$lesson) {
            return $this->json(['error' => 'Leçon introuvable'], 404);
        }

        $hasAccess = $purchaseRepository->findOneBy(['customer' => $user, 'lesson' => $lesson]) !== null;
        
        if (!$hasAccess && $lesson->getCourse()) {
            $hasAccess = $purchaseRepository->findOneBy(['customer' => $user, 'course' => $lesson->getCourse()]) !== null;
        }

        if (!$hasAccess) {
            return $this->json(['error' => 'Accès interdit'], 403);
        }

        if ($completedLessonRepository->findOneBy(['user' => $user, 'lesson' => $lesson])) {
            return $this->json(['completed' => true]);
        }

        $completedLesson = new CompletedLesson();
        $completedLesson->setUser($user);
        $completedLesson->setLesson($lesson);
        $completedLesson->setCreatedAt(new \DateTimeImmutable());
        $completedLesson->setUpdatedAt(new \DateTimeImmutable());
        $completedLesson->setCreatedBy($user);
        $completedLesson->setUpdatedBy($user);
        $entityManager->persist($completedLesson);
        $entityManager->flush();

        $course = $lesson->getCourse();
        $allLessons = $lessonRepository->findBy(['course' => $course]);
        $allCompleted = true;

        foreach ($allLessons as $l) {
            if (!$completedLessonRepository->findOneBy(['user' => $user, 'lesson' => $l])) {
                $allCompleted = false;
                break;
            }
        }
        
        if ($allCompleted && !$completedCourseRepository->findOneBy(['user' => $user, 'course' => $course])) {
            $completedCourse = new CompletedCourse();
            $completedCourse->setUser($user);
            $completedCourse->setCourse($course);
            $completedCourse->setCreatedAt(new \DateTimeImmutable());
            $completedCourse->setUpdatedAt(new \DateTimeImmutable());
            $completedCourse->setCreatedBy($user);
            $completedCourse->setUpdatedBy($user);
            $entityManager->persist($completedCourse);
            $entityManager->flush();

            $topic = $course->getTopic();
            $allCourses = $topic->getCourses();
            $topicCompleted = true;

            foreach ($allCourses as $c) {
                if (!$completedCourseRepository->findOneBy(['user' => $user, 'course' => $c])) {
                    $topicCompleted = false;
                    break;
                }
            }

            if ($topicCompleted && !$certificateRepository->findOneBy(['user' => $user, 'topic' => $topic])) {
                $certificate = new Certificate();
                $certificate->setUser($user);
                $certificate->setTopic($topic);
                $certificate->setCreatedAt(new \DateTimeImmutable());
                $certificate->setUpdatedAt(new \DateTimeImmutable());
                $certificate->setCreatedBy($user);
                $certificate->setUpdatedBy($user);
                $entityManager->persist($certificate);
                $entityManager->flush();
            }
        }

        return $this->json(['completed' => true]);
    }
}