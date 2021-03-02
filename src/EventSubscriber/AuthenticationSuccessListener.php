<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\EventSubscriber;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\UrlGeneratorInterface;
use ApiPlatform\Core\JsonLd\ContextBuilderInterface;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;
use ApiPlatform\Core\Bridge\Symfony\Routing\RouteNameGenerator;
use ApiPlatform\Core\Api\OperationType;
use Symfony\Component\String\UnicodeString;

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
    public function __construct(
        ParameterBagInterface $parameterBag,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->ttl = $parameterBag->get('lexik_jwt_authentication.token_ttl');
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
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

        $data = $event->getData();

        $resourceName = (new UnicodeString((new \ReflectionClass($event->getUser()))->getShortName()))->snake();

        $event->setData(array_merge([
            '@id' => $this->urlGenerator->generate(
                RouteNameGenerator::generate('get', (string) $resourceName, OperationType::ITEM),
                [ 'id' => $userData['id'] ]
            ),
            'token' => [
                'access' => $data['token'],
                'type' => 'BEARER',
                'expires_in' => $this->ttl,
                'refresh' => $data['refresh_token'],
            ],
        ], $userData));
    }
}
