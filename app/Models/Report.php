<?php

namespace App\Models;

use Database\Factories\ReportsFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'severity', 'description', 'status',
        'program_id', 'user_id', 'evidence_image',
        'ai_summary', 'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    protected static function newFactory(): ReportsFactory
    {
        return ReportsFactory::new();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Report $report): void {
            $report->slug = Str::slug($report->title).'-'.Str::random(5);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Semantic Search Scope (The AI Soul)
    |--------------------------------------------------------------------------
    */
    public function scopeSearchSemantic($query, array $vector)
    {
        $vectorString = '['.implode(',', $vector).']';

        return $query->select('*')
            // 1. Calculate Cosine Distance
            ->selectRaw('embedding <=> ? AS distance', [$vectorString])
            // 2. CRITICAL: Only include reports that have been processed by AI
            ->whereNotNull('embedding')
            // 3. Order by closest meaning
            ->orderBy('distance', 'asc');
    }

    /*
    |--------------------------------------------------------------------------
    | Filtering Scope (The GIN Engine)
    |--------------------------------------------------------------------------
    */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            // Get driver once, no need for try-catch
            $driver = (string) $query->getConnection()->getConfig('driver');

            if ($driver === 'pgsql') {
                // 'websearch_to_tsquery' is better than 'plainto_tsquery'
                // because it handles quotes and logic like a real search engine.
                $query->whereRaw(
                    "to_tsvector('english', title || ' ' || description) @@ websearch_to_tsquery('english', ?)",
                    [$search]
                );
            } else {
                $query->where(function ($q) use ($search) {
                    // Use ILIKE for case-insensitive search in Postgres
                    $q->where('title', 'ILIKE', "%{$search}%")
                        ->orWhere('description', 'ILIKE', "%{$search}%");
                });
            }
        })
            ->when($filters['severity'] ?? null, function ($query, $severity) {
                $query->where('severity', $severity);
            });
    }

    public function scopeOrderUploadedFirst(Builder $query): Builder
    {
        return $query->orderByRaw('CASE WHEN evidence_image IS NULL THEN 1 ELSE 0 END ASC, created_at DESC');
    }
}
