<?php
declare(strict_types=1);

namespace MKoprek\RabbitmqDlqBundle\Message;

/**
 * @codeCoverageIgnore
 */
class AbstractMessage implements MessageInterface
{
    /** @var array<mixed> */
    protected array $payload = [];

    /** @return array<mixed> */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getRoutingKey(): string
    {
        return static::ROUTING_KEY;
    }
}
