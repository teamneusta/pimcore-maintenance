<?php
declare(strict_types=1);

namespace Neusta\Pimcore\Maintenance\Versions;

use Symfony\Component\Finder\Finder;

class FinderFactory
{
    public function createFinder(): Finder
    {
        return new Finder();
    }

    public function createShallowDirFinder(string $dir, int $level = 0): Finder
    {
        return $this->createFinder()->directories()->depth($level)->in($dir);
    }

    public function createFileOnlyFinder(string $dir): Finder
    {
        return $this->createFinder()->files()->in($dir);
    }
}
