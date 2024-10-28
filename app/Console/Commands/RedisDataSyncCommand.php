<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RedisDataSyncService;
use Illuminate\Support\Facades\Log;

class RedisDataSyncCommand extends Command
{
    protected $signature = 'app:redis-data-sync-command {--force}';
    protected $description = 'News ve Coins verilerini Redis e kaydeder';

    protected RedisDataSyncService $redisDataSyncService;

    public function __construct(RedisDataSyncService $redisDataSyncService)
    {
        parent::__construct();
        $this->redisDataSyncService = $redisDataSyncService;
    }

    public function handle()
    {
        $this->info('Sync işlemi başlatılıyor.');
        Log::info('Sync işlemi başlatıldı.');

        try {

            $this->redisDataSyncService->syncNews();
            $this->redisDataSyncService->syncCoins();

            Log::info('Sync işlemi tamamlandı.');
            $this->info('Sync işlemi tamamlandı.');

        } catch (\Exception $e) {
            Log::error('Sync işlemi sırasında hata oluştu! ' . $e->getMessage());
            $this->error('Sync işlemi sırasında bir hata oluştu.');
            return;
        }

    }

}
