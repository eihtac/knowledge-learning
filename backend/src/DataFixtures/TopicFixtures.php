<?php

namespace App\DataFixtures;

use App\Entity\Topic;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\UserFixtures;

class TopicFixtures extends Fixture implements DependentFixtureInterface
{
    public const TOPIC_REFERENCE_PREFIX = 'topic_';

    public function load(ObjectManager $manager): void
    {
        $json = file_get_contents(__DIR__ . '/topics.json');
        $topicsData = json_decode($json, true);

        $now = new \DateTimeImmutable();

        foreach ($topicsData as $index => $data) {
            $adminUser = $this->getReference(UserFixtures::ADMIN_USER_REFERENCE, User::class);
            $topic = new Topic();
            $topic->setName($data['name']);
            $topic->setCreatedAt($now);
            $topic->setUpdatedAt($now);
            $topic->setCreatedBy($adminUser);
            $topic->setUpdatedBy($adminUser);

            $manager->persist($topic);

            $this->addReference(self::TOPIC_REFERENCE_PREFIX . $index, $topic);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class
        ];
    }
}