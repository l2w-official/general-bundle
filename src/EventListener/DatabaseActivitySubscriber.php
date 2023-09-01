<?php

namespace LearnToWin\GeneralBundle\EventListener;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use LearnToWin\GeneralBundle\Service\EntityEvent;

readonly class DatabaseActivitySubscriber
{
    public function __construct(private EntityEvent $entityEvent)
    {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->entityEvent->sendEntityEventMessage('persist', $args->getObject(), ['entity_event']);
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->entityEvent->sendEntityEventMessage('remove', $args->getObject(), ['entity_event']);
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->entityEvent->sendEntityEventMessage('update', $args->getObject(), ['entity_event']);
    }
}
