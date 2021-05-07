<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\Serializer;

use ApiPlatform\Core\Problem\Serializer\ErrorNormalizerTrait;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Converts {@see \Exception} or {@see FlattenException} to a JSON error representation.
 */
final class JsonErrorNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    use ErrorNormalizerTrait;

    public const FORMAT = 'json';
    public const TITLE = 'title';

    /** @var bool $debug */
    private $debug;

    /** @var array|string[] $defaultContext */
    private $defaultContext = [
        self::TITLE => 'An error occurred',
    ];

    /**
     * JsonErrorNormalizer constructor.
     *
     * @param bool  $debug
     * @param array $defaultContext
     */
    public function __construct(bool $debug = false, array $defaultContext = [])
    {
        $this->debug = $debug;
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'title' => $context[self::TITLE] ?? $this->defaultContext[self::TITLE],
            'description' => $this->getErrorMessage($object, $context, $this->debug),
        ];

        if (null !== $errorCode = $this->getErrorCode($object)) {
            $data['code'] = $errorCode;
        }

        if ($this->debug && null !== $trace = $object->getTrace()) {
            $data['trace'] = $trace;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return self::FORMAT === $format && (
                $data instanceof \Exception
                || $data instanceof FlattenException
            );
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
