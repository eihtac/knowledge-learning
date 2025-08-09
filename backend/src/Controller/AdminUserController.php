<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUserController extends AbstractController
{
    #[Route('/api/admin/users', name: 'api_admin_users', methods: ['GET'])]
    public function getUsers(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'isVerified' => $user->isVerified(),
                'roles' => $user->getRoles(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/admin/user', name: 'api_admin_add_user', methods: ['POST'])]
    public function addUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Security $security): JsonResponse
    {
        $admin = $security->getUser();

        if (!$admin) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || empty($data['name'])) {
            return $this->json(['error' => 'Nom manquant'], 400);
        }

        if (!isset($data['email']) || empty($data['email'])) {
            return $this->json(['error' => 'Email manquant'], 400);
        }

        if (!isset($data['password']) || empty($data['password'])) {
            return $this->json(['error' => 'Mot de passe manquant'], 400);
        }

        if (!isset($data['roles']) || empty($data['roles'])) {
            return $this->json(['error' => 'Rôle manquant'], 400);
        }

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setRoles($data['roles']);
        $user->setIsVerified((bool)$data['isVerified']);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());
        $user->setCreatedBy($admin);
        $user->setUpdatedBy($admin);
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/admin/user/{id}', name: 'api_admin_update_user', methods: ['PUT'])]
    public function updateUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Security $security): JsonResponse
    {
        $admin = $security->getUser();

        if (!$admin) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || empty($data['name'])) {
            return $this->json(['error' => 'Nom manquant'], 400);
        }

        if (!isset($data['email']) || empty($data['email'])) {
            return $this->json(['error' => 'Email manquant'], 400);
        }

        if (!isset($data['roles']) || empty($data['roles'])) {
            return $this->json(['error' => 'Rôle manquant'], 400);
        }

        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setRoles($data['roles']);
        $user->setIsVerified((bool)$data['isVerified']);
        $user->setUpdatedAt(new \DateTimeImmutable());
        $user->setUpdatedBy($admin);

        if (!empty($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'isVerified' => $user->isVerified()
        ]);
    }

    #[Route('/api/admin/user/{id}', name: 'api_admin_delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $admin = $security->getUser();

        if (!$admin) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
}