<?php

namespace App\EventListener;

use App\Repository\TokenRepository;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TokenVerificationListener
{
    private TokenRepository $tokenRepository;

    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Check if the route requires token verification
        if (!$request->attributes->get('_token_verification', false)) {
            return;
        }

        $token = $request->headers->get('Authorization');

        if (!$token) {
            $event->setResponse(new JsonResponse(['status' => 'UNAUTHORIZED', 'error' => 'No token provided'], Response::HTTP_UNAUTHORIZED));
            return;
        }

        // Remove 'Bearer ' if present
        $token = str_replace('Bearer ', '', $token);

        $validToken = $this->tokenRepository->findOneBy(['token' => $token, 'isRevoked' => false]);

        if (!$validToken) {
            $event->setResponse(new JsonResponse(['status' => 'UNAUTHORIZED', 'error' => 'Invalid or expired token'], Response::HTTP_UNAUTHORIZED));
            return;
        }

        $request->attributes->set('user', $validToken->getSession()->getUser());
    }
}
