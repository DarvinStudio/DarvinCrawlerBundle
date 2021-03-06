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

use Darvin\Utils\DependencyInjection\ConfigLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * Crawler extension
 */
class DarvinCrawlerExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('darvin_crawler.default_uri', $config['default_uri']);
        $container->setParameter('darvin_crawler.blacklists.parse', $config['blacklists']['parse']);
        $container->setParameter('darvin_crawler.blacklists.visit', $config['blacklists']['visit']);

        (new ConfigLoader($container, __DIR__.'/../Resources/config/services'))->load([
            'command',
            'crawler',
            'http',
        ]);
    }
}
