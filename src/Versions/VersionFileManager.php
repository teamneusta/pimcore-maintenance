<?php
declare(strict_types=1);

namespace Neusta\Pimcore\Maintenance\Versions;

class VersionFileManager
{
    /**
     * @var VersionFileStorage
     */
    private $versionStorage;

    /**
     * @var VersionExistenceInterface
     */
    private $versionExistenceTester;

    /**
     * @var VersionFileEventHandlerInterface
     */
    private $eventHandler;

    public function __construct(
        VersionFileStorage $versionStorage,
        VersionExistenceInterface $versionExistenceTester,
        ?VersionFileEventHandlerInterface $eventHandler = null
    ) {
        $this->versionStorage = $versionStorage;
        $this->versionExistenceTester = $versionExistenceTester;
        $this->setEventHandler($eventHandler ?? new NullVersionFileEventHandler);
    }

    public function removeOrphans(): void
    {
        foreach ($this->versionStorage->getVersionFiles() as $versionFile) {
            $this->removeOrphanedFile($versionFile);
        }
    }

    public function setEventHandler(VersionFileEventHandlerInterface $eventHandler): void
    {
        $this->eventHandler = $eventHandler;
    }

    protected function handleDeletionException(VersionFile $versionFile, IOException $e): void
    {
        $this->eventHandler->onDeletionException($versionFile, $e);
    }

    protected function removeFile(VersionFile $versionFile): void
    {
        try {
            $this->eventHandler->onRemoveFile($versionFile);
            $this->versionStorage->deleteVersionFile($versionFile);
        } catch (IOException $e) {
            $this->handleDeletionException($versionFile, $e);
        }
    }

    private function removeOrphanedFile(VersionFile $versionFile): void
    {
        if ($this->isOrphaned($versionFile)) {
            $this->removeFile($versionFile);
        }
    }

    /**
     * Tests if the version file has a corresponding entry in the versions table.
     *
     * @return bool true if db entry for version file is missing, false - otherwise
     */
    private function isOrphaned(VersionFile $versionFile): bool
    {
        return !$this->versionExistenceTester->doesVersionExist($versionFile->getVersionId());
    }
}
