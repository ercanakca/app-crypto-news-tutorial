<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('fetch-data-news-every-minute', function () {
    $this->info('Fetching news data...');
    $exitCode = Artisan::call('app:fetch-data-news', [
        '--force' => true,
    ]);
    $this->info('Command executed with exit code: ' . $exitCode);
})->purpose('Fetch data news every minute')->everyMinute();

Artisan::command('redis-data-news-every-hour', function () {
    $this->info('Redis news data...');
    $exitCode = Artisan::call('app:redis-data-sync-command', [
        '--force' => true,
    ]);
    $this->info('Command executed with exit code: ' . $exitCode);
})->purpose('Redis data news every hour')->hourly();
