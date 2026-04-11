<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'bounty_multiplier'];
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
    //
}
