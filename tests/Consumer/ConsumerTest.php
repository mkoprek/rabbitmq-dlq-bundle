<?php
declare(strict_types=1);

namespace Tests\MKoprek\RabbitmqDlqBundle\Consumer;

use MKoprek\RabbitmqDlqBundle\Consumer\AbstractMessageConsumer;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ConsumerTest extends TestCase
{
    /**
     * @test
     */
    public function itCanConsume()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $consumer = new MessageConsumerMock($logger);

        $msg = new AMQPMessage('{"json": true}');
        $msg->setDeliveryInfo('tag', true, 'string', 'routingKey');

        $this->assertEquals(
            ConsumerInterface::MSG_ACK,
            $consumer->execute($msg)
        );
    }

    /**
     * @test
     */
    public function itCantConsume()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $consumer = new MessageConsumerWithExceptionMock($logger);

        $msg = new AMQPMessage('{"json": true}');
        $msg->setDeliveryInfo('tag', true, 'string', 'routingKey');

        $this->assertEquals(
            ConsumerInterface::MSG_REJECT,
            $consumer->execute($msg)
        );
    }
}
