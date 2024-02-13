<?php



/*
 * This file is part of the SymfonyCasts ResetPasswordBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author xorg <xorg@i2p.i2p>
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('neox_doctrine_secure');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('neox_encryptor')->defaultValue("external")->end()
                ->scalarNode('neox_pws')->defaultValue("!passwordToChange!")->end()
                ->scalarNode('neox_dsn')->defaultValue("standalone://default")->end()
            
            ->end()
        ;

        return $treeBuilder;
    }
}