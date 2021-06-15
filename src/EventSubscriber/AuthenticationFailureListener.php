<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use PhpGuild\ApiBundle\Http\RequestHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AuthenticationFailureListener
 */
class AuthenticationFailureListener
{
    /** @var RequestHandler $requestHandler */
    private $requestHandler;

    /** @var TranslatorInterface $translator */
    private $translator;

    /**
     * AuthenticationFailureListener constructor.
     *
     * @param RequestHandler      $requestHandler
     * @param TranslatorInterface $translator
     */
    public function __construct(RequestHandler $requestHandler, TranslatorInterface $translator)
    {
        $this->requestHandler = $requestHandler;
        $this->translator = $translator;
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
        $exception = $event->getException();
        if ($exception instanceof BadCredentialsException) {
            $exception = new BadCredentialsException(
                $this->translator->trans($exception->getMessage(), [], 'security'),
                $exception->getCode(),
                $exception
            );
        }

        $event->setResponse(
            $this->requestHandler->getResponse($exception, Response::HTTP_UNAUTHORIZED)
        );
    }
}
