# Pimcore Maintenance Jobs

The aim of this project is to extend the suite of pimcore maintenance jobs. At the moment 
it only contains one job that helps pimcore dealing with [orphaned file versions](https://github.com/pimcore/pimcore/issues/4807).

## Usage

```bash
composer require neusta/pimcore-maintenance
```

First, you will have to provide a implementation for `\Neusta\Pimcore\Maintenance\Versions\VersionExistenceInterface`.
Here is an example:
```php
<?php
declare(strict_types=1);

namespace AppBundle\Some\Package;

use Neusta\Pimcore\Maintenance\Versions\VersionExistenceInterface;
use Pimcore\Model\Version;

class VersionExistenceTester implements VersionExistenceInterface
{
    public function doesVersionExist(int $versionId): bool
    {
        try {
            return null !== Version::getById($versionId);
        } catch (\Exception $ex) {
            return false;
        }
    }
}

```

Now you can register `\Neusta\Pimcore\Maintenance\Versions\VersionFileManager::removeOrphans`
as a pimcore maintenance job. For pimcore 5.x you will need to register an event listener in
your `services.yml`

```
    AppBundle\Some\Package\MaintenanceJobRegistrator:
    tags:
        - { name: pimcore.maintenance.task, type: remove_version_orphans }
```

`MaintenanceJobRegistrator` implementation could look like this:

```php
<?php
declare(strict_types=1);

namespace AppBundle\Janitor\Versions;

use Pimcore\Maintenance\TaskInterface;

class MaintenanceJobRegistrator implements TaskInterface
{
    public const JOB_ID = 'remove_version_orphans';

    /**
     * @var VersionFileManager
     */
    private $versionManager;

    /**
     * MaintenanceJobRegistrator constructor.
     */
    public function __construct(VersionFileManager $versionManager)
    {
        $this->versionManager = $versionManager;
    }

    public function execute(): void
    {
        $this->versionManager->removeOrphans();
    }
}
``` 
Now you can remove orphaned version files by executing `pimcore:maintenance -j remove_version_orphans`

For pimcore 6.x refer to [official documentation](https://pimcore.com/docs/6.x/Development_Documentation/Extending_Pimcore/Maintenance_Tasks.html)
for registering a task.
