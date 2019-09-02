<?php
declare(strict_types=1);

namespace Tests\Unit\Neusta\Pimcore\Maintenance\Versions;

use Neusta\Pimcore\Maintenance\Versions\VersionFile;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use SplFileInfo;

class VersionFileTest extends TestCase
{
    private const VERSION_ID = 1;

    /**
     * @var ObjectProphecy|SplFileInfo
     */
    private $versionFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->versionFile = $this->prophesize(SplFileInfo::class);
        $this->versionFile->getPathname()->willReturn('var/versions/object/g0/1/' . self::VERSION_ID);
        $this->versionFile->getFilename()->willReturn(self::VERSION_ID);
    }

    /**
     * @test
     */
    public function getVersionId_returns_filename_as_version_id(): void
    {
        $versionFile = new VersionFile($this->versionFile->reveal());

        self::assertSame(self::VERSION_ID, $versionFile->getVersionId());
    }
}
