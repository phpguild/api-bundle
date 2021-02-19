<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class AuthenticationSuccessListener
 */
class AuthenticationSuccessListener
{
    private int $ttl;
    private SerializerInterface $serializer;

    /**
     * AuthenticationSuccessListener constructor.
     *
     * @param ParameterBagInterface $parameterBag
     * @param SerializerInterface   $serializer
     */
    public function __construct(ParameterBagInterface $parameterBag, SerializerInterface $serializer)
    {
        $this->ttl = $parameterBag->get('lexik_jwt_authentication.token_ttl');
        $this->serializer = $serializer;
    }

    /**
     * onAuthenticationSuccessResponse
     *
     * @param AuthenticationSuccessEvent $event
     *
     * @throws \JsonException
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        //@TODO Find best way
        $userData = json_decode(
            $this->serializer->serialize($event->getUser(), 'json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $event->setData(array_merge([
            'token' => [
                'access' => $event->getData()['token'],
                'type' => 'BEARER',
                'expires_in' => $this->ttl,
            ],
        ], $userData));
    }
}
