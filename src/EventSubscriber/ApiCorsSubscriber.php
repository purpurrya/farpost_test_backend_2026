<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiCorsSubscriber implements EventSubscriberInterface
{
    private const PREFIX = '/api';

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9999],
            KernelEvents::RESPONSE => ['onKernelResponse', -9999],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), self::PREFIX)) {
            return;
        }

        if ($request->getMethod() === Request::METHOD_OPTIONS) {
            $response = new Response('', Response::HTTP_NO_CONTENT);
            $this->applyHeaders($response, $request);
            $event->setResponse($response);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), self::PREFIX)) {
            return;
        }

        $this->applyHeaders($event->getResponse(), $request);
    }

    private function applyHeaders(Response $response, Request $request): void
    {
        $origin = $request->headers->get('Origin');
        if ($origin !== null && !$this->isAllowedOrigin($origin)) {
            return;
        }
        if ($origin !== null) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Vary', 'Origin');
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        $response->headers->set('Access-Control-Max-Age', '86400');
    }

    private function isAllowedOrigin(string $origin): bool
    {
        return (bool) preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#', $origin);
    }
}
