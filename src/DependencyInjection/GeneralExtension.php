<?php

namespace LearnToWin\GeneralBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class GeneralExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['DoctrineBundle'])) {
            $container->prependExtensionConfig('doctrine', [
                'dbal' => [
                    'types' => [
                        'datetime_immutable' => LearnToWin\GeneralBundle\Doctrine\Types\DateTimeImmutableType::class,
                    ],
                ],
            ]);
        }
    }
}