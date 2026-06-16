<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'client_id',
        'title',
        'description',
        'status',
        'budget',
        'hourly_rate',
        'start_date',
        'deadline',
        'google_calendar_event_id',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'start_date' => 'date',
            'deadline' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
