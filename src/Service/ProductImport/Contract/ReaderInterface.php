<?php

namespace App\Service\ProductImport\Contract;

use Generator;

interface ReaderInterface
{
    public function readRows(string $filePath): Generator;

    public function readAssoc(string $filePath): Generator;

    public function supports(string $type): bool;
}
