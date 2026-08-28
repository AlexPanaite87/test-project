<?php

namespace App\Services;

use App\Models\Product;
use App\Models\VideoCandidate;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiVerifier
{
    /**
     * @throws ConnectionException
     */
    public function verifyCandidates(Product $product, Collection $candidates) : ?array
    {
        $apiKey = config('services.ai.key');

        if (!$apiKey || $candidates->isEmpty()) {
            return null;
        }

        $candidatesData = $candidates->map(function ($candidate) {
            return [
                'video_id' => $candidate->video_id,
                'title' => $candidate->title,
                'channel' => $candidate->channel,
                'description' => $candidate->description_snippet,
            ];
        })->toArray();

        $candidatesJson = json_encode($candidatesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $prompt = <<<EOT
        You are an AI assistant specialized in validating video game metadata.
        Your goal is to find the best official trailer/gameplay video for the product below.

        Our Product:
        - Name: {$product->name}
        - Category: {$product->category}

        List of YouTube candidates (Top 5):
        {$candidatesJson}

        Your task:
        1. Analyze each candidate.
        2. Choose a single "best match" that best represents the official trailer or gameplay presentation. Prioritize official channels, and titles containing 'Official Trailer', 'Launch Trailer', etc.
        3. If none of the candidates are a good match, set "verified" to false.

        Answer STRICTLY in valid JSON format with the following structure:
        {
            "verified": true/false,
            "selected_video_id": "the ID of the chosen video, or null if none match",
            "accuracy": integer between 0 and 100 representing confidence,
            "explanation": "A short explanation of your decision"
        }
        EOT;

        $maxRetries = 3;
        $response = null;

        for ($i = 0; $i < $maxRetries; $i++) {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=' . $apiKey, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                    'temperature' => 0.1
                ]
            ]);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                $text = str_replace(['```json', '```'], '', $text);
                $text = trim($text);

                return json_decode($text, true);
            }

            if ($response->status() === 503 || $response->status() === 429) {
                sleep(2);
                continue;
            }

            break;
        }

        if ($response && !$response->successful()) {
            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            throw new \RuntimeException('Gemini API failed with status: ' . $response->status());
        }

        return null;
    }
}
