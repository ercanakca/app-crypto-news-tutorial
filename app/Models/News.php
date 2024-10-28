<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'kind',
        'title',
        'slug',
        'published_at',
        'source_title',
        'source_domain',
        'source_type',
    ];

    public function coins()
    {
        return $this->belongsToMany(Coin::class, 'coin_news');
    }
}
