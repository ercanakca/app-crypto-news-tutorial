<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    use HasFactory;

    protected $table = 'coins';

    protected $fillable = [
        'code',
        'slug',
        'title',
    ];

    public function news()
    {
        return $this->belongsToMany(News::class, 'coin_news');
    }
}
