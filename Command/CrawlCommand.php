<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\CrawlerBundle\Command;

use Darvin\CrawlerBundle\Crawler\CrawlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Crawl command
 */
class CrawlCommand extends Command
{
    /**
     * @var \Darvin\CrawlerBundle\Crawler\CrawlerInterface
     */
    private $crawler;

    /**
     * @param \Darvin\CrawlerBundle\Crawler\CrawlerInterface $crawler Crawler
     */
    public function __construct(CrawlerInterface $crawler)
    {
        parent::__construct();

        $this->crawler = $crawler;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('darvin:crawler:crawl')
            ->setDefinition([
                new InputArgument('uri', InputArgument::REQUIRED),
            ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $report = $this->crawler->crawl($input->getArgument('uri'), function ($message, bool $error = false) use ($io): void {
            if ($error) {
                $io->error($message);

                return;
            }
            if ($io->isVerbose()) {
                $io->writeln($message);
            }
        });

        if ($report->hasVisited()) {
            $io->success(sprintf('Links visited: %d.', $report->getVisited()));
        }
        if ($report->hasFailed()) {
            $io->error(sprintf('Links failed: %s.', $report->getFailed()));
        }

        return 0;
    }
}
