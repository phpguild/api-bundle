<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\Http;

use ApiPlatform\Core\Problem\Serializer\ErrorNormalizerTrait;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class RequestHandler
 */
final class RequestHandler
{
    /** @var NormalizerInterface $normalizer */
    private $normalizer;

    /** @var ?Request $request */
    private $request;

    /**
     * NormalizedResponse constructor.
     *
     * @param NormalizerInterface $normalizer
     * @param RequestStack        $requestStack
     */
    public function __construct(NormalizerInterface $normalizer, RequestStack $requestStack)
    {
        $this->normalizer = $normalizer;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * denormalize
     *
     * @param Request $request
     *
     * @return array
     *
     * @throws \JsonException
     */
    public function denormalize(Request $request): array
    {
        return json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * normalize
     *
     * @param $data
     *
     * @return array
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($data): array
    {
        $format = $this->request->getFormat($this->request->headers->get('accept')) ?: 'json';

        return $this->normalizer->normalize($data, $format);
    }

    /**
     * getResponse
     *
     * @param     $data
     * @param int $status
     *
     * @return JsonResponse
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getResponse($data, int $status = 200): JsonResponse
    {
        return new JsonResponse($this->normalize($data), $status);
    }
}
