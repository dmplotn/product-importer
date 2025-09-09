<?php

namespace App\Service\ProductImport;

use App\Service\ProductImport\Contract\ReaderInterface;
use InvalidArgumentException;

readonly class ReaderFactory
{
    public function __construct(private iterable $readers)
    {
    }

    public function createReader(string $type): ReaderInterface
    {
        foreach ($this->readers as $reader) {
            if ($reader->supports($type)) {
                return $reader;
            }
        }

        throw new InvalidArgumentException(sprintf('Unknown file type: %s', $type));
    }
}
