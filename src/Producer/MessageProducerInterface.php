<?php
declare(strict_types=1);

namespace MKoprek\RabbitmqDlqBundle\Producer;

use MKoprek\RabbitmqDlqBundle\Message\MessageInterface;

/**
 * @codeCoverageIgnore
 */
interface MessageProducerInterface
{
    public function produce(MessageInterface $message): void;
}
