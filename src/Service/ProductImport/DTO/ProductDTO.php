<?php

namespace App\Service\ProductImport\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ProductDTO
{
    public function __construct(
        ?string $code = null,
        ?string $name = null,
        ?float $price = null,
        ?float $quantity = null,
    ) {
        $this->code = $code;
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    #[Assert\NotBlank(message: 'Product code is required.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Product code cannot be longer than {{ limit }} characters.'
    )]
    public ?string $code = null;

    #[Assert\NotBlank(message: 'Product name is required.')]
    #[Assert\Length(
        max: 2000,
        maxMessage: 'Product name cannot be longer than {{ limit }} characters.'
    )]
    public ?string $name = null;

    #[Assert\NotNull(message: 'Price is required.')]
    #[Assert\Positive(message: 'Price must be greater than zero.')]
    public ?float $price = null;

    #[Assert\NotNull(message: 'Quantity is required.')]
    #[Assert\PositiveOrZero(message: 'Quantity cannot be negative.')]
    #[Assert\Type(
        type: 'numeric',
        message: 'Quantity must be a valid number.'
    )]
    public ?float $quantity = null;
}
