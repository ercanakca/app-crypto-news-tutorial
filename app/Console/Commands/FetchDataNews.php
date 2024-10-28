<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Artisan;
use App\Models\News;
use App\Models\Coin;

class FetchDataNews extends Command
{
    protected $signature = 'app:fetch-data-news {--force}';
    protected $description = 'Her 1 dk da haberleri (coin bilgisi olmayan haberler hariç) veritabanına kaydeder.';

    private $startTime;
    private $endTime;
    private $addedNewsCount;
    private $skippedCount;

    public function __construct()
    {
        parent::__construct();
        $this->addedNewsCount = 0;
        $this->skippedCount = 0;
    }

    public function handle()
    {
        $this->setStartTime(now());

        $this->line('Haber verileri çekme işlemi başladı ..........: ' . $this->getStartTime(), 'info');

        $nextPageUrl = config('cryptopanic.api_url') . '/?auth_token=' . config('cryptopanic.app_token') . '&kind=news&page=1';

        do {
            $response = Http::withHeaders([
                'Referer' => 'https://cryptopanic.com',
            ])->get($nextPageUrl);

            if ($response->successful()) {
                $this->processNewsData($response['results']);
                $nextPageUrl = $response['next'] ?? null;
            } else {
                Log::error('Cryptopanic API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                break;
            }

        } while ($nextPageUrl);

        $this->checkRedisDataSync();

        $this->setEndTime(now());
        $this->logDuration();
    }

    private function processNewsData(array $newsData)
    {
        foreach ($newsData as $item) {
            if (empty($item['currencies'])) {
                $this->incrementSkippedCount();
                Log::info('Atlanan haber: ', [
                    'title' => $item['title'],
                    'reason' => 'Currencies alanı boş olduğu için eklenmedi.'
                ]);
                continue;
            }

            $newsItem = News::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'kind' => $item['kind'],
                    'slug' => $item['slug'],
                    'published_at' => $this->formatDate($item['published_at']),
                    'source_title' => $item['source']['title'],
                    'source_type' => $item['source']['type'],
                    'source_domain' => $item['source']['domain'],
                ]
            );

            if ($newsItem->wasRecentlyCreated) {
                $this->incrementAddedNewsCount();
            }

            foreach ($item['currencies'] as $currency) {
                $coin = $this->getOrCreateCoin($currency);
                // Coin ve news => for pivot table
                $newsItem->coins()->syncWithoutDetaching([$coin->id]);
            }
        }

    }

    private function getOrCreateCoin(array $currency): Coin
    {
        return Coin::firstOrCreate(
        //['code' => $currency['code']], Code = ETH or title Bridged Ether (StarkGate),
            ['slug' => $currency['slug']],
            [
                'slug' => $currency['slug'],
                'code' => $currency['code'],
                'title' => $currency['title'],
            ]
        );
    }

    private function formatDate(string $date): ?string
    {
        $dateTime = \DateTime::createFromFormat(DATE_ISO8601, $date);
        return $dateTime ? $dateTime->format('Y-m-d H:i:s') : null;
    }

    private function logDuration()
    {
        $this->line('Haber verileri çekme işlemi tamamlandı .......: ' . $this->getEndTime()->toDateTimeString() . ' - ' . $this->getStartTime()->diffInSeconds($this->getEndTime()) . ' sn', 'info');
        Log::info('Haber verileri çekme işlemi tamamlandı ......:', [
            'end_time' => $this->getEndTime()->toDateTimeString(),
            'duration' => $this->getStartTime()->diffInSeconds($this->getEndTime()) . ' saniye',
            'added_news_count' => $this->getAddedNewsCount(),
        ]);

        $this->line('Atlanan haber sayısı  ........................: ' . $this->skippedCount, 'info');
        $this->line('Yeni eklenen haber sayısı  ...................: ' . $this->getAddedNewsCount(), 'info');
    }

    private function getStartTime()
    {
        return $this->startTime;
    }

    private function setStartTime($time)
    {
        $this->startTime = $time;
    }

    private function getEndTime()
    {
        return $this->endTime;
    }

    private function setEndTime($time)
    {
        $this->endTime = $time;
    }

    private function getAddedNewsCount()
    {
        return $this->addedNewsCount;
    }

    private function incrementAddedNewsCount()
    {
        $this->addedNewsCount++;
    }

    private function incrementSkippedCount()
    {
        $this->skippedCount++;
    }

    protected function checkRedisDataSync()
    {
        $newsCount = Redis::keys('news:*');
        $coinsCount = Redis::keys('coin:*');

        if (empty($newsCount) && empty($coinsCount)) {
            Artisan::call('app:redis-data-sync-command');
            $this->info('RedisDataSyncCommand çalıştırıldı');
        }
    }
}
