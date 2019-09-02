<?php
declare(strict_types=1);

namespace Neusta\Pimcore\Maintenance\Versions;

interface VersionFileEventHandlerInterface
{
    public function onRemoveFile(VersionFile $versionFile): void;

    public function onDeletionException(VersionFile $versionFile, IOException $e): void;
}
