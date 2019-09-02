<?php
declare(strict_types=1);

namespace Neusta\Pimcore\Maintenance\Versions;

use Symfony\Component\Filesystem\Exception\IOException as SymfonyIOException;
use Symfony\Component\Filesystem\Filesystem;

class VersionFileStorage
{
    /**
     * @var FinderFactory
     */
    private $finderFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $versionDir;

    public function __construct(
        FinderFactory $finderFactory,
        Filesystem $filesystem,
        string $versionDir = PIMCORE_VERSION_DIRECTORY
    ) {
        $this->finderFactory = $finderFactory;
        $this->filesystem = $filesystem;
        $this->versionDir = $versionDir;
    }

    /**
     * @return VersionFile[]
     */
    public function getVersionFiles(): iterable
    {
        // Iterating through 3 levels of version storage here (object/g0/1/1234):
        // - buckets (object/g0)
        // - object id (object/g0/1)
        // - version id (object/g0/1/1234)
        foreach ($this->iterateVersionDirs($this->versionDir . '/*') as $bucketDir) {
            foreach ($this->iterateVersionDirs($bucketDir->getPathname()) as $classIdDir) {
                foreach ($this->iterateFilesInDir($classIdDir->getPathname()) as $versionFile) {
                    yield new VersionFile($versionFile);
                }
            }
        }
    }

    /**
     * @throws IOException
     */
    public function deleteVersionFile(VersionFile $file): void
    {
        try {
            $this->filesystem->remove($file->getFullPath());
        } catch (SymfonyIOException $e) {
            throw IOException::fromSymfonyIOException($e);
        }
    }

    /**
     * @return \Iterator|\SplFileInfo[]
     */
    private function iterateVersionDirs(string $path): iterable
    {
        return $this->finderFactory
            ->createShallowDirFinder($path)
            ->getIterator();
    }

    /**
     * @return \Iterator|\SplFileInfo[]
     */
    private function iterateFilesInDir(string $versionDir): iterable
    {
        return $this->finderFactory
            ->createFileOnlyFinder($versionDir)
            ->getIterator();
    }
}
