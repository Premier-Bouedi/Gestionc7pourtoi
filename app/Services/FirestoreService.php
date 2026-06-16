<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\FirestoreClient;
use App\Data\FirestoreIncident;
use App\Data\FirestoreOrder;
use App\Data\FirestoreProduct;
use App\Support\CatalogData;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FirestoreService
{
    private const TOKEN_CACHE_KEY = 'firebase.access_token';

    public function isConfigured(): bool
    {
        $credentials = config('firebase.credentials');

        return is_string($credentials) && $credentials !== '' && file_exists($credentials);
    }

    public function isConnected(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        return $this->accessToken() !== null;
    }

    /**
     * @return Collection<int, FirestoreClient>
     */
    public function clients(?string $search = null): Collection
    {
        return $this->fetchCollection(config('firebase.collections.clients'))
            ->map(fn (array $doc) => $this->mapClient($doc))
            ->filter(fn (FirestoreClient $client) => $this->matchesClientSearch($client, $search))
            ->sortBy(fn (FirestoreClient $client) => mb_strtolower($client->contact_name))
            ->values();
    }

    public function countClients(): int
    {
        return $this->clients()->count();
    }

    /**
     * @return Collection<int, FirestoreProduct>
     */
    public function products(): Collection
    {
        return $this->fetchCollection(config('firebase.collections.products'))
            ->map(fn (array $doc) => $this->mapProduct($doc))
            ->filter()
            ->sortBy(fn (FirestoreProduct $product) => mb_strtolower($product->name))
            ->values();
    }

    /**
     * @return Collection<int, FirestoreOrder>
     */
    public function orders(): Collection
    {
        return $this->fetchCollection(config('firebase.collections.orders'))
            ->map(fn (array $doc) => $this->mapOrder($doc))
            ->filter()
            ->sortByDesc(fn (FirestoreOrder $order) => $order->created_at->timestamp)
            ->values();
    }

    /**
     * @return Collection<int, FirestoreIncident>
     */
    public function incidents(): Collection
    {
        $orders = $this->orders()->keyBy('id');

        return $this->fetchCollection(config('firebase.collections.incidents'))
            ->map(function (array $doc) use ($orders) {
                $incident = $this->mapIncident($doc);
                if ($incident === null) {
                    return null;
                }

                return new FirestoreIncident(
                    id: $incident->id,
                    order_id: $incident->order_id,
                    type: $incident->type,
                    description: $incident->description,
                    status: $incident->status,
                    order: $orders->get($incident->order_id),
                );
            })
            ->filter()
            ->values();
    }

    /**
     * @return Collection<int, array{id: string, data: array<string, mixed>}>
     */
    private function fetchCollection(string $collectionName): Collection
    {
        $token = $this->accessToken();
        if ($token === null) {
            return collect();
        }

        $projectId = config('firebase.project_id');
        $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/{$collectionName}";

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(15)
                ->get($url);

            if ($response->failed()) {
                Log::warning('Firestore REST: lecture échouée', [
                    'collection' => $collectionName,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return collect();
            }

            $documents = $response->json('documents', []);

            return collect($documents)->map(function (array $document) {
                $name = (string) ($document['name'] ?? '');
                $id = $name !== '' ? basename($name) : '';

                return [
                    'id' => $id,
                    'data' => $this->decodeFirestoreFields($document['fields'] ?? []),
                ];
            });
        } catch (Throwable $exception) {
            Log::warning('Firestore REST: exception', [
                'collection' => $collectionName,
                'message' => $exception->getMessage(),
            ]);

            return collect();
        }
    }

    private function accessToken(): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $cached = Cache::get(self::TOKEN_CACHE_KEY);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        try {
            $credentials = json_decode(
                (string) file_get_contents(config('firebase.credentials')),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            $now = time();
            $payload = [
                'iss' => $credentials['client_email'],
                'sub' => $credentials['client_email'],
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
                'scope' => 'https://www.googleapis.com/auth/datastore',
            ];

            $jwt = JWT::encode($payload, $credentials['private_key'], 'RS256');

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->failed()) {
                Log::error('Firestore REST: échec OAuth2', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $token = $response->json('access_token');
            if (is_string($token) && $token !== '') {
                Cache::put(self::TOKEN_CACHE_KEY, $token, 3300);
            }

            return is_string($token) ? $token : null;
        } catch (Throwable $exception) {
            Log::error('Firestore REST: impossible de générer le token', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    private function decodeFirestoreFields(array $fields): array
    {
        $decoded = [];

        foreach ($fields as $key => $value) {
            $decoded[$key] = $this->decodeFirestoreValue($value);
        }

        return $decoded;
    }

    private function decodeFirestoreValue(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_key_exists('stringValue', $value)) {
            return $value['stringValue'];
        }

        if (array_key_exists('integerValue', $value)) {
            return (int) $value['integerValue'];
        }

        if (array_key_exists('doubleValue', $value)) {
            return (float) $value['doubleValue'];
        }

        if (array_key_exists('booleanValue', $value)) {
            return (bool) $value['booleanValue'];
        }

        if (array_key_exists('timestampValue', $value)) {
            return Carbon::parse($value['timestampValue']);
        }

        if (array_key_exists('nullValue', $value)) {
            return null;
        }

        if (array_key_exists('arrayValue', $value)) {
            $values = $value['arrayValue']['values'] ?? [];

            return collect($values)
                ->map(fn ($item) => $this->decodeFirestoreValue($item))
                ->all();
        }

        if (array_key_exists('mapValue', $value)) {
            return $this->decodeFirestoreFields($value['mapValue']['fields'] ?? []);
        }

        return $value;
    }

    /**
     * @param array{id: string, data: array<string, mixed>} $document
     */
    private function mapClient(array $document): FirestoreClient
    {
        $data = $document['data'];

        return new FirestoreClient(
            id: $document['id'],
            contact_name: $this->stringValue($data, ['contact_name', 'name', 'fullName', 'nom'], 'Sans nom'),
            company_name: $this->stringValue($data, ['company_name', 'company', 'entreprise'], '—'),
            email: $this->nullableString($data, ['email', 'mail']),
            phone_whatsapp: $this->nullableString($data, ['phone_whatsapp', 'phone', 'whatsapp', 'telephone']),
            city: $this->nullableString($data, ['city', 'ville']),
            address: $this->nullableString($data, ['address', 'adresse']),
            notes: $this->nullableString($data, ['notes', 'note']),
            invoices_count: $this->intValue($data, ['invoices_count', 'invoiceCount'], 0),
        );
    }

    /**
     * @param array{id: string, data: array<string, mixed>} $document
     */
    private function mapProduct(array $document): ?FirestoreProduct
    {
        $data = $document['data'];
        $name = $this->stringValue($data, ['name', 'title', 'nom'], '');

        if ($name === '') {
            return null;
        }

        $priceXaf = $this->intValue($data, ['price_xaf', 'base_price', 'price', 'prix'], 0);
        $priceMad = $this->intValue($data, ['price_mad', 'priceMad'], 0);

        if ($priceMad === 0 && $priceXaf > 0) {
            $priceMad = CatalogData::priceMad($priceXaf);
        }

        $imageUrl = $this->nullableString($data, ['image_url', 'image', 'imageUrl']);
        if ($imageUrl === null && isset($data['images']) && is_array($data['images']) && count($data['images']) > 0) {
            $imageUrl = (string) $data['images'][0];
        }

        return new FirestoreProduct(
            id: $document['id'],
            name: $name,
            model: ucfirst($this->stringValue($data, ['model', 'category', 'categorie'], 'C7Pourt3')),
            price_xaf: $priceXaf,
            price_mad: $priceMad,
            stock_libreville: $this->intValue($data, ['stock_libreville', 'stock', 'stockGabon', 'stock_morocco'], 0),
            image_url: $imageUrl,
        );
    }

    /**
     * @param array{id: string, data: array<string, mixed>} $document
     */
    private function mapOrder(array $document): ?FirestoreOrder
    {
        $data = $document['data'];

        return new FirestoreOrder(
            id: $document['id'],
            customer_name: $this->stringValue($data, ['customer_name', 'customerName', 'client_name'], '—'),
            customer_whatsapp: $this->stringValue($data, ['customer_whatsapp', 'whatsapp', 'phone'], '—'),
            address_libreville: $this->stringValue($data, ['address_libreville', 'address', 'adresse'], '—'),
            total_amount: $this->intValue($data, ['total_amount', 'total', 'amount'], 0),
            status: $this->stringValue($data, ['status', 'statut'], 'pending'),
            created_at: $this->carbonValue($data, ['created_at', 'createdAt']),
        );
    }

    /**
     * @param array{id: string, data: array<string, mixed>} $document
     */
    private function mapIncident(array $document): ?FirestoreIncident
    {
        $data = $document['data'];

        return new FirestoreIncident(
            id: $document['id'],
            order_id: $this->stringValue($data, ['order_id', 'orderId'], ''),
            type: $this->stringValue($data, ['type'], 'incident'),
            description: $this->stringValue($data, ['description', 'message'], ''),
            status: $this->stringValue($data, ['status', 'statut'], 'open'),
        );
    }

    private function matchesClientSearch(FirestoreClient $client, ?string $search): bool
    {
        if ($search === null || trim($search) === '') {
            return true;
        }

        $needle = mb_strtolower(trim($search));
        $haystack = mb_strtolower(implode(' ', array_filter([
            $client->contact_name,
            $client->company_name,
            $client->email,
            $client->phone_whatsapp,
            $client->city,
            $client->address,
        ])));

        return str_contains($haystack, $needle);
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $keys
     */
    private function stringValue(array $data, array $keys, string $default = ''): string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                return trim((string) $data[$key]);
            }
        }

        return $default;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $keys
     */
    private function nullableString(array $data, array $keys): ?string
    {
        $value = $this->stringValue($data, $keys, '');

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $keys
     */
    private function intValue(array $data, array $keys, int $default = 0): int
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && is_numeric($data[$key])) {
                return (int) $data[$key];
            }
        }

        return $default;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $keys
     */
    private function carbonValue(array $data, array $keys): Carbon
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $data) || $data[$key] === null) {
                continue;
            }

            $value = $data[$key];

            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value);
            }

            if (is_string($value) && $value !== '') {
                return Carbon::parse($value);
            }
        }

        return Carbon::now();
    }
}
