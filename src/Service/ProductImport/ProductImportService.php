<?php

namespace App\Service\ProductImport;

use App\Entity\Product;
use App\Service\ProductImport\Contract\ReaderInterface;
use App\Service\ProductImport\DTO\ProductDTO;
use App\Service\ProductImport\Report\ImportReport;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductImportService
{
    private ReaderInterface $reader;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
        private readonly ReaderFactory $readerFactory,
        private readonly int $batchSize,
    ) {
    }

    public function import(string $filePath): ImportReport
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException(sprintf('Import file not found: %s', $filePath));
        }

        $readerType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $this->reader = $this->readerFactory->createReader($readerType);

        return $this->process($filePath);
    }

    private function prepareLastSeenMapping(string $filePath): array
    {
        $mapping = [];

        foreach ($this->reader->readAssoc($filePath) as $lineNumber => $item) {
            $mapping[$item['code']] = $lineNumber;
        }

        return $mapping;
    }

    private function process(string $filePath): ImportReport
    {
        $lastSeenMapping = $this->prepareLastSeenMapping($filePath);

        $importReport = new ImportReport();

        $this->entityManager->wrapInTransaction(function () use ($filePath, $lastSeenMapping, $importReport) {
            foreach ($this->readBatches($filePath, $lastSeenMapping) as $batch) {
                $this->writeBatch($batch, $importReport);
            }
        });

        return $importReport;
    }

    public function readBatches(string $filePath, array $lastSeenMapping): \Generator
    {
        $batch = [];
        $itemsCount = 0;

        foreach ($this->reader->readAssoc($filePath) as $lineNumber => $item) {
            $dto = $this->validateItem($item);

            if ($lastSeenMapping[$dto->code] !== $lineNumber) {
                continue;
            }

            $batch[] = $dto;
            ++$itemsCount;

            if ($itemsCount >= $this->batchSize) {
                yield $batch;
                $batch = [];
                $itemsCount = 0;
            }
        }

        if (!empty($batch)) {
            yield $batch;
        }
    }

    private function validateItem(array $item): ProductDTO
    {
        $dto = new ProductDTO(
            $item['code'] ?? null,
            $item['name'] ?? null,
            $item['price'] ?? null,
            $item['quantity'] ?? null,
        );

        $errors = $this->validator->validate($dto);

        if (count($errors) !== 0) {
            throw new InvalidArgumentException('Invalid product item');
        }

        return $dto;
    }

    private function writeBatch(array $batch, ImportReport $importReport): void
    {
        $itemsCount = 0;

        foreach ($batch as $productDTO) {
            $product = $this->entityManager->getRepository(Product::class)->findOneBy(['code' => $productDTO->code]);

            if (!$product) {
                $product = new Product();
                $product
                    ->setCode($productDTO->code)
                    ->setName($productDTO->name)
                    ->setPrice($productDTO->price)
                    ->setQuantity($productDTO->quantity);

                $importReport->addProduct($productDTO);
            } else {
                $product
                    ->setName($productDTO->name)
                    ->setPrice($productDTO->price)
                    ->setQuantity($productDTO->quantity);
            }

            $this->entityManager->persist($product);

            if (($itemsCount % $this->batchSize) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            $itemsCount++;
        }

        if (($itemsCount % $this->batchSize) !== 0) {
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }
}
