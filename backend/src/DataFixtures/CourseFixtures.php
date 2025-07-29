<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\User;
use App\Entity\Topic;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\TopicFixtures;

class CourseFixtures extends Fixture implements DependentFixtureInterface
{
    public const COURSE_REFERENCE_PREFIX = 'course_';

    public function load(ObjectManager $manager): void
    {
        $json = file_get_contents(__DIR__ . '/courses.json');
        $coursesData = json_decode($json, true);

        $now = new \DateTimeImmutable();

        foreach ($coursesData as $index => $data) {
            $adminUser = $this->getReference(UserFixtures::ADMIN_USER_REFERENCE, User::class);
            $course = new Course();
            $course->setTitle($data['title']);
            $course->setPrice($data['price']);
            $course->setCreatedAt($now);
            $course->setUpdatedAt($now);
            $course->setCreatedBy($adminUser);
            $course->setUpdatedBy($adminUser);

            $topic = $this->getReference(TopicFixtures::TOPIC_REFERENCE_PREFIX . $data['topic_index'], Topic::class);
            $course->setTopic($topic);
            $topic->addCourse($course);

            $manager->persist($course);

            $this->addReference(self::COURSE_REFERENCE_PREFIX . $index, $course);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TopicFixtures::class
        ];
    }
}