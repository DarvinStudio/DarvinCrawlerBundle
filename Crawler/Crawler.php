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

use Darvin\CrawlerBundle\Report\Report;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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
    public function crawl(string $uri, ?callable $output = null): Report
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

        $visited = [];
        $failed = [];

        $this->visit($uri, $output, $scheme, $host, $visited, $failed);

        return new Report(count($visited), count($failed));
    }

    /**
     * @param string   $uri           URI to visit
     * @param callable $output        Output callback
     * @param string   $websiteScheme Website scheme
     * @param string   $websiteHost   Website host
     * @param string[] $visited       Visited URIs
     * @param string[] $failed        Failed URIs
     */
    private function visit(string $uri, callable $output, string $websiteScheme, string $websiteHost, array &$visited, array &$failed): void
    {
        $visited[] = $uri;

        $response = $this->client->request('GET', $uri);

        $handleException = function (\Throwable $ex) use ($uri, $output, &$failed): void {
            $output(implode(': ', [$uri, $ex->getMessage()]), true);

            $failed[] = $uri;
        };

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $ex) {
            $handleException($ex);

            return;
        }
        if ($statusCode >= 300) {
            $failed[] = $uri;

            $output(implode(': ', [$statusCode, $uri]), true);

            return;
        }
        try {
            $headers = $response->getHeaders();
        } catch (ExceptionInterface $ex) {
            $handleException($ex);

            return;
        }

        $output(implode(': ', [$statusCode, $uri]));

        if (false === strpos($headers['content-type'][0] ?? '', 'html')) {
            return;
        }
        try {
            $content = $response->getContent();
        } catch (ExceptionInterface $ex) {
            $handleException($ex);

            return;
        }

        /** @var \DOMElement[] $nodes */
        $nodes = (new \Symfony\Component\DomCrawler\Crawler($content))->filter(implode(', ', array_map(function (string $attribute): string {
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
                    $this->visit($link, $output, $websiteScheme, $websiteHost, $visited, $failed);
                }
            }
        }
    }
}
