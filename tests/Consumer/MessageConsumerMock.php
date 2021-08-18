<?php
declare(strict_types=1);

namespace Tests\MKoprek\RabbitmqDlqBundle\Consumer;

use MKoprek\RabbitmqDlqBundle\Consumer\AbstractMessageConsumer;

class MessageConsumerMock extends AbstractMessageConsumer
{
    public function processMessage(string $messageName, array $payload): void
    {
    }
}
