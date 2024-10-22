<?php

namespace App\Controller;

use App\Attribute\TokenVerification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Token;

#[Route('/api/note')]
class NoteController extends AbstractController
{
    #[Route('', name: 'app_note_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $userId = $request->attributes->get('user');
        return new JsonResponse(['message' => 'Create note method reached', 'user_id' => $userId]);
    }

    #[Route('', name: 'app_note_get', methods: ['GET'])]
    #[TokenVerification]
    public function getNotes(Request $request): JsonResponse
    {
        $userId = $request->attributes->get('user');
        return new JsonResponse(['message' => 'Get notes method reached', 'user_id' => $userId]);
    }
}
