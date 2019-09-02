<?php
declare(strict_types=1);

namespace Neusta\Pimcore\Maintenance\Versions;

use Symfony\Component\Filesystem\Exception\IOException as SymfonyIOException;

class IOException extends \Exception
{
    public static function fromSymfonyIOException(SymfonyIOException $e): self
    {
        return new self($e->getMessage(), $e->getCode(), $e->getPrevious());
    }
}
