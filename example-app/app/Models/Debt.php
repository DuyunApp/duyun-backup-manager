<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    /** @use HasFactory<\Database\Factories\DebtFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'amount'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
