<?php

namespace Spryker\IndexProject\DataProvider\Finder\Strategy;

use Symfony\Component\Finder\Finder;

/**
 * Strategy for finding Plugin interface files
 */
class PluginInterfaceFinderStrategy extends AbstractFinderStrategy
{
    /**
     * @param \Symfony\Component\Finder\Finder $finder
     *
     * @return void
     */
    protected function configureFinder(Finder $finder): void
    {
        $finder->files()
            ->name('*PluginInterface.php')
            ->contains(['/Service/', '/Client/','/Glue/', '/Zed/', '/Yves/']);
    }
}
