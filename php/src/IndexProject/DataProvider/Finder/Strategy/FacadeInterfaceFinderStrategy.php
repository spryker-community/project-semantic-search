<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider\Finder\Strategy;

use Symfony\Component\Finder\Finder;

/**
 * Strategy for finding Facade interface files
 */
class FacadeInterfaceFinderStrategy extends AbstractFinderStrategy
{
    /**
     * @param \Symfony\Component\Finder\Finder $finder
     *
     * @return void
     */
    protected function configureFinder(Finder $finder): void
    {
        $finder->files()
            ->name('*FacadeInterface.php')
            ->contains('Business');
    }
}
