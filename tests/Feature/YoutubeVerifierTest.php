<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\VideoCandidate;
use App\Services\AiVerifier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SearchYoutubeAndVerifyJob;

class YoutubeVerifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_video_candidate_to_database()
    {
        $product = Product::create([
            'name' => 'Test Game',
            'category' => 'PC Digital',
        ]);

        VideoCandidate::create([
            'product_id' => $product->id,
            'video_id' => 'dQw4w9WgXcQ',
            'title' => 'Test Video',
            'channel' => 'Test Channel',
        ]);

        $this->assertDatabaseHas('video_candidates', [
            'video_id' => 'dQw4w9WgXcQ',
            'title' => 'Test Video'
        ]);
    }

    public function test_product_relationship_returns_latest_candidates()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'PC'
        ]);

        VideoCandidate::create([
            'product_id' => $product->id,
            'video_id' => '12345',
            'title' => 'Candidate 1'
        ]);

        $this->assertCount(1, $product->videoCandidates);
        $this->assertEquals('12345', $product->videoCandidates->first()->video_id);
    }

    public function test_manual_override_updates_product_status()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'PC'
        ]);

        $product->update([
            'youtube_url' => 'https://www.youtube.com/watch?v=abc',
            'youtube_video_id' => 'abc',
            'youtube_found_at' => now(),
            'ai_verified' => true,
            'ai_accuracy' => 100,
            'ai_explanation' => 'Manually verified by user',
        ]);

        $this->assertTrue((bool) $product->fresh()->ai_verified);
        $this->assertEquals('abc', $product->fresh()->youtube_video_id);
    }

    public function test_ai_verifier_parses_json_correctly()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => "```json\n{\n\"verified\": true,\n\"selected_video_id\": \"xyz123\",\n\"accuracy\": 95,\n\"explanation\": \"Looks good.\"\n}\n```"]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'PC'
        ]);

        $candidates = collect([
            (object) [
                'video_id' => 'xyz123',
                'title' => 'Title',
                'channel' => 'Channel',
                'description_snippet' => 'Desc'
            ]
        ]);

        $verifier = new AiVerifier();
        $result = $verifier->verifyCandidates($product, $candidates);

        $this->assertTrue($result['verified']);
        $this->assertEquals('xyz123', $result['selected_video_id']);
        $this->assertEquals(95, $result['accuracy']);
    }

    public function test_search_job_is_dispatched_to_queue()
    {
        Queue::fake();

        $product = Product::create([
            'name' => 'Test Product',
            'category' => 'PC'
        ]);

        SearchYoutubeAndVerifyJob::dispatch($product);

        Queue::assertPushed(SearchYoutubeAndVerifyJob::class, function ($job) use ($product) {
            return $job->product->id === $product->id;
        });
    }
}
