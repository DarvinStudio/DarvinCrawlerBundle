DarvinCrawlerBundle
===================

This bundle provides console command that detects broken links on your website.

## Sample configuration

```yaml
# config/packages/dev/darvin_crawler.yaml
darvin_crawler:
    default_uri: https://example.com # Default value of command's "uri" argument
    blacklists:
        parse: # Content from URIs matching these regexes will not be parsed
            - '/\/filtered\//'
        visit: # URIs matching these regexes will not be visited
            - '/\/filtered\//'
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
