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
     * @var string|null
     */
    private $defaultUri;

    /**
     * @param \Darvin\CrawlerBundle\Crawler\CrawlerInterface $crawler    Crawler
     * @param string|null                                    $defaultUri Default URI
     */
    public function __construct(CrawlerInterface $crawler, ?string $defaultUri)
    {
        $this->crawler = $crawler;
        $this->defaultUri = $defaultUri;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('darvin:crawler:crawl')
            ->setDefinition([
                new InputArgument('uri', null !== $this->defaultUri ? InputArgument::OPTIONAL : InputArgument::REQUIRED, '', $this->defaultUri),
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

        if ($report->isSuccessful()) {
            $io->success($report);
        } else {
            $io->error($report);
        }

        return 0;
    }
}
