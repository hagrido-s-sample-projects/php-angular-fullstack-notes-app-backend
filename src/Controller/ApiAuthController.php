<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class ApiAuthController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/auth/register', name: 'app_api_auth', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$email || !$password) {
            return $this->json(['message' => 'Missing body parameters'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(['message' => 'User registered successfully'], Response::HTTP_CREATED);
    }

    #[Route('/api/auth/login', name: 'app_api_auth_login', methods: ['POST'])]
    public function login(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$password) {
            return $this->json(['message' => 'Missing body parameters'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user) {
            return $this->json(['message' => 'Invalid credentials user'], Response::HTTP_UNAUTHORIZED);
        }

        if ($password != $user->getPassword()) {
            return $this->json(['message' => 'Invalid credentials: \'' . $password . '\' , \'' . $user->getPassword() . '\''], Response::HTTP_UNAUTHORIZED);
        }
        
        return $this->json(['message' => 'Login successful'], Response::HTTP_OK);
    }
}
