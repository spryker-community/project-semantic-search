<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider\Finder\Strategy;

use Iterator;
use Symfony\Component\Finder\Finder;

/**
 * Abstract base class for finder strategies
 */
abstract class AbstractFinderStrategy implements FinderStrategyInterface
{
    /**
     * @param array<string> $dirs
     *
     * @return \Iterator<string>
     */
    public function find(array $dirs): Iterator
    {
        $finder = $this->createFinder();
        $this->configureFinder($finder);

        $splFileInfos = $finder->in($dirs);

        foreach ($splFileInfos as $splFileInfo) {
            yield $splFileInfo->getRealPath();
        }
    }

    /**
     * Create a new Finder instance
     *
     * @return \Symfony\Component\Finder\Finder
     */
    protected function createFinder(): Finder
    {
        return new Finder();
    }

    /**
     * Configure the finder with specific search criteria
     *
     * @param \Symfony\Component\Finder\Finder $finder
     *
     * @return void
     */
    abstract protected function configureFinder(Finder $finder): void;
}
