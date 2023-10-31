<?php

namespace LearnToWin\GeneralBundle\Service;

use LearnToWin\GeneralBundle\Attribute\EntityEventAttribute;
use LearnToWin\GeneralBundle\Message\EntityMessage;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

readonly class EntityEvent
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private SerializerInterface $serializer
    ) {
    }

    /**
     * @param string $action
     * @param mixed $entity
     * @param array<string>|string|null $groups
     * @return void
     */
    public function sendEntityEventMessage(string $action, mixed $entity): void
    {
        // Wrap this in a try/catch block to prevent Doctrine from throwing exceptions if we can't send the message
        // Log the exception caught as a critical error, so we can investigate
        try {
            $reflectionClass = new ReflectionClass($entity);

            // Get all the fields that should be included in the message
            $fields = $this->getFieldsFromAttributes($reflectionClass, $action);
            if (false === $fields) {
                // Since there are no fields, we don't need to send a message
                return;
            }

            // Set up the routing key for this message
            $resource = $reflectionClass->getShortName();
            $routingKey = strtolower($resource) . '.' . $action;

            // Serialize the entity to JSON
            $data = $this->serializeData($fields, $entity);

            // Create the message envelope
            $messageId = Uuid::v4()->toRfc4122();
            $envelope = $this->createTheMessageEnvelope($resource, $action, $data, $routingKey, $messageId);

            // Send the message
            $this->logger->debug('Sending message: (' . $messageId . ') [' . $routingKey . '] - ' . $data);
            $this->messageBus->dispatch($envelope);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), [
                'code' => $e->getCode(),
                'message' => 'Failed to send Entity Event: ',
                'messageId' => $messageId,
                'action' => $action,
                'entityClass' => get_class($entity),
            ]);
        }
    }

    private function getFieldsFromAttributes(ReflectionClass $reflectionClass, string $action): array|false
    {
        $entityEventAttributes = $reflectionClass->getAttributes(EntityEventAttribute::class);
        if (empty($entityEventAttributes)) {
            return false;
        }

        $fields = [];
        foreach ($entityEventAttributes as $entityEventAttribute) {
            /** @var EntityEventAttribute $attribute */
            $attribute = $entityEventAttribute->newInstance();
            if (in_array($action, $attribute->getActions())) {
                $fields = array_merge($fields, $attribute->getFieldsForAction($action));
            }
        }

        return $fields;
    }

    /**
     * @param array $fields
     * @param mixed $entity
     * @return string
     */
    private function serializeData(array $fields, mixed $entity): string
    {
        $contextBuilder = new ObjectNormalizerContextBuilder();
        $context = $contextBuilder->withAttributes($fields)->toArray();
        return $this->serializer->serialize($entity, 'json', $context);
    }

    /**
     * @param string $resource
     * @param string $action
     * @param string $data
     * @param string $routingKey
     * @param string $messageId
     * @return Envelope
     */
    private function createTheMessageEnvelope(
        string $resource,
        string $action,
        string $data,
        string $routingKey,
        string $messageId
    ): Envelope {
        $message = new EntityMessage($resource, $action, $data);
        return new Envelope($message, [
            // Added to route this message to particular topics
            new AmqpStamp($routingKey),
            // Added to ensure we don't send this to multiple transports
            new TransportNamesStamp(['rabbit_entity_publish']),
            // Give each message a unique ID
            new TransportMessageIdStamp($messageId)
        ]);
    }
}
