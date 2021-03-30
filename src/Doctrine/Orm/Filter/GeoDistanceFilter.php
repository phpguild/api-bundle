<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\Doctrine\Orm\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Class GeoDistanceFilter
 */
final class GeoDistanceFilter extends AbstractFilter
{
    public const PROPERTY_NAME = '_distance';

    /** @var string $latPropertyName */
    private $latPropertyName;

    /** @var string $lngPropertyName */
    private $lngPropertyName;

    /**
     * GeoDistanceFilter constructor.
     *
     * @param ManagerRegistry             $managerRegistry
     * @param RequestStack|null           $requestStack
     * @param LoggerInterface|null        $logger
     * @param array|null                  $properties
     * @param NameConverterInterface|null $nameConverter
     * @param null                        $latPropertyName
     * @param null                        $lngPropertyName
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        ?RequestStack $requestStack = null,
        LoggerInterface $logger = null,
        array $properties = null,
        NameConverterInterface $nameConverter = null,
        $latPropertyName = null,
        $lngPropertyName = null
    ) {
        parent::__construct($managerRegistry, $requestStack, $logger, $properties, $nameConverter);

        $this->latPropertyName = $latPropertyName ?? 'latitude';
        $this->lngPropertyName = $lngPropertyName ?? 'longitude';
    }

    /**
     * filterProperty
     *
     * @param string                      $property
     * @param                             $value
     * @param QueryBuilder                $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string                      $resourceClass
     * @param string|null                 $operationName
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        if (self::PROPERTY_NAME !== $property) {
            return;
        }

        $this->initJoins();

        $latValue = $value['lat'] ?? null;
        $lngValue = $value['lng'] ?? null;
        $nearValue = $value['near'] ?? null;

        if (null === $latValue || null === $lngValue) {
            return;
        }

        $qbSearch = $queryBuilder->expr()->andX();

        $distancePropertyName = 'distance';
        $nearPropertyKey = $queryNameGenerator->generateParameterName('near');

        $lngPropertyKey = $queryNameGenerator->generateParameterName($this->lngPropertyName);
        $lngPropertyName = $this->getPropertyName($this->lngPropertyName);

        $latPropertyKey = $queryNameGenerator->generateParameterName($this->latPropertyName);
        $latPropertyName = $this->getPropertyName($this->latPropertyName);

        $queryBuilder->setParameter($latPropertyKey, (float) $latValue);
        $queryBuilder->setParameter($lngPropertyKey, (float) $lngValue);

        $distanceQuery = '( 6371 * acos(cos(radians(:' . $latPropertyKey . '))' .
            '* cos( radians( ' . $latPropertyName . ' ) )' .
            '* cos( radians( ' . $lngPropertyName . ' )
                - radians(:' . $lngPropertyKey . ') )
                + sin( radians(:' . $latPropertyKey . ') )' .
            '* sin( radians( ' . $latPropertyName . ' ) ) ) )';

        $queryBuilder->addSelect($distanceQuery . ' AS HIDDEN ' . $distancePropertyName);

        if (null !== $nearValue) {
            $qbSearch->add($distanceQuery . ' <= :' . $nearPropertyKey);
            $queryBuilder->setParameter($nearPropertyKey, (float) $nearValue);
            $queryBuilder->andWhere($qbSearch);
        }

        $queryBuilder->addOrderBy($distancePropertyName, 'ASC');

        $this->applyJoins($queryBuilder);
    }

    /**
     * getDescription
     *
     * @param string $resourceClass
     *
     * @return array[]
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            self::PROPERTY_NAME => [
                'property' => self::PROPERTY_NAME,
                'type' => 'array',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter by distance'
                ],
            ]
        ];
    }
}
