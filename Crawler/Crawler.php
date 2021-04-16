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
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Crawler
 */
class Crawler implements CrawlerInterface
{
    private const ATTRIBUTES = [
        'href',
        'src',
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
     * @var string[]
     */
    private $parseBlacklist;

    /**
     * @var string[]
     */
    private $visitBlacklist;

    /**
     * @param \Symfony\Contracts\HttpClient\HttpClientInterface $client         HTTP client
     * @param string[]                                          $parseBlacklist Parse blacklist
     * @param string[]                                          $visitBlacklist Visit blacklist
     */
    public function __construct(HttpClientInterface $client, array $parseBlacklist, array $visitBlacklist)
    {
        $this->client = $client;
        $this->parseBlacklist = $parseBlacklist;
        $this->visitBlacklist = $visitBlacklist;
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
        $broken  = [];

        $this->visit($uri, $output, $scheme, $host, $visited, $broken);

        return new Report($visited, $broken);
    }

    /**
     * @param string   $uri           URI
     * @param callable $output        Output callback
     * @param string   $websiteScheme Website scheme
     * @param string   $websiteHost   Website host
     * @param string[] $visited       Visited links
     * @param string[] $broken        Broken links
     */
    private function visit(string $uri, callable $output, string $websiteScheme, string $websiteHost, array &$visited, array &$broken): void
    {
        $visited[] = $uri;

        $response = $this->client->request('GET', $uri);

        $handleError = function (?\Throwable $ex, string $message = '') use ($uri, $output, &$broken): void {
            if ('' === $message) {
                $message = implode(': ', [$uri, $ex->getMessage()]);
            }

            $output($message, true);

            $broken[] = $uri;
        };

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $ex) {
            $handleError($ex);

            return;
        }
        if ($statusCode >= 300) {
            $handleError(null, implode(': ', [$statusCode, $uri]));

            return;
        }
        try {
            $headers = $response->getHeaders();
        } catch (ExceptionInterface $ex) {
            $handleError($ex);

            return;
        }

        $output(implode(': ', [$statusCode, $uri]));

        if (isset($headers['content-type'][0]) && false === strpos($headers['content-type'][0], 'html')) {
            return;
        }
        if ($this->isBlacklisted($uri, $this->parseBlacklist)) {
            return;
        }
        try {
            $html = $response->getContent();
        } catch (ExceptionInterface $ex) {
            $handleError($ex);

            return;
        }

        $this->parse($html, $output, $websiteScheme, $websiteHost, $visited, $broken);
    }

    /**
     * @param string   $html          HTML
     * @param callable $output        Output callback
     * @param string   $websiteScheme Website scheme
     * @param string   $websiteHost   Website host
     * @param string[] $visited       Visited links
     * @param string[] $broken        Broken links
     */
    private function parse(string $html, callable $output, string $websiteScheme, string $websiteHost, array &$visited, array &$broken): void
    {
        /** @var \DOMElement[] $nodes */
        $nodes = (new \Symfony\Component\DomCrawler\Crawler($html))->filter(implode(', ', array_map(function (string $attribute): string {
            return sprintf('[%s]', $attribute);
        }, self::ATTRIBUTES)));

        foreach ($nodes as $node) {
            foreach (self::ATTRIBUTES as $attr) {
                if (!$node->hasAttribute($attr)) {
                    continue;
                }

                $link = preg_replace('/#.*$/', '', $node->getAttribute($attr));

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

                if ($host === $websiteHost
                    && !in_array($link, $visited)
                    && !in_array(rtrim($link, '/'), $visited)
                    && !$this->isBlacklisted($link, $this->visitBlacklist)
                ) {
                    $this->visit($link, $output, $websiteScheme, $websiteHost, $visited, $broken);
                }
            }
        }
    }

    /**
     * @param string   $uri       URI
     * @param string[] $blacklist Blacklist
     *
     * @return bool
     */
    private function isBlacklisted(string $uri, array $blacklist): bool
    {
        foreach ($blacklist as $regex) {
            if (preg_match($regex, $uri)) {
                return true;
            }
        }

        return false;
    }
}
