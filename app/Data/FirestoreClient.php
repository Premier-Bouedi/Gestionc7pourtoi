<?php

declare(strict_types=1);

namespace App\Data;

final class FirestoreClient
{
    public function __construct(
        public readonly string $id,
        public readonly string $contact_name,
        public readonly string $company_name,
        public readonly ?string $email = null,
        public readonly ?string $phone_whatsapp = null,
        public readonly ?string $city = null,
        public readonly ?string $address = null,
        public readonly ?string $notes = null,
        public readonly int $invoices_count = 0,
    ) {}
}
