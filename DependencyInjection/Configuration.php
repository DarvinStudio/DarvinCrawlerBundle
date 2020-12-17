<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\CrawlerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('darvin_crawler');
        $builder->getRootNode()
            ->children()
                ->scalarNode('default_uri')->defaultNull()->end()
                ->arrayNode('blacklists')->addDefaultsIfNotSet()
                    ->children()
                        ->append($this->buildBlacklistNode('parse'))
                        ->append($this->buildBlacklistNode('visit'));

        return $builder;
    }

    /**
     * @param string $name Node name
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function buildBlacklistNode(string $name): ArrayNodeDefinition
    {
        $node = (new TreeBuilder($name))->getRootNode();
        $node
            ->prototype('scalar')->cannotBeEmpty()
                ->validate()
                    ->ifTrue(function ($regex): bool {
                        return false === @preg_match((string)$regex, '');
                    })->thenInvalid('%s is not valid regex.');

        return $node;
    }
}
