<?php
/*
 * This file is part of the BeSimpleSoapBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
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
 */
class Configuration
{
    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\Config\Definition\ArrayNode The config tree
     */
    public function getConfigTree()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('be_simple_soap');

        $this->addServicesSection($rootNode);
        $this->addWsdlDumperSection($rootNode);

        return $treeBuilder->buildTree();
    }

    private function addServicesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('services')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('namespace')
                            ->isRequired()
                        ->end()
                        ->scalarNode('resource')
                            ->defaultValue('*')
                        ->end()
                        ->scalarNode('resource_type')
                            ->defaultValue('annotation')
                        ->end()
                        ->scalarNode('binding')
                            ->defaultValue('document-wrapped')
                            ->validate()
                                ->ifNotInArray(array('rpc-literal', 'document-wrapped'))
                                ->thenInvalid("Service binding style has to be either 'rpc-literal' or 'document-wrapped'")
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
                        ->scalarNode('stylesheet')->defaultNull()
                    ->end()
                ->end()
            ->end()
        ;
    }
}