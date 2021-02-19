<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle;

use PhpGuild\ApiBundle\DependencyInjection\PhpGuildApiExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class PhpGuildApiBundle
 */
class PhpGuildApiBundle extends Bundle
{
    /**
     * getContainerExtension
     *
     * @return ExtensionInterface
     */
    public function getContainerExtension(): ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new PhpGuildApiExtension();
        }

        return $this->extension;
    }
}
