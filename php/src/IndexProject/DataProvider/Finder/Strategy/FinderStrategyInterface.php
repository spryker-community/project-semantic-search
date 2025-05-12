<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider\Finder\Strategy;

use Iterator;

/**
 * Interface for finding specific types of files
 */
interface FinderStrategyInterface
{
    /**
     * Find files based on specific criteria
     *
     * @param array<string> $paths Directories to search in
     *
     * @return \Iterator<string>
     */
    public function find(array $paths): Iterator;
}
