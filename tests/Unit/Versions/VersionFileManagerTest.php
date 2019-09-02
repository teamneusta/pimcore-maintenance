<?php
declare(strict_types=1);

namespace Tests\Unit\Neusta\Pimcore\Maintenance\Versions;

use Neusta\Pimcore\Maintenance\Versions\IOException;
use Neusta\Pimcore\Maintenance\Versions\VersionExistenceInterface;
use Neusta\Pimcore\Maintenance\Versions\VersionFile;
use Neusta\Pimcore\Maintenance\Versions\VersionFileEventHandlerInterface;
use Neusta\Pimcore\Maintenance\Versions\VersionFileManager;
use Neusta\Pimcore\Maintenance\Versions\VersionFileStorage;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class VersionFileManagerTest extends TestCase
{
    /**
     * @var VersionFileManager
     */
    private $versionFileManager;

    /**
     * @var ObjectProphecy|VersionExistenceInterface
     */
    private $versionExistenceTester;

    /**
     * @var ObjectProphecy|VersionFileStorage
     */
    private $versionStorage;

    /**
     * @var ObjectProphecy|VersionFile
     */
    private $versionFile;

    /**
     * @var ObjectProphecy|VersionFileEventHandlerInterface
     */
    private $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->versionFile = $this->prophesize(VersionFile::class);
        $this->versionFile->getVersionId()->willReturn(1);

        $this->versionStorage = $this->prophesize(VersionFileStorage::class);
        $this->versionStorage->getVersionFiles()->willReturn([$this->versionFile->reveal()]);

        $this->versionExistenceTester = $this->prophesize(VersionExistenceInterface::class);
        $this->versionExistenceTester->doesVersionExist(1)->willReturn(false);
        $this->versionStorage->deleteVersionFile($this->versionFile->reveal());

        $this->listener = $this->prophesize(VersionFileEventHandlerInterface::class);
        $this->listener->onRemoveFile($this->versionFile->reveal());

        $this->versionFileManager = new VersionFileManager(
            $this->versionStorage->reveal(),
            $this->versionExistenceTester->reveal(),
            $this->listener->reveal()
        );
    }

    /**
     * @test
     */
    public function removeOrphans_deletes_version_file_when_version_entry_missing(): void
    {
        $this->versionExistenceTester->doesVersionExist(1)->shouldBeCalled()->willReturn(false);
        $this->versionStorage->deleteVersionFile($this->versionFile->reveal())->shouldBeCalled();
        $this->versionFileManager->removeOrphans();
    }

    /**
     * @test
     */
    public function removeOrphans_leaves_version_file_when_version_entry_exists(): void
    {
        $this->versionExistenceTester->doesVersionExist(1)->shouldBeCalled()->willReturn(true);
        $this->versionStorage->deleteVersionFile($this->versionFile->reveal())->shouldNotBeCalled();
        $this->versionFileManager->removeOrphans();
    }

    /**
     * If a version file cannot be deleted storage will throw an IOException indicating a failure.
     * In such a situation manager should carry on with cleaning other files.
     *
     * @test
     *
     * @throws IOException
     */
    public function removeOrphans_tolerates_IOExceptions(): void
    {
        $brokenVersionFile = $this->prophesize(VersionFile::class);
        $brokenVersionFile->getVersionId()->willReturn(2);

        $this->versionExistenceTester->doesVersionExist(Argument::any())
            ->shouldBeCalled()
            ->willReturn(false);

        $exception = new IOException();
        $this->listener->onDeletionException($brokenVersionFile->reveal(), $exception);
        $this->listener->onRemoveFile($brokenVersionFile->reveal());

        $this->versionStorage->deleteVersionFile($brokenVersionFile->reveal())
            ->willThrow($exception);
        $this->versionStorage->getVersionFiles()
            ->willReturn([$brokenVersionFile->reveal(), $this->versionFile->reveal()]);

        $this->versionStorage->deleteVersionFile($this->versionFile->reveal())
            ->shouldBeCalled();

        $this->versionFileManager->removeOrphans();
    }

    /**
     * @test
     */
    public function removeOrphans_notifies_listener_when_file_is_removed(): void
    {
        $this->listener->onRemoveFile($this->versionFile->reveal())->shouldBeCalled();
        $this->versionFileManager->removeOrphans();
    }

    /**
     * @test
     *
     * @throws IOException
     */
    public function removeOrphans_notifies_listener_when_file_on_exception(): void
    {
        $ioException = new IOException();
        $this->versionStorage->deleteVersionFile($this->versionFile->reveal())
            ->willThrow($ioException);

        $this->listener->onDeletionException($this->versionFile->reveal(), $ioException)->shouldBeCalled();
        $this->versionFileManager->removeOrphans();
    }
}
