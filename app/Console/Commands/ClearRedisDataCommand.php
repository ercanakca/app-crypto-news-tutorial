<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ClearRedisDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-redis-data-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Redis de ki mevcut verileri siler';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Silme işlemi başlatılıyor.');
        Redis::flushall();
        $this->info('Silme işlemi tamamlandı.');
    }
}
