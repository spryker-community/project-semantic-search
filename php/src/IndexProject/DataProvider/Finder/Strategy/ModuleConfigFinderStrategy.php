<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider\Finder\Strategy;

use Symfony\Component\Finder\Finder;

/**
 * Strategy for finding Module config files
 */
class ModuleConfigFinderStrategy extends AbstractFinderStrategy
{
    /**
     * @param \Symfony\Component\Finder\Finder $finder
     *
     * @return void
     */
    protected function configureFinder(Finder $finder): void
    {
        $finder->files()
            ->name('*Config.php')
            ->contains(['/Service/', '/Client/','/Glue/', '/Zed/', '/Yves/']);
    }
}
