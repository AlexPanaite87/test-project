<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class YouTubeClient
{
    public function searchVideo(string $productName): ?array
    {
        $apiKey = config('services.youtube.key');
        $cacheKey = 'youtube_search_top5_' . md5($productName);
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($apiKey, $productName) {
            $keywords = ['trailer', 'gameplay', 'official trailer'];
            $randomKeyword = $keywords[array_rand($keywords)];
            $response = Http::timeout(5)
                ->retry(2, 100)
                ->get('https://www.googleapis.com/youtube/v3/search', [
                    'part' => 'snippet',
                    'q' => "{$productName} {$randomKeyword}",
                    'type' => 'video',
                    'maxResults' => 5,
                    'key' => $apiKey,
                ]);

            if ($response->successful() && !empty($response->json('items'))) {
                return $response->json('items');
            }

            return null;
        });
    }
}
