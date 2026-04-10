<?php

namespace App\Models;

use Database\Factories\ReportsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'severity',
        'description',
        'status',
        'program_id',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
