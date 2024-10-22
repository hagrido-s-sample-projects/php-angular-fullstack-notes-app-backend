<?php

namespace App\Controller;

use App\Attribute\TokenVerification;

use App\Entity\User;
use App\Entity\Note;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/note')]
class NoteController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'app_note_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $userId = $request->attributes->get('user');

        if (!$userId) {
            return new JsonResponse(['status' => 'USER_ID_NOT_FOUND', 'error' => 'User ID not found'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return new JsonResponse(['status' => 'USER_NOT_FOUND', 'error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true);
        $title = $body['title'] ?? '';
        $content = $body['content'] ?? ''; // Add this line

        if (empty($title)) {
            $title = 'Untitled Note ' . (new \DateTime())->format('d F Y H:i');
        }

        $note = new Note($user, $title);
        $note->setContent($content); // Add this line

        $this->entityManager->persist($note);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'SUCCESS', 'note' => $note->toArray()], Response::HTTP_CREATED);
    }

    #[Route('', name: 'app_note_get', methods: ['GET'])]
    #[TokenVerification]
    public function getNotes(Request $request): JsonResponse
    {
        $userId = $request->attributes->get('user');
        return new JsonResponse(['message' => 'Get notes method reached', 'user_id' => $userId]);
    }
}
