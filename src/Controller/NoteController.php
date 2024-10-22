<?php

namespace App\Controller;

use App\Attribute\TokenVerification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/note')]
class NoteController extends AbstractController
{
    #[Route('', name: 'app_note_create', methods: ['POST'])]
    public function create(): JsonResponse
    {
        return new JsonResponse(['message' => 'Create method reached']);
    }

    #[Route('', name: 'app_note_get', methods: ['GET'])]
    #[TokenVerification]
    public function getNotes(): JsonResponse
    {
        return new JsonResponse(['message' => 'Get notes method reached']);
    }
}
