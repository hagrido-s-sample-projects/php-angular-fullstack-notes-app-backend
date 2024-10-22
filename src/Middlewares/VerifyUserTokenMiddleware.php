<?php

namespace App\Middlewares;

use App\Attribute\PublicRoute;

use App\Entity\Token;


use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class VerifyUserTokenMiddleware implements EventSubscriberInterface
{
    private $requestStack;
    private $kernel;

    public function __construct(RequestStack $requestStack, KernelInterface $kernel)
    {
        $this->requestStack = $requestStack;
        $this->kernel = $kernel;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        $controller = $request->attributes->get('_controller');

        // Check if the route or controller is marked as public
        if ($this->isPublicRoute($controller)) {
            return;
        }

        $response = $this->verifyToken();
        if ($response !== null) {
            $event->setResponse($response);
        }
    }

    private function isPublicRoute($controller): bool
    {
        if (!$controller) {
            return false;
        }

        if (is_array($controller)) {
            $reflectionClass = new \ReflectionClass($controller[0]);
            $reflectionMethod = $reflectionClass->getMethod($controller[1]);

            if ($reflectionClass->getAttributes(PublicRoute::class) || $reflectionMethod->getAttributes(PublicRoute::class)) {
                return true;
            }
        } elseif (is_string($controller)) {
            $parts = explode('::', $controller);
            if (count($parts) === 2) {
                $reflectionClass = new \ReflectionClass($parts[0]);
                $reflectionMethod = $reflectionClass->getMethod($parts[1]);

                if ($reflectionClass->getAttributes(PublicRoute::class) || $reflectionMethod->getAttributes(PublicRoute::class)) {
                    return true;
                }
            }
        }

        return false;
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

        $entityManager = $this->kernel->getContainer()->get('doctrine')->getManager();
        $tokenEntity = $entityManager->getRepository(Token::class)->findOneBy(['token' => $token]);

        if (!$tokenEntity) {
            return new JsonResponse(['status' => 'INVALID_TOKEN', 'error' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        if ($tokenEntity->getCreatedAt() < new \DateTime('-7 days')) {
            return new JsonResponse(['status' => 'EXPIRED_TOKEN', 'error' => 'Token has expired'], Response::HTTP_UNAUTHORIZED);
        }

        $request->attributes->set('user', $tokenEntity->getSession()->getUser()->getId());

        return null;
    }
}
