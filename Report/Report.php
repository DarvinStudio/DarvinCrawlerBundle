<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\CrawlerBundle\Report;

/**
 * Report
 */
class Report
{
    /**
     * @var string[]
     */
    private $visited;

    /**
     * @var string[]
     */
    private $broken;

    /**
     * @param string[] $visited Visited links
     * @param string[] $broken  Broken links
     */
    public function __construct(array $visited, array $broken)
    {
        $this->visited = $visited;
        $this->broken = $broken;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('%d/%d links are broken.', count($this->broken), count($this->visited));
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return empty($this->broken);
    }
}
