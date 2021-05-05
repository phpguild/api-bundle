<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\PostWriteEvent;

use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class PostWriteEventSubscriber
 */
class PostWriteEventSubscriber implements EventSubscriberInterface
{
    private $method;
    private $className;
    private $route;
    private $service;
    private $action;

    /**
     * PostWriteEventSubscriber constructor.
     *
     * @param string $method
     * @param string $className
     * @param string $route
     * @param mixed  $service
     * @param string $action
     */
    public function __construct(string $method, string $className, string $route, $service, string $action)
    {
        $this->method = $method;
        $this->className = $className;
        $this->route = $route;
        $this->service = $service;
        $this->action = $action;
    }

    /**
     * getSubscribedEvents
     *
     * @return \array[][]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [ 'postWriteAction', EventPriorities::POST_WRITE ],
        ];
    }

    /**
     * postWriteAction
     *
     * @param ViewEvent $event
     *
     * @throws PostWriteEventException
     */
    public function postWriteAction(ViewEvent $event): void
    {
        $object = $event->getControllerResult();
        $request = $event->getRequest();

        if (
            !$object instanceof $this->className
            || $this->method !== $request->getMethod()
            || $this->route !== $request->attributes->get('_route')
            || $this->className !== $request->attributes->get('_api_resource_class')
        ) {
            return;
        }

        if (!method_exists($this->service, $this->action)) {
            throw new PostWriteEventException(sprintf(
                'Missing method %s of service %s.',
                \get_class($this->service),
                $this->action
            ));
        }

        $this->service->{$this->action}($object, $request);
    }
}
