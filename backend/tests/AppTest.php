<?php

namespace App\Tests;

use App\Entity\User;
use App\Entity\Topic;
use App\Entity\Course;
use App\Entity\Purchase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppTest extends WebTestCase
{
    public function testUserRegistration(): void 
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setName('Jean Test');
        $user->setEmail('jean@test.com');
        $user->setPassword($hasher->hashPassword($user, 'PasswordTest'));
        $user->setIsVerified(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($user);
        $entityManager->flush();

        $savedUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'jean@test.com']);

        $this->assertNotNull($savedUser, 'Utilisateur enregistré avec succès !');
        $this->assertNotSame('PasswordTest', $savedUser->getPassword(), 'Mot de passe hashé avec succès !');
    }

    public function testUserLogin(): void 
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'jean@test.com']);

        if (!$user) {
            $user = new User();
            $user->setName('Jean Test');
            $user->setEmail('jean@test.com');
            $user->setPassword($hasher->hashPassword($user, 'PasswordTest'));
            $user->setIsVerified(false);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($user);
            $entityManager->flush();
        }

        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'email' => 'jean@test.com',
            'password' => 'PasswordTest'
        ]));

        $this->assertSame(200, $client->getResponse()->getStatusCode(), 'Connexion réussie !');
    }

    public function testPurchase(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $customer = $entityManager->getRepository(User::class)->findOneBy(['email' => 'jean@test.com']);
        
        if (!$customer) {
            $hasher = $container->get(UserPasswordHasherInterface::class);
            $customer = new User();
            $customer->setName('Jean Test');
            $customer->setEmail('jean@test.com');
            $customer->setPassword($hasher->hashPassword($customer, 'PasswordTest'));
            $customer->setIsVerified(true);
            $customer->setCreatedAt(new \DateTimeImmutable());
            $customer->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->persist($customer);
        }

        $topic = new Topic();
        $topic->setName('Topic Test');
        $topic->setCreatedAt(new \DateTimeImmutable());
        $topic->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->persist($topic);

        $course = new Course();
        $course->setTitle('Course Test');
        $course->setPrice(1000);
        $course->setTopic($topic);
        $course->setCreatedAt(new \DateTimeImmutable());
        $course->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->persist($course);

        $entityManager->flush();

        $purchase = new Purchase();
        $purchase->setCustomer($customer);
        $purchase->setCourse($course); 
        $purchase->setCreatedAt(new \DateTimeImmutable());
        $purchase->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->persist($purchase);
        $entityManager->flush();

        $savedPurchase = $entityManager->getRepository(Purchase::class)->findOneBy([
            'customer' => $customer,
            'course' => $course
        ]);

        $this->assertNotNull($savedPurchase, 'Achat enregistré avec succès !');
    }   
}