<?php

declare(strict_types=1);

namespace App\Data;

final class FirestoreProduct
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $model,
        public readonly int $price_xaf,
        public readonly int $price_mad,
        public readonly int $stock_libreville,
        public readonly ?string $image_url = null,
    ) {}
}
