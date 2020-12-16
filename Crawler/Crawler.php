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

use Symfony\Component\HttpClient\HttpClient;

/**
 * Crawler
 */
class Crawler implements CrawlerInterface
{
    private const ATTRIBUTES = [
        'href',
        'src',
    ];

    private const OPTIONS = [
        'timeout' => 5,
    ];

    private const SCHEMES = [
        'http',
        'https',
    ];

    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private $client;

    /**
     * Crawler constructor.
     */
    public function __construct()
    {
        $this->client = HttpClient::create(self::OPTIONS);
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

        $scheme = parse_url($uri, PHP_URL_SCHEME);

        if (null === $scheme) {
            $scheme = 'http';

            $uri = implode('://', [$scheme, $uri]);
        }
        if (!in_array($scheme, self::SCHEMES)) {
            throw new \InvalidArgumentException(sprintf('Scheme "%s" is not supported.', $scheme));
        }

        $host = parse_url($uri, PHP_URL_HOST);

        if (null === $host) {
            throw new \InvalidArgumentException(sprintf('Unable to parse URI "%s".', $uri));
        }

        $this->crawlUri($uri, $output, $scheme, $host);
    }

    /**
     * @param string   $uri           URI
     * @param callable $output        Output callback
     * @param string   $websiteScheme Website scheme
     * @param string   $websiteHost   Website host
     * @param string[] $visited       Visited URIs
     */
    private function crawlUri(string $uri, callable $output, string $websiteScheme, string $websiteHost, array &$visited = []): void
    {
        $response = $this->client->request('GET', $uri);

        $output($uri);

        $visited[] = $uri;

        if (false === strpos($response->getHeaders()['content-type'][0] ?? '', 'html')) {
            return;
        }

        /** @var \DOMElement[] $nodes */
        $nodes = (new \Symfony\Component\DomCrawler\Crawler($response->getContent()))->filter(implode(', ', array_map(function (string $attribute): string {
            return sprintf('[%s]', $attribute);
        }, self::ATTRIBUTES)));

        foreach ($nodes as $node) {
            foreach (self::ATTRIBUTES as $attr) {
                if (!$node->hasAttribute($attr)) {
                    continue;
                }

                $link = $node->getAttribute($attr);

                if ('' === $link) {
                    continue;
                }

                $scheme = parse_url($link, PHP_URL_SCHEME);

                if (null !== $scheme && !in_array($scheme, self::SCHEMES)) {
                    continue;
                }
                if (null === $scheme) {
                    $scheme = $websiteScheme;

                    $link = sprintf('%s://%s%s', $scheme, $websiteHost, $link);
                }

                $host = parse_url($link, PHP_URL_HOST);

                if ($host === $websiteHost && !in_array($link, $visited) && !in_array(rtrim($link, '/'), $visited)) {
                    $this->crawlUri($link, $output, $websiteScheme, $websiteHost, $visited);
                }
            }
        }
    }
}
