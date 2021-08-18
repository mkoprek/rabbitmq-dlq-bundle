# RabbitMQ DLQ Bundle

[![Build Status](https://app.travis-ci.com/mkoprek/rabbitmq-dlq-bundle.svg?branch=main)](https://app.travis-ci.com/mkoprek/rabbitmq-dlq-bundle)

## Configuration
This bundle is extension to [RabbitMqBundle](https://github.com/php-amqplib/RabbitMqBundle), it will automatically add DLQ queues to existing `multiple_consumer` in `old_sound_rabbit_mq.yaml`

```yaml
    multiple_consumers:
        default:
            connection: default
            exchange_options:
                name: 'exchange'
                type: 'topic'
            graceful_max_execution:
                timeout: 60
            queues:
                legacy.investments.investment_added.event:
                    name: 'legacy.investments.investment_added.event'
                    routing_key: 'legacy.investments.investment_added.event'
                    callback: Namespace\InvestmentAddedLegacyConsumer
                legacy.investments.investment_edited.event:
                    name: 'legacy.investments.investment_edited.event'
                    routing_key: 'legacy.investments.investment_edited.event'
                    callback: Namespace\InvestmentEditedLegacyConsumer
```

After that configuration you will have 2 additional DLQ queues with routing keys:
* legacy.investments.investment_added.retry
* legacy.investments.investment_edited.retry

Each *.retry queue will re-route all messages back to original queue after `30s` delay.

To put message to `*.retry` queue you just need to throw any **Exception** when parsing message.

## Consuming

You are creating consumer like in example above - by adding callback. This callback MUST extends `AbstractMessageConsumer`.

That is it! If everything is OK, just leave it.<br/>
If there was any problem, then throw Exception.

## Producing

Just inject `MessageProducerInterface` to your service where you need to produce message.
Then create class with extends `AbstractMessage` or implements `MessageInterface`.

### Message
```php
<?php
declare(strict_types=1);

use MKoprek\RabbitmqDlqBundle\Message\AbstractMessage;

class Message extends AbstractMessage
{
    public const ROUTING_KEY = 'legacy.investments.investment_added.event';

    public function __construct(array $array)
    {
        $this->payload = [
            'id' => '7186971d-1b63-46ba-9804-012e8477d370',
            'name' => 'Lorem Ipsum',
            'array' => $array,
        ];
    }
}
```
### Producer
```php
<?php
declare(strict_types=1);

use MKoprek\RabbitmqDlqBundle\Producer\MessageProducerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProduceMessage
{
    public function __construct(private MessageProducerInterface $producer)
    {
    }

    protected function produce(InputInterface $input, OutputInterface $output): void
    {
        $this->producer->produce(
            new Message(['some_key' => 'some_val'])
        );
    }
}

```
