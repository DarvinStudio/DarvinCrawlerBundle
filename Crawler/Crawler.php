<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\CrawlerBundle\Crawler;

/**
 * Crawler
 */
class Crawler implements CrawlerInterface
{
    /**
     * {@inheritDoc}
     */
    public function crawl(string $uri, ?callable $output = null): void
    {
        // TODO: Implement crawl() method.
    }
}
