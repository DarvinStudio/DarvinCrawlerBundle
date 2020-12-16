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

use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Crawler
 */
class Crawler implements CrawlerInterface
{
    private const OPTIONS = [
        'timeout' => 5,
    ];

    /**
     * @var \Goutte\Client
     */
    private $client;

    /**
     * Crawler constructor.
     */
    public function __construct()
    {
        $this->client = new Client(HttpClient::create(self::OPTIONS));
    }

    /**
     * {@inheritDoc}
     */
    public function crawl(string $uri, ?callable $output = null): void
    {
        if (null === $output) {
            $output = function ($message, bool $error = false): void {
            };
        }
    }
}
