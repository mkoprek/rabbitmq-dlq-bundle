<?php
declare(strict_types=1);

namespace MKoprek\RabbitmqDlqBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @codeCoverageIgnore
 */
class RabbitmqDlqExtension extends Extension implements PrependExtensionInterface
{
    private const DLQ_MESSAGE_TTL = 30_000;

    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    /**
     * @param array<mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $oldSoundConfig = $container->getExtensionConfig('old_sound_rabbit_mq');
        $multipleConsumers = $this->prepareMultipleConsumers($oldSoundConfig);

        $config = [
            'multiple_consumers' => $multipleConsumers,
        ];

        $container->prependExtensionConfig('old_sound_rabbit_mq', $config);
    }

    /**
     * @param array<mixed> $oldSoundConfig
     * @return array<mixed>
     */
    private function prepareMultipleConsumers(array $oldSoundConfig): array
    {
        if (!isset($oldSoundConfig[0]['multiple_consumers'])) {
            return [];
        }

        $multipleConsumers = $oldSoundConfig[0]['multiple_consumers'];

        if (is_array($multipleConsumers)) {
            foreach ($multipleConsumers as $multipleConsumerKey => $multipleConsumerItem) {
                $exchangeOptions = $multipleConsumerItem['exchange_options'];
                $exchangeName = $exchangeOptions['name'];

                $queues = [];
                $retryQueues = [];

                foreach ($multipleConsumerItem['queues'] as $queueKey => $queueVal) {
                    $queues[$queueVal['name']] = $this->addDlqRoutingToExistingQueues(
                        $queueVal,
                        $multipleConsumerItem['exchange_options']['name'],
                    );

                    $retryQueues[$this->getDlqQueueRetryName($queueKey)] = $this->buildDlqQueue(
                        $queueVal['name'],
                        $queueVal['callback'],
                        $exchangeName,
                    );
                }

                $return[$multipleConsumerKey] = [
                    'exchange_options' => $exchangeOptions,
                    'queues' => $queues,
                ];

                $return[sprintf('%s_dlq', $multipleConsumerKey)] = [
                    'exchange_options' => $exchangeOptions,
                    'queues' => $retryQueues,
                ];
            }
        }

        return $return ?? [];
    }

    /**
     * @param array<mixed> $queueVal
     * @return array<string>
     */
    private function addDlqRoutingToExistingQueues(array $queueVal, string $exchangeName): array
    {
        $dlq = [
            'arguments' => [
                'x-dead-letter-exchange' => ['S', $exchangeName],
                'x-dead-letter-routing-key' => [
                    'S',
                    $this->getDlqQueueRetryName($queueVal['name']),
                ],
            ],
        ];

        return array_merge($queueVal, $dlq);
    }

    /**
     * @return array<mixed>
     */
    private function buildDlqQueue(string $queueName, string $callback, string $exchangeName): array
    {
        return [
            'name' => $this->getDlqQueueRetryName($queueName),
            'callback' => $callback,
            'routing_keys' => [$this->getDlqQueueRetryName($queueName)],
            'arguments' => [
                'x-dead-letter-exchange' => ['S', $exchangeName],
                'x-dead-letter-routing-key' => ['S', $queueName],
                'x-message-ttl' => ['I', self::DLQ_MESSAGE_TTL],
            ],
        ];
    }

    private function getDlqQueueRetryName(string $name): string
    {
        $parts = explode('.', $name);
        $parts[count($parts)] = 'retry';

        return implode('.', $parts);
    }
}
