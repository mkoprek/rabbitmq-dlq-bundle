<?php
declare(strict_types=1);

namespace MKoprek\RabbitmqDlqBundle\Producer;

use MKoprek\RabbitmqDlqBundle\Message\MessageInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

/**
 * @codeCoverageIgnore
 */
class MessageProducer implements MessageProducerInterface
{
    public function __construct(private ProducerInterface $producer)
    {
    }

    public function produce(MessageInterface $message): void
    {
        $this->producer->publish(
            (string) json_encode($message->getPayload()),
            $message->getRoutingKey(),
        );
    }
}
