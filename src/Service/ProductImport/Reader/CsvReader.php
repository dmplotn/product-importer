<?php

namespace App\Service\ProductImport\Reader;

use App\Service\ProductImport\Contract\ReaderInterface;
use Generator;
use RuntimeException;

readonly class CsvReader implements ReaderInterface
{
    private const TYPE = 'csv';

    public function __construct(
        private string $delimiter = ',',
        private string $enclosure = '"',
        private string $escape = '/',
        private bool $hasHeader = true,
    ) {
    }

    public function readRows(string $filePath): \Generator
    {
        $handle = fopen($filePath, 'rb');
        if (false === $handle) {
            throw new RuntimeException(sprintf('Failed to open file: %s', $filePath));
        }
        flock($handle, LOCK_SH);

        try {
            $lineNumber = 0;
            while (false !== $row = fgetcsv($handle, 0, $this->delimiter, $this->enclosure, $this->escape)) {
                $lineNumber++;

                if (1 === count($row) && null === $row[0]) {
                    continue;
                }

                yield $lineNumber => $row;
            }
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function readAssoc(string $filePath): Generator
    {
        $headers = null;

        foreach ($this->readRows($filePath) as $lineNumber => $row) {
            if ($this->hasHeader && null === $headers) {
                $headers = $row;
                continue;
            }

            if (null !== $headers) {
                $assocRow = [];
                foreach ($headers as $index => $header) {
                    $assocRow[$header] = $row[$index] ?? null;
                }
                yield $lineNumber => $assocRow;
            } else {
                yield $lineNumber => $row;
            }
        }
    }

    public function supports(string $type): bool
    {
        return self::TYPE === $type;
    }
}
