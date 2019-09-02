<?php
declare(strict_types=1);

namespace Neusta\Pimcore\Maintenance\Versions;

class NullVersionFileEventHandler implements VersionFileEventHandlerInterface
{
    public function onRemoveFile(VersionFile $versionFile): void
    {
    }

    public function onDeletionException(VersionFile $versionFile, IOException $e): void
    {
    }
}
