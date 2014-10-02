<?php

namespace AdamQuaile\Behat\CommandRunnerExtension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CommandRunnerExtension implements Extension
{
    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'command_runner';
    }

    /**
     * Initializes other extensions.
     *
     * This method is called immediately after all extensions are activated but
     * before any extension `configure()` method is called. This allows extensions
     * to hook into the configuration of other extensions providing such an
     * extension point.
     *
     * @param ExtensionManager $extensionManager
     */
    public function initialize(ExtensionManager $extensionManager)
    {

    }

    /**
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $buildCommandListConfig = function(NodeBuilder $nodeBuilder, $hookName) {
            $nodeBuilder
                ->arrayNode($hookName)
                    ->prototype('array')
                        ->beforeNormalization()
                        ->ifString()
                        ->then(function($v) {
                            return [
                                'command' => $v
                            ];
                        })
                        ->end()
                        ->children()
                            ->scalarNode('command')->isRequired()->end()
                            ->booleanNode('background')
                                ->defaultValue(false)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ;
        };

        $buildCommandListConfig($builder->children(), 'beforeSuite');
        $buildCommandListConfig($builder->children(), 'afterSuite');
        $buildCommandListConfig($builder->children(), 'beforeFeature');
        $buildCommandListConfig($builder->children(), 'afterFeature');
        $buildCommandListConfig($builder->children(), 'beforeScenario');
        $buildCommandListConfig($builder->children(), 'afterScenario');

    }

    /**
     * Loads extension services into temporary container.
     *
     * @param ContainerBuilder $container
     * @param array $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $definition = (new Definition('AdamQuaile\Behat\CommandRunnerExtension\CommandRunnerSubscriber'))
            ->addTag('event_dispatcher.subscriber')
            ->setArguments([
                $config['beforeSuite'],
                $config['afterSuite'],
                $config['beforeFeature'],
                $config['afterFeature'],
                $config['beforeScenario'],
                $config['afterScenario'],

            ])
        ;

        $container->setDefinition('command_runner.listener', $definition);

    }

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {

    }


}