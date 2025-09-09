<?php

namespace App\Service\ProductImport\Report;

use App\Service\ProductImport\DTO\ProductDTO;

class ImportReport
{
    private int $addedItemsCount = 0;
    private float $addedItemsTotal = 0.0;

    public function addProduct(ProductDTO $productDTO): void
    {
        $this->addedItemsCount++;
        $this->addedItemsTotal += round($productDTO->price * $productDTO->quantity, 2);
    }

    public function getAddedItemsCount(): int
    {
        return $this->addedItemsCount;
    }

    public function getAddedItemsTotal(): float
    {
        return $this->addedItemsTotal;
    }
}
