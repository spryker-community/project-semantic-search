<?php

namespace Spryker\IndexProject\DataProvider\Finder\Strategy;

use Symfony\Component\Finder\Finder;

/**
 * Strategy for finding Service interface files
 */
class ServiceInterfaceFinderStrategy extends AbstractFinderStrategy
{
    /**
     * @param \Symfony\Component\Finder\Finder $finder
     *
     * @return void
     */
    protected function configureFinder(Finder $finder): void
    {
        $finder->files()
            ->name('*ServiceInterface.php')
            ->notContains('/Dependency/')
            ->contains('/Service/');
    }
}
