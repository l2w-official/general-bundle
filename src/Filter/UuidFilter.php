<?php

namespace LearnToWin\GeneralBundle\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Uid\Uuid;

/**
 * We might want to move this to the General Bundle, until api-platform fixes the SearchFilter.
 */
class UuidFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     * @return array<mixed>
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];

        $properties = $this->getProperties();

        if (null === $properties) {
            $properties = array_fill_keys($this->getClassMetadata($resourceClass)->getFieldNames(), null);
        }

        foreach ($properties as $property => $unused) {
            if (!$this->isPropertyMapped($property, $resourceClass)) {
                continue;
            }

            $filterParameterNames = [$property, $property . '[]'];

            foreach ($filterParameterNames as $filterParameterName) {
                $description[$filterParameterName] = [
                    'property' => $property,
                    'type' => 'uuid',
                    'required' => false,
                    'strategy' => 'exact',
                    'is_collection' => str_ends_with((string)$filterParameterName, '[]'),
                    'swagger' => [
                        'type' => 'uuid',
                    ],
                ];
            }
        }

        return $description;
    }

    /**
     * @inheritDoc
     * @param mixed $value
     * @param array<mixed> $context
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        if (
            !$this->isPropertyEnabled($property, $resourceClass) ||
            !$this->isPropertyMapped($property, $resourceClass)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $field = $property;

        if ($this->isPropertyNested($property, $resourceClass)) {
            [$alias, $field] = $this->addJoinsForNestedProperty(
                $property,
                $alias,
                $queryBuilder,
                $queryNameGenerator,
                $resourceClass,
                Join::INNER_JOIN
            );
        }

        $valueParameter = $queryNameGenerator->generateParameterName($field);

        /** @var class-string $resourceClass */
        $type = $this->managerRegistry
            ->getManagerForClass($resourceClass)
            ?->getClassMetadata($resourceClass)
            ->getTypeOfField($field);

        if (is_array($value)) {
            $queryBuilder
                ->andWhere(sprintf('%s.%s IN (:%s)', $alias, $field, $valueParameter))
                ->setParameter(
                    $valueParameter,
                    array_map(static fn ($uuid) => Uuid::fromString($uuid)->toBinary(), $value)
                );
        } else {
            $queryBuilder
                ->andWhere(sprintf('%s.%s = :%s', $alias, $field, $valueParameter))
                ->setParameter($valueParameter, $value, $type);
        }
    }
}
