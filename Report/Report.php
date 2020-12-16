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
     * @var int
     */
    private $visited;

    /**
     * @var int
     */
    private $failed;

    /**
     * @param int $visited Count of visited URIs
     * @param int $failed  Count of failed URIs
     */
    public function __construct(int $visited, int $failed)
    {
        $this->visited = $visited;
        $this->failed = $failed;
    }

    /**
     * @return int
     */
    public function getVisited(): int
    {
        return $this->visited;
    }

    /**
     * @return int
     */
    public function getFailed(): int
    {
        return $this->failed;
    }
}
