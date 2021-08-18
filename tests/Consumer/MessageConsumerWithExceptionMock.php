<?php
declare(strict_types=1);

namespace Tests\MKoprek\RabbitmqDlqBundle\Consumer;

use Exception;
use MKoprek\RabbitmqDlqBundle\Consumer\AbstractMessageConsumer;

class MessageConsumerWithExceptionMock extends AbstractMessageConsumer
{
    public function processMessage(string $messageName, array $payload): void
    {
        throw new Exception();
    }
}
