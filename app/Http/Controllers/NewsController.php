<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class NewsController extends Controller
{
    public function news(Request $request)
    {
        $selectedCoins = $request->get('coins', []);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        [$news, $coins] = $this->getArticlesWithCoins($selectedCoins, $startDate, $endDate);

        return view('news', [
            'coins' => collect($coins)->sortBy('title')->values()->all(),
            'news' => $news,
            'selectedCoins' => $selectedCoins,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    protected function getArticlesWithCoins(array $selectedCoins = [], $startDate = null, $endDate = null)
    {
        $newKeys = Redis::keys(config('database.redis.default.prefix') . 'news:*');

        $news = collect($newKeys)->map(function ($newKey) {
            $newId = str_replace('laravel_database_news:', '', $newKey);
            $newRedisKey = 'news:' . $newId;
            $newData = Redis::get($newRedisKey);

            if ($newData) {
                $newItem = json_decode($newData, true);

                $coinTitles = array_column($newItem['coins'], 'title');
                $newItem['coin_found'] = implode(', ', $coinTitles);

                return $newItem;
            }

            return null;
        })->filter();

        $selectedCoins = array_map('intval', $selectedCoins);

        if (!empty($selectedCoins) || !empty($startDate) || !empty($endDate)) {
            $news = $news->filter(function ($newItem) use ($selectedCoins, $startDate, $endDate) {
                $publishedAt = Carbon::parse($newItem['published_at']);


                if (!empty($startDate)) {
                    $startDate = Carbon::parse($startDate);
                    if ($publishedAt < $startDate) {
                        return false;
                    }
                }

                if (!empty($endDate)) {
                    $endDate = Carbon::parse($endDate);
                    if ($publishedAt > $endDate) {
                        return false;
                    }
                }

                $newCoins = array_column($newItem['coins'], 'id');
                return empty($selectedCoins) || !empty(array_intersect($selectedCoins, $newCoins));
            });
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $currentPageItems = $news->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedArticles = new LengthAwarePaginator($currentPageItems, $news->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $coins = $this->getCoins();

        return [$paginatedArticles, $coins];
    }

    protected function getCoins()
    {
        $coinsData = Redis::keys(config('database.redis.default.prefix') . 'coin:*');
        $coins = collect();

        foreach ($coinsData as $coinKey) {
            $coinId = str_replace('laravel_database_coin:', '', $coinKey);
            $coinRedisKey = 'coin:' . $coinId;

            $coinData = Redis::get($coinRedisKey);

            if ($coinData === null) {
                continue;
            }

            $coinDataArray = json_decode($coinData, true);
            $coinDataArray["id"] = $coinId;

            $coins->push($coinDataArray);
        }

        return $coins;
    }

    public function clearRedisData()
    {
        Redis::flushall();
        return response()->json(['message' => 'Silme işlemi tamamlandı.']);
    }

}
