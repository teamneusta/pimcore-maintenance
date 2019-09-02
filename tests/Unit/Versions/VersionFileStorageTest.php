<?php
declare(strict_types=1);

namespace Tests\Unit\Neusta\Pimcore\Maintenance\Versions;

use Neusta\Pimcore\Maintenance\Versions\FinderFactory;
use Neusta\Pimcore\Maintenance\Versions\VersionFile;
use Neusta\Pimcore\Maintenance\Versions\VersionFileStorage;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class VersionFileStorageTest extends TestCase
{
    /**
     * @var ObjectProphecy|\SplFileInfo
     */
    private $versionFile;

    /**
     * @var ObjectProphecy|Finder
     */
    private $finder;

    /**
     * @var ObjectProphecy|Filesystem
     */
    private $filesystem;

    /**
     * @var VersionFileStorage
     */
    private $versionStorage;

    /**
     * @var ObjectProphecy|FinderFactory
     */
    private $finderFactory;

    /**
     * @var ObjectProphecy|Finder
     */
    private $dirFinder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = $this->prophesize(Filesystem::class);

        $this->versionFile = $this->createFileMock('var/versions/object/g0/1/1');
        $this->finder = $this->prophesize(Finder::class);
        $this->finder->getIterator()
            ->willReturn([$this->versionFile->reveal()]);

        $this->dirFinder = $this->prophesize(Finder::class);
        $this->dirFinder->getIterator()
            ->willReturn([$this->createFileMock('var/versions/object/g0')]);

        $this->finderFactory = $this->prophesize(FinderFactory::class);
        $this->finderFactory->createFileOnlyFinder(Argument::any())
            ->willReturn($this->finder->reveal());

        $this->finderFactory->createShallowDirFinder(
            Argument::containingString('var/versions/'),
            Argument::cetera()
        )
            ->willReturn($this->dirFinder->reveal());

        $this->versionStorage = new VersionFileStorage(
            $this->finderFactory->reveal(),
            $this->filesystem->reveal(),
            'var/versions'
        );
    }

    /**
     * @test
     */
    public function getVersionFiles_generates_list_of_VersionFiles(): void
    {
        self::assertSame(
            $this->versionFile->reveal()->getPathname(),
            $this->getFirstItem($this->versionStorage->getVersionFiles())->getFullPath()
        );
    }

    /**
     * @test
     *
     * @throws \Neusta\Pimcore\Maintenance\Versions\IOException
     */
    public function deleteVersionFile_throws_on_filesystem_IO_error(): void
    {
        $this->filesystem->remove(
            $this->versionFile->reveal()
                ->getPathname()
        )
            ->willThrow(new IOException('Filesystem has gone wild'));

        $this->expectException(\Neusta\Pimcore\Maintenance\Versions\IOException::class);

        $this->versionStorage->deleteVersionFile(new VersionFile($this->versionFile->reveal()));
    }

    /**
     * @return ObjectProphecy|\SplFileInfo
     */
    protected function createFileMock(string $path)
    {
        $versionFile = $this->prophesize(\SplFileInfo::class);
        $versionFile->getPathname()->willReturn($path);
        $versionFile->getFilename()->willReturn(basename($path));

        return $versionFile;
    }

    /**
     * @param VersionFile[] $iterable
     */
    private function getFirstItem(iterable $iterable): ?VersionFile
    {
        /* @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($iterable as $item) {
            return $item;
        }

        return null;
    }
}
