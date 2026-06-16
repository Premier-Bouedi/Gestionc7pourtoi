<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'time_spent_seconds',
        'assigned_date',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'time_spent_seconds' => 'integer',
            'assigned_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
