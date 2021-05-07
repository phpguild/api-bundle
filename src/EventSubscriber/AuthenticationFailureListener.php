<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\EventSubscriber;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use PhpGuild\ApiBundle\Http\RequestHandler;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use ApiPlatform\Core\Bridge\Symfony\Routing\RouteNameGenerator;
use ApiPlatform\Core\Api\OperationType;
use Symfony\Component\String\UnicodeString;

/**
 * Class AuthenticationFailureListener
 */
class AuthenticationFailureListener
{
    /** @var RequestHandler $requestHandler */
    private $requestHandler;

    /**
     * AuthenticationFailureListener constructor.
     *
     * @param RequestHandler $requestHandler
     */
    public function __construct(RequestHandler $requestHandler)
    {
        $this->requestHandler = $requestHandler;
    }

    /**
     * onAuthenticationFailureResponse
     *
     * @param AuthenticationFailureEvent $event
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event): void
    {
        $event->setResponse($this->requestHandler->getResponse($event->getException(), 400));
    }
}
