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
                return new JsonResponse(['error' => 'Missing required fields', 'status' => 'EMPTY_FIELDS'], Response::HTTP_BAD_REQUEST);
            }

            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $data['username']]);
            if ($existingUser) {
                return new JsonResponse(['error' => 'Username already exists', 'status' => 'USERNAME_EXISTS'], Response::HTTP_CONFLICT);
            }

            $existingEmail = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existingEmail) {
                return new JsonResponse(['error' => 'Email already exists', 'status' => 'EMAIL_EXISTS'], Response::HTTP_CONFLICT);
            }

            $user = new User();
            $user->setUsername($data['username'] ?? '');
            $user->setEmail($data['email'] ?? '');
            $user->setPassword($data['password'] ?? '');

            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }

            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'User registered successfully', 'status' => 'SUCCESS'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), 'status' => 'INTERNAL_ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                return $this->json(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
            }

            $accessToken = Uuid::v4()->toRfc4122();
            $refreshToken = Uuid::v4()->toRfc4122();

            $session = new Session();
            $session->setUser($user);
            $session->setAccessToken([$accessToken]);
            $session->setRefreshToken($refreshToken);

            $this->entityManager->persist($session);
            $this->entityManager->flush();

            return $this->json([
                'status' => 'SUCCESS',
                'message' => 'Login successful',
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), 'status' => 'INTERNAL_ERROR'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
