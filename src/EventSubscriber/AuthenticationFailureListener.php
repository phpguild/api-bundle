<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use PhpGuild\ApiBundle\Http\RequestHandler;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

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
     * @throws ExceptionInterface
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event): void
    {
        $event->setResponse($this->requestHandler->getResponse($event->getException(), 400));
    }
}
