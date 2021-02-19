<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\Doctrine\Orm\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Class MultisearchFilter
 */
final class MultisearchFilter extends AbstractFilter
{
    public const PROPERTY_NAME = '_search';

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

        foreach (explode(' ', $value) as $index => $valueWord) {
            $valueWord = trim($valueWord);
            if ('' === $valueWord) {
                continue;
            }

            $qbSearch = $queryBuilder->expr()->orX();
            foreach ($this->properties as $propertyName => $propertyValue) {
                $propertyKey = $queryNameGenerator->generateParameterName($propertyName) . '_' . $index;
                $propertyName = $this->getPropertyName($propertyName);
                switch ($propertyValue) {
                    default:
                    case 'partial':
                        $qbSearch->add($queryBuilder->expr()->like($propertyName, ':' . $propertyKey));
                        $queryBuilder->setParameter($propertyKey, '%' . $valueWord . '%');
                        break;
                    case 'exact':
                        $qbSearch->add($queryBuilder->expr()->eq($propertyName, ':' . $propertyKey));
                        $queryBuilder->setParameter($propertyKey, $valueWord);
                        break;
                    case 'start':
                        $qbSearch->add($queryBuilder->expr()->like($propertyName, ':' . $propertyKey));
                        $queryBuilder->setParameter($propertyKey, $valueWord . '%');
                        break;
                    case 'end':
                        $qbSearch->add($queryBuilder->expr()->like($propertyName, ':' . $propertyKey));
                        $queryBuilder->setParameter($propertyKey, '%' . $valueWord);
                        break;
                }
            }

            $queryBuilder->andWhere($qbSearch);
        }

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
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Search in multiple fields (' . implode(', ', array_keys($this->properties)) . ')'
                ],
            ]
        ];
    }
}
