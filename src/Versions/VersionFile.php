<?php
declare(strict_types=1);

namespace Neusta\Pimcore\Maintenance\Versions;

class VersionFile
{
    /**
     * @var \SplFileInfo
     */
    private $versionFile;

    public function __construct(\SplFileInfo $versionFile)
    {
        $this->versionFile = $versionFile;
    }

    public function getVersionId(): int
    {
        return (int) $this->versionFile->getFilename();
    }

    public function getFullPath(): string
    {
        return $this->versionFile->getPathname();
    }
}
