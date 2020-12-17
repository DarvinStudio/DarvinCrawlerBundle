DarvinCrawlerBundle
===================

This bundle provides console command that detects broken links on your website.

## Configuration

```yaml
# config/packages/dev/darvin_crawler.yaml
darvin_crawler:
    default_uri: https://example.com
```

## Usage

Crawl default URI:

```shell
$ bin/console darvin:crawler:crawl
```

Crawl specified URI:

```shell
$ bin/console darvin:crawler:crawl https://example.com
```

Display all visited links:

```shell
$ bin/console darvin:crawler:crawl https://example.com -v
```
