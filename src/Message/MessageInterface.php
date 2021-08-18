<?php
declare(strict_types=1);

namespace MKoprek\RabbitmqDlqBundle\Message;

/**
 * @codeCoverageIgnore
 */
interface MessageInterface
{
    public const ROUTING_KEY = '';

    public function getRoutingKey(): string;

    /** @return array<mixed> */
    public function getPayload(): array;
}
