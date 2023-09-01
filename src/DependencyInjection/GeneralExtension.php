<?php

namespace LearnToWin\GeneralBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
        // Add the messenger configuration for the publishing part of entity events to rabbitmq
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['FrameworkBundle'])) {
            // create the configuration array
            $configs = [
                'messenger' => [
                    'transports' => [
                        'rabbit_entity_publish' => [
                            'dsn' => getenv('MESSENGER_TRANSPORT_DSN_RABBIT'),
                            'options' => [
                                'exchange' => [
                                    'name' => 'entity_event',
                                    'type' => 'topic',
                                ],
                                'queues' => []
                            ],
                        ],
                    ],
                    'routing' => [
                        'LearnToWin\GeneralBundle\Message\EntityMessage' => ['rabbit_entity_publish'],
                    ],
                ],
            ];

            $container->prependExtensionConfig('framework', $configs);
        }
    }
}
