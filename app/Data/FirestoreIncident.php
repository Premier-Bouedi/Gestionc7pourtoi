<?php

declare(strict_types=1);

namespace App\Data;

final class FirestoreIncident
{
    public function __construct(
        public readonly string $id,
        public readonly string $order_id,
        public readonly string $type,
        public readonly string $description,
        public readonly string $status,
        public readonly ?FirestoreOrder $order = null,
    ) {}
}
