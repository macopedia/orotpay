<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('oro_tpay');

        $treeBuilder
            ->getRootNode()
                ->children()
                    ->scalarNode('tax_field')->end()
                    ?->arrayNode('channels')
                        ->children()
                            ->integerNode('expiresAfter')->defaultValue(300)->end()
                         ?->end()
                    ?->end()
                    ->arrayNode('gateway')
                        ->children()
                            ->booleanNode('logging')->defaultValue(true)->end()
                        ?->end()
                    ->end()
                    ?->arrayNode('channels')
                        ->children()
                            ->integerNode('expiresAfter')->defaultValue(300)->end()
                         ?->end()
                    ?->end()
                    ?->arrayNode('automatic_cancellation')
                        ->children()
                            ->integerNode('after')->defaultValue(7)->end()
                            ?->scalarNode('cron_definition')->defaultValue('5 3 * * *')->end()
                         ?->end()
                    ?->end()
                 ?->end()
        ;

        SettingsBuilder::append(
            $treeBuilder->getRootNode(),
            [
                'apple_pay_domain_verification' => [
                    'type' => 'text',
                    'value' => ''
                ],
            ]
        );

        return $treeBuilder;
    }
}
