<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use PhpGuild\ApiBundle\Http\RequestHandler;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * Class AuthenticationSuccessListener
 */
class AuthenticationSuccessListener
{
    /** @var RequestHandler $requestHandler */
    private $requestHandler;

    /** @var int|mixed $ttl */
    private $ttl;

    /**
     * AuthenticationSuccessListener constructor.
     *
     * @param RequestHandler        $requestHandler
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        RequestHandler $requestHandler,
        ParameterBagInterface $parameterBag
    ) {
        $this->requestHandler = $requestHandler;
        $this->ttl = $parameterBag->get('lexik_jwt_authentication.token_ttl');
    }

    /**
     * onAuthenticationSuccessResponse
     *
     * @param AuthenticationSuccessEvent $event
     *
     * @throws ExceptionInterface
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();

        $event->setData($this->requestHandler->normalize($event->getUser()) + [
            'token' => [
                'access' => $data['token'],
                'type' => 'BEARER',
                'expires_in' => $this->ttl,
                'refresh' => $data['refresh_token'],
            ],
        ]);

        $event->getResponse()->headers->add([
            'content-type' => sprintf(
                '%s; charset=%s',
                $this->requestHandler->getContentType(),
                $this->requestHandler->getCharset()
            ),
        ]);
    }
}
