<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reports extends Model
{
     protected $fillable = [
        'user_id',
        'title',
        'severity',
        'description',
        'status'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    //
}
