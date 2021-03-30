<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use PhpGuild\UserBundle\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class MeSubscriber
 */
class MeSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /**
     * UserMeSubscriber constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * getSubscribedEvents
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                [ 'preRead', EventPriorities::PRE_READ ],
            ],
        ];
    }

    /**
     * preRead
     *
     * @param RequestEvent $event
     */
    public function preRead(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        /** @var UserInterface $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $identifier = $request->attributes->get('id');
        $resourceClass = $request->attributes->get('_api_resource_class');

        if (
            empty($identifier)
            || empty($resourceClass)
            || 'me' !== $identifier
            || !is_a($resourceClass, UserInterface::class, true)
        ) {
            return;
        }

        $request->attributes->set('id', $user->getId());
    }
}
