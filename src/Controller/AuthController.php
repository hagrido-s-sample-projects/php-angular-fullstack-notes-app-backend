<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Session;
use Symfony\Component\Uid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Token;
use App\Enum\TokenType;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
    }

    #[Route('/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                return new JsonResponse(['status' => 'EMPTY_FIELDS', 'error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
            }

            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $data['username']]);
            if ($existingUser) {
                return new JsonResponse(['status' => 'USERNAME_EXISTS', 'error' => 'Username already exists'], Response::HTTP_CONFLICT);
            }

            $existingEmail = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existingEmail) {
                return new JsonResponse(['status' => 'EMAIL_EXISTS', 'error' => 'Email already exists'], Response::HTTP_CONFLICT);
            }

            $user = new User();
            $user->setUsername($data['username'] ?? '');
            $user->setEmail($data['email'] ?? '');
            $user->setPassword($data['password'] ?? '');

            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                return new JsonResponse(['status' => 'VALIDATION_ERROR', 'errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }

            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse(['status' => 'SUCCESS', 'message' => 'User registered successfully'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'INTERNAL_ERROR', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/login', name: 'app_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

            if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
                return new JsonResponse(['status' => 'INVALID_CREDENTIALS', 'error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
            }

            $session = new Session();
            $session->setUser($user);

            $tokens = $session->regenerateTokens();

            $this->entityManager->persist($session);
            $this->entityManager->flush();

            return $this->json([
                'status' => 'SUCCESS',
                'message' => 'Login successful',
                'access_token' => $tokens['access_token']->getToken(),
                'refresh_token' => $tokens['refresh_token']->getToken()
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'INTERNAL_ERROR', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
