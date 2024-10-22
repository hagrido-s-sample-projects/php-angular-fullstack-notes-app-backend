<?php

namespace App\Middlewares;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class VerifyUserTokenMiddleware implements EventSubscriberInterface
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $response = $this->verifyToken();
        if ($response !== null) {
            $event->setResponse($response);
        }
    }

    private function verifyToken(): ?JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return new JsonResponse(['status' => 'NO_REQUEST', 'error' => 'No request found'], Response::HTTP_BAD_REQUEST);
        }

        $token = $request->headers->get('Authorization');
        if (!$token) {
            return new JsonResponse(['status' => 'NO_TOKEN', 'error' => 'No token provided'], Response::HTTP_UNAUTHORIZED);
        }

        $token = str_replace('Bearer ', '', $token);
        if (empty($token)) {
            return new JsonResponse(['status' => 'INVALID_TOKEN', 'error' => 'Invalid token format'], Response::HTTP_UNAUTHORIZED);
        }

        // If everything is okay, return null to allow the request to continue
        return null;
    }
}