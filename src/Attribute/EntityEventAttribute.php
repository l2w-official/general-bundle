<?php

namespace LearnToWin\GeneralBundle\Attribute;

use Attribute;

/**
 * This attribute defines which fields should be included in the message for a given action on an entity.
 * If this attribute is not added to an Entity class then no messages will be sent for that entity.
 *
 * Actions: 'persist', 'update', 'remove'
 * Example:
 *
 * #[EntityEvent(['persist' => ['name'], 'update' => ['name'], 'remove' => ['id', 'name']])]
 * class MyEntity
 * {
 *    #[ORM\Id]
 *    #[ORM\GeneratedValue]
 *    #[ORM\Column(type: 'integer')]
 *     private int $id;
 *
 *     #[ORM\Column(type: 'string')]
 *     private string $name;
 *
 *     public function getId(): int
 *     {
 *        return $this->id;
 *     }
 *
 *     public function setId(int $id): void
 *     {
 *        $this->id = $id;
 *     }
 *
 *     public function getName(): string
 *     {
 *       return $this->name;
 *     }
 *
 *     public function setName(string $name): void
 *     {
 *       $this->name = $name;
 *     }
 * }
 *
 * Other examples:
 *
 * To include all fields in the message:
 * #[EntityEvent(['persist', 'update', 'remove'])]
 *
 * To include no fields in the message:
 * #[EntityEvent(['persist' => [], 'update' => [], 'remove' => []])]
 *
 * To include all fields in the message for persist and update, but no fields for remove:
 * #[EntityEvent(['persist', 'update', 'remove' => []])]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class EntityEventAttribute
{
    public const ACTION_PERSIST = 'persist';
    public const ACTION_UPDATE = 'update';
    public const ACTION_REMOVE = 'remove';

    private ?array $actions;

    public function __construct(array $actions = null)
    {
        $this->actions = $actions;
    }

    public function getFieldsForAction(string $action): array
    {
        return $this->actions[$action] ?? [];
    }

    public function getActions(): array
    {
        return array_keys($this->actions);
    }
}
