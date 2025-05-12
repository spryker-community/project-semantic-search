<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider\Finder;

use Iterator;

/**
 * Interface for project path providers
 */
interface ProjectPathProviderInterface
{
    /**
     * Get project paths for searching
     *
     * @return \Iterator<string>
     */
    public function getProjectPaths(): Iterator;
}
