<?php

namespace LearnToWin\GeneralBundle\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\PropertyHelperTrait;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

/**
 * We might want to move this to the General Bundle, until api-platform fixes the SearchFilter.
 */
class UuidFilter extends AbstractFilter
{
    use PropertyHelperTrait;

    /**
     * @param array<mixed> $context
     * @param mixed $value
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
            !$this->isPropertyEnabled($property, $resourceClass)
            || !$this->isPropertyMapped($property, $resourceClass)
            || !$this->isUuidField($property, $resourceClass)
        ) {
            return;
        }

        $values = $this->normalizeValues($value, $property);
        if (null === $values) {
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

        if (1 === \count($values)) {
            $queryBuilder
                ->andWhere(sprintf('%s.%s = :%s', $alias, $field, $valueParameter))
                ->setParameter(
                    $valueParameter,
                    $values[0],
                    (string)$this->getDoctrineFieldType($property, $resourceClass)
                );
        } else {
            $queryBuilder
                ->andWhere(sprintf('%s.%s IN (:%s)', $alias, $field, $valueParameter))
                ->setParameter($valueParameter, $values);
        }
    }

    /**
     * @return array<mixed>
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];

        $properties = $this->getProperties();
        if (null === $properties) {
            $properties = array_fill_keys($this->getClassMetadata($resourceClass)->getFieldNames(), null);
        }

        foreach ($properties as $property => $strategy) {
            if (!$this->isPropertyMapped($property, $resourceClass)) {
                continue;
            }

            $propertyName = $this->normalizePropertyName($property);
            $filterParameterNames = [$propertyName, $propertyName . '[]'];
            foreach ($filterParameterNames as $filterParameterName) {
                $description[$filterParameterName] = [
                    'property' => $propertyName,
                    'type' => $this->getDoctrineFieldType($property, $resourceClass),
                    'required' => false,
                    'is_collection' => str_ends_with((string)$filterParameterName, '[]'),
                    'openapi' => [
                        'allowReserved' => false,
                        'allowEmptyValue' => true,
                        'explode' => false,
                    ],
                ];
            }
        }

        return $description;
    }

    private function isUuidField(string $property, string $resourceClass): bool
    {
        return UuidType::NAME === $this->getDoctrineFieldType($property, $resourceClass);
    }

    private function normalizeValues($value, string $property): ?array
    {
        if (!is_string($value) && !($value instanceof Uuid) && !\is_array($value)) {
            $this->getLogger()->notice('Invalid filter ignored', [
                'exception' => new InvalidArgumentException(sprintf('Invalid uuid value for "%s" property', $property)),
            ]);

            return null;
        }

        $values = (array)$value;
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $values[$key] = Uuid::fromString($value)->toBinary();
            } elseif ($value instanceof Uuid) {
                $values[$key] = $value->toBinary();
            } else {
                unset($values[$key]);
            }
        }

        if (empty($values)) {
            $error = 'At least one value is required, multiple values should be in "';
            $error .= $property . '[]=firstvalue&' . $property . '[]=secondvalue" format';
            $this->getLogger()->notice('Invalid filter ignored', ['exception' => new InvalidArgumentException($error)]);
            return null;
        }

        return array_values($values);
    }
}
