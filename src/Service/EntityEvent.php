<?php

namespace LearnToWin\GeneralBundle\Service;

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
    public function sendEntityEventMessage(string $action, mixed $entity, array|null|string $groups = null): void
    {
        // Wrap this in a try/catch block to prevent Doctrine from throwing exceptions if we can't send the message
        // Log the exception caught as a critical error, so we can investigate
        $messageId = Uuid::v4()->toRfc4122();
        try {
            // Set up the routing key for this message
            $resource = (new ReflectionClass($entity))->getShortName();
            $routingKey = strtolower($resource) . '.' . $action;

            // Serialize the entity to JSON
            $context = (new ObjectNormalizerContextBuilder())->withGroups($groups)->toArray();
            $data = $this->serializer->serialize($entity, 'json', $context);

            // Create the message envelope
            $message = new EntityMessage($resource, $action, $data);
            $envelope = new Envelope($message, [
                // Added to route this message to particular topics
                new AmqpStamp($routingKey),
                // Added to ensure we don't send this to multiple transports
                new TransportNamesStamp(['rabbit_entity_publish']),
                // Give each message a unique ID
                new TransportMessageIdStamp($messageId)
            ]);

            // Send the message
            $this->logger->info('Sending message: (' . $messageId . ') [' . $routingKey . '] - ' . $data);
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
}
