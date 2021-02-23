<?php

declare(strict_types=1);

namespace PhpGuild\ApiBundle\Doctrine\Orm\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AbstractFilter
 */
abstract class AbstractFilter extends AbstractContextAwareFilter
{
    private array $joins = [];

    /**
     * initJoins
     */
    protected function initJoins(): void
    {
        $this->joins = [];
    }

    /**
     * applyJoins
     *
     * @param QueryBuilder $queryBuilder
     */
    protected function applyJoins(QueryBuilder $queryBuilder): void
    {
        foreach ($this->joins as $alias => $join) {
            $queryBuilder->leftJoin($join, $alias);
        }
    }

    /**
     * getPropertyName
     *
     * @param string $propertyName
     * @param string $alias
     *
     * @return string
     */
    protected function getPropertyName(string $propertyName, string $alias = 'o'): string
    {
        $dot = strpos($propertyName, '.');

        if (false === $dot) {
            return $alias . '.' . $propertyName;
        }

        $objectName = substr($propertyName, 0, $dot);
        $objectAlias = $alias . '_' . $objectName;
        $this->joins[$objectAlias] = $alias . '.' . $objectName;
        $partialPropertyName = substr($propertyName, $dot + 1);

        if ($partialPropertyName) {
            return $this->getPropertyName($partialPropertyName, $objectAlias);
        }

        return $propertyName;
    }
}
