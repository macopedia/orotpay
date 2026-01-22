<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\DependencyInjection;

use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OroTpayExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->prependExtensionConfig(
            $this->getAlias(),
            $config
        );

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('parameters.yml');
        $loader->load('controllers.yml');
        $loader->load('methods.yml');
        $loader->load('tpay.yml');
        $loader->load('forms.yml');
        $loader->load('listeners.yml');

        $container->registerAliasForArgument('monolog.logger.tpay', LoggerInterface::class);
    }

    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('monolog', [
            'channels' => ['tpay'],
            'handlers' => [
                'tpay' => [
                    'type' => 'rotating_file',
                    'path' => '%kernel.logs_dir%/tpay.log',
                    'level' => 'debug',
                    'channels' => ['tpay'],
                ],
            ],
        ]);
    }
}
