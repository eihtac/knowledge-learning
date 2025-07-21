<?php 

namespace App\DataFixtures;

use App\Entity\Lesson;
use App\Entity\User;
use App\Entity\Course;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\CourseFixtures;

class LessonFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $json = file_get_contents(__DIR__ . '/lessons.json');
        $lessonsData = json_decode($json, true);

        $now = new \DateTimeImmutable();

        foreach ($lessonsData as $data) {
            $adminUser = $this->getReference(UserFixtures::ADMIN_USER_REFERENCE, User::class);
            $lesson = new Lesson();
            $lesson->setTitle($data['title']);
            $lesson->setPrice($data['price']);
            $lesson->setContent($data['content']);
            $lesson->setVideoUrl($data['videoUrl']);
            $lesson->setCreatedAt($now);
            $lesson->setUpdatedAt($now);
            $lesson->setCreatedBy($adminUser);
            $lesson->setUpdatedBy($adminUser);

            $course = $this->getReference(CourseFixtures::COURSE_REFERENCE_PREFIX . $data['course_index'], Course::class);
            $lesson->setCourse($course);

            $manager->persist($lesson);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CourseFixtures::class
        ];
    }
}