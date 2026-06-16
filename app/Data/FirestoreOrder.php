<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;

final class FirestoreOrder
{
    public function __construct(
        public readonly string $id,
        public readonly string $customer_name,
        public readonly string $customer_whatsapp,
        public readonly string $address_libreville,
        public readonly int $total_amount,
        public readonly string $status,
        public readonly CarbonInterface $created_at,
    ) {}
}
