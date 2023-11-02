<?php

namespace LearnToWin\GeneralBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use UnitEnum;

class GeneralExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        $this->addMessengerConfig($bundles, $container);
        $this->addDoctrineTypeConfig($bundles, $container);
    }

    /**
     * Adds the messenger configuration to the framework config for the rabbit entity publish transport
     * @param array<string,mixed>|bool|float|int|string|UnitEnum|null $bundles
     * @param ContainerBuilder $container
     * @return void
     */
    private function addMessengerConfig(
        array|bool|float|int|null|string|UnitEnum $bundles,
        ContainerBuilder $container
    ): void {
        if (isset($bundles['FrameworkBundle'])) {
            $transport = 'in-memory://';
            if (getenv('APP_ENV') !== 'test') {
                $transport = [
                    'dsn' => getenv('MESSENGER_TRANSPORT_DSN_RABBIT'),
                    'options' => [
                        'exchange' => [
                            'name' => 'entity_event',
                            'type' => 'topic',
                        ],
                        'queues' => []
                    ],
                ];
            }

            $configs = [
                'messenger' => [
                    'transports' => [
                        'rabbit_entity_publish' => $transport,
                    ],
                    'routing' => [
                        'LearnToWin\GeneralBundle\Message\EntityMessage' => ['rabbit_entity_publish'],
                    ],
                ],
            ];

            $container->prependExtensionConfig('framework', $configs);
        }
    }

    /**
     * Adds the doctrine type configuration to the doctrine config for the microsecond datetime type
     * @param UnitEnum|float|int|bool|array<string>|string|null $bundles
     * @param ContainerBuilder $container
     * @return void
     */
    private function addDoctrineTypeConfig(
        UnitEnum|float|int|bool|array|string|null $bundles,
        ContainerBuilder $container
    ): void {
        if (isset($bundles['DoctrineBundle'])) {
            // create the configuration array
            $configs = [
                'dbal' => [
                    'types' => [
                        'datetime_immutable' => '\LearnToWin\GeneralBundle\Doctrine\Types\DateTimeMicrosecondsType',
                    ],
                ],
            ];

            $container->prependExtensionConfig('doctrine', $configs);
        }
    }
}
