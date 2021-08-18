<?php
declare(strict_types=1);

namespace MKoprek\RabbitmqDlqBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class AbstractMessageConsumer implements ConsumerInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * @param array<mixed> $payload
     */
    abstract public function processMessage(string $messageName, array $payload): void;

    public function execute(AMQPMessage $msg): int
    {
        try {
            $payload = json_decode($msg->getBody(), true) ?? [];
            $this->processMessage((string)$msg->getRoutingKey(), $payload);

            return self::MSG_ACK;
        } catch (Throwable $exception) {
            $this->logger->error(
                'Failed to process AMQP message',
                [
                    'message' => $msg->getRoutingKey(),
                    'exception' => $exception->getMessage(),
                    'body' => $msg->getBody(),
                ],
            );

            return self::MSG_REJECT;
        }
    }
}
