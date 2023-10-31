<?php

namespace LearnToWin\GeneralBundle\EventListener;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use LearnToWin\GeneralBundle\Attribute\EntityEventAttribute;
use LearnToWin\GeneralBundle\Service\EntityEvent;

readonly class EntityEventDatabaseSubscriber
{
    public function __construct(private EntityEvent $entityEvent)
    {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->entityEvent->sendEntityEventMessage(EntityEventAttribute::ACTION_PERSIST, $args->getObject());
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->entityEvent->sendEntityEventMessage(EntityEventAttribute::ACTION_REMOVE, $args->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->entityEvent->sendEntityEventMessage(EntityEventAttribute::ACTION_UPDATE, $args->getObject());
    }
}
