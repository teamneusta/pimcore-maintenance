<?php
declare(strict_types=1);

namespace Neusta\Pimcore\Maintenance\Versions;

interface VersionExistenceInterface
{
    /**
     * @param int $versionId id of the version to check existence for
     *
     * @return bool true if version exists, false - otherwise
     */
    public function doesVersionExist(int $versionId): bool;
}
