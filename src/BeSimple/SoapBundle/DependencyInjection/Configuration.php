<?php

/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * WebServiceExtension configuration structure.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 * @author Francis Besset <francis.besset@gmail.com>
 */
class Configuration
{
    private $cacheTypes = array('none', 'disk', 'memory', 'disk_memory');
    private $proxyAuth = array('basic', 'ntlm');

    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\Config\Definition\ArrayNode The config tree
     */
    public function getConfigTree()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('be_simple_soap');

        $this->addCacheSection($rootNode);
        $this->addClientSection($rootNode);
        $this->addServicesSection($rootNode);
        $this->addWsdlDumperSection($rootNode);

        $rootNode
            ->children()
                ->scalarNode('exception_controller')->defaultValue('BeSimpleSoapBundle:SoapWebService:exception')->end()
            ->end()
        ;

        return $treeBuilder->buildTree();
    }

    private function addCacheSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('type')
                                ->defaultValue('disk')
                                ->validate()
                                    ->ifNotInArray($this->cacheTypes)
                                    ->thenInvalid(sprintf('The cache type has to be either %s', implode(', ', $this->cacheTypes)))
                                ->end()
                            ->end()
                            ->scalarNode('lifetime')->defaultNull()->end()
                            ->scalarNode('limit')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addClientSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('clients')
                    ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('wsdl')->isRequired()->end()
                                ->scalarNode('user_agent')->end()
                                ->scalarNode('cache_type')
                                    ->validate()
                                        ->ifNotInArray($this->cacheTypes)
                                        ->thenInvalid(sprintf('The cache type has to be either: %s', implode(', ', $this->cacheTypes)))
                                    ->end()
                                ->end()
                                ->arrayNode('classmap')
                                    ->useAttributeAsKey('name')->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('proxy')
                                    ->info('proxy configuration')
                                    ->addDefaultsIfNotSet()
                                    ->beforeNormalization()
                                        ->ifTrue(function ($v) { return !is_array($v); })
                                        ->then(function ($v) { return array('host' => null === $v ? false : $v); })
                                    ->end()
                                    ->children()
                                        ->scalarNode('host')->defaultFalse()->end()
                                        ->scalarNode('port')->defaultValue(3128)->end()
                                        ->scalarNode('login')->defaultNull()->end()
                                        ->scalarNode('password')->defaultNull()->end()
                                        ->scalarNode('auth')
                                            ->defaultNull()
                                            ->validate()
                                                ->ifNotInArray($this->proxyAuth)
                                                ->thenInvalid(sprintf('The proxy auth has to be either: %s', implode(', ', $this->proxyAuth)))
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addServicesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('services')
                    ->useAttributeAsKey('name')
                        ->prototype('array')
                        ->children()
                            ->scalarNode('namespace')->isRequired()->end()
                            ->scalarNode('resource')->defaultValue('*')->end()
                            ->scalarNode('resource_type')->defaultValue('annotation')->end()
                            ->scalarNode('binding')
                                ->defaultValue('document-wrapped')
                                ->validate()
                                    ->ifNotInArray(array('rpc-literal', 'document-wrapped'))
                                    ->thenInvalid("Service binding style has to be either 'rpc-literal' or 'document-wrapped'")
                                ->end()
                            ->end()
                            ->scalarNode('cache_type')
                                ->validate()
                                    ->ifNotInArray($this->cacheTypes)
                                    ->thenInvalid(sprintf('The cache type has to be either %s', implode(', ', $this->cacheTypes)))
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addWsdlDumperSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('wsdl_dumper')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('stylesheet')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
