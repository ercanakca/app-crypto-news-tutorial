<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\News;
use App\Models\Coin;
use Illuminate\Support\Facades\Redis;

class RedisDataSyncService
{
    protected int $ttl = 3600;

    public function syncNews()
    {
        $news = News::with('coins')->get();

        $news->each(function ($newsItem) {
            try {
                $key = "news:{$newsItem->id}";

                if (Redis::exists($key)) {
                    Log::info("News {$newsItem->id} zaten Redis'te mevcut, ekleme atlandı.");
                    return;
                }

                $value = json_encode([
                    'kind' => $newsItem->kind,
                    'title' => $newsItem->title,
                    'slug' => $newsItem->slug,
                    'published_at' => $newsItem->published_at,
                    'source_title' => $newsItem->source_title,
                    'source_domain' => $newsItem->source_domain,
                    'source_type' => $newsItem->source_type,
                    'coins' => $newsItem->coins->map(fn($coin) => [
                        'id' => $coin->id,
                        'code' => $coin->code,
                        'title' => $coin->title,
                    ])->toArray()
                ]);

                Redis::set($key, $value);
                Redis::expire($key, $this->ttl);

                Log::info("News {$newsItem->id} Redis'e eklendi.");
            } catch (\Exception $e) {
                Log::error("News {$newsItem->id} Redis'e kaydedilirken hata oluştu: " . $e->getMessage());
            }
        });
    }

    public function syncCoins()
    {
        $coins = Coin::with('news')->get();

        $coins->each(function ($coin) {
            try {
                $key = "coin:{$coin->id}";
                $value = json_encode([
                    'code' => $coin->code,
                    'slug' => $coin->slug,
                    'title' => $coin->title,
                    'news' => $coin->news->map(fn($news) => [
                        'id' => $news->id,
                        'title' => $news->title,
                        'published_at' => $news->published_at,
                    ])->toArray()
                ]);

                Log::info("KONTROL:::::::: {$coin->id} Redis kontrol.", [$key, $value]);

                Redis::set($key, $value);
                Redis::expire($key, $this->ttl);

                Log::info("Coin {$coin->id} Redis'e başarıyla kaydedildi.");
            } catch (\Exception $e) {
                Log::error("Coin {$coin->id} Redis'e kaydedilirken hata oluştu: " . $e->getMessage());
            }
        });
    }
}
