<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider\Finder\Strategy;

use Symfony\Component\Finder\Finder;

/**
 * Strategy for finding Client interface files
 */
class ClientInterfaceFinderStrategy extends AbstractFinderStrategy
{
    /**
     * @param \Symfony\Component\Finder\Finder $finder
     *
     * @return void
     */
    protected function configureFinder(Finder $finder): void
    {
        $finder->files()
            ->name('*ClientInterface.php')
            ->notContains('/Dependency/')
            ->contains('/Client/');
    }
}
