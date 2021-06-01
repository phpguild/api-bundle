<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
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
     * @throws ExceptionInterface
     */
    public function normalize($data): array
    {
        return $this->normalizer->normalize($data, $this->request->getFormat($this->getContentType()));
    }

    /**
     * getResponse
     *
     * @param     $data
     * @param int $status
     *
     * @return Response
     *
     * @throws ExceptionInterface
     */
    public function getResponse($data, int $status = 200): Response
    {
        $data = $this->normalize($data);
        $responseFormat = \is_array($data) ? JsonResponse::class : Response::class;

        return new $responseFormat($data, $status, [
            'content-type' => sprintf('%s; charset=%s', $this->getContentType(), $this->getCharset()),
        ]);
    }

    /**
     * getCharset
     *
     * @return string
     */
    public function getCharset(): string
    {
        return 'utf-8';
    }

    /**
     * getContentType
     *
     * @return string
     */
    public function getContentType(): string
    {
        $accept = $this->request->headers->get('accept');

        switch ($accept) {
            case 'application/json':
            case 'application/ld+json':
                return $accept;
            default:
                return 'application/json';
        }
    }
}
