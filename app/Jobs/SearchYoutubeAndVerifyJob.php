<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\VideoCandidate;
use App\Services\AiVerifier;
use App\Services\YouTubeClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SearchYoutubeAndVerifyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180;

    public function __construct(
        public Product $product
    ) {}

    public function handle(YouTubeClient $youtubeClient, AiVerifier $aiVerifier): void
    {
        try {
            $cacheKey = 'youtube_search_' . $this->product->id;

            $videos = Cache::remember($cacheKey, 60 * 24, function () use ($youtubeClient) {
                return $youtubeClient->searchVideo($this->product->name);
            });

            if ($videos && is_array($videos)) {
                $candidatesList = collect();

                foreach ($videos as $videoData) {
                    if (!isset($videoData['id']['videoId'])) {
                        continue;
                    }

                    $candidate = VideoCandidate::updateOrCreate(
                        [
                            'product_id' => $this->product->id,
                            'video_id' => $videoData['id']['videoId'],
                        ],
                        [
                            'title' => $videoData['snippet']['title'] ?? null,
                            'channel' => $videoData['snippet']['channelTitle'] ?? null,
                            'published_at' => isset($videoData['snippet']['publishedAt'])
                                ? date('Y-m-d H:i:s', strtotime($videoData['snippet']['publishedAt']))
                                : null,
                            'description_snippet' => $videoData['snippet']['description'] ?? null,
                            'raw_payload' => json_encode($videoData),
                        ]
                    );

                    $candidatesList->push($candidate);
                }

                if ($candidatesList->isNotEmpty()) {
                    Log::info('Starting AI verification for product: ' . $this->product->name);

                    $aiResult = $aiVerifier->verifyCandidates($this->product, $candidatesList);

                    Log::info('Raw AI response:', (array) $aiResult);

                    $isMatch = $aiResult['verified'] ?? false;
                    $selectedId = $aiResult['selected_video_id'] ?? null;

                    $this->product->update([
                        'youtube_url' => ($isMatch && $selectedId) ? 'https://www.youtube.com/watch?v=' . $selectedId : null,
                        'youtube_video_id' => ($isMatch && $selectedId) ? $selectedId : null,
                        'youtube_found_at' => now(),
                        'ai_verified' => $isMatch,
                        'ai_accuracy' => $aiResult['accuracy'] ?? 0,
                        'ai_explanation' => $aiResult['explanation'] ?? 'Error during AI validations',
                    ]);

                    Log::info('Database update completed for product ID: ' . $this->product->id);
                } else {
                    Log::warning('No YouTube videos found for product: ' . $this->product->name);
                }
            }
        } finally {
            Cache::forget('product_processing_' . $this->product->id);
        }
    }
}
