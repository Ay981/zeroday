<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Program extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'bounty_multiplier'];

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    protected static function booted()
    {
        // Whenever a program is created, updated, or deleted...
        static::saved(fn () => Cache::forget('programs_all'));
        static::deleted(fn () => Cache::forget('programs_all'));
    }
    //
}
