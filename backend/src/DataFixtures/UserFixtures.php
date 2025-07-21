<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture 
{
    public const ADMIN_USER_REFERENCE = 'admin_user';
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setName('Admin');
        $admin->setEmail('admin@knowledge.com');
        $admin->setIsVerified(true);
        $admin->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'adminpassword');
        $admin->setPassword($hashedPassword);

        $now = new \DateTimeImmutable();
        $admin->setCreatedAt($now);
        $admin->setUpdatedAt($now);
        $admin->setCreatedBy($admin); 
        $admin->setUpdatedBy($admin);

        $manager->persist($admin);

        $this->addReference(self::ADMIN_USER_REFERENCE, $admin);

        $manager->flush();
    }
}