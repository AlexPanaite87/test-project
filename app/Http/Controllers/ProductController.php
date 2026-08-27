<?php

namespace App\Http\Controllers;

use App\Models\VideoCandidate;
use App\Services\AiVerifier;
use App\Services\YouTubeClient;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        if ($request->has('no_video') && $request->no_video == '1') {
            $query->whereNull('youtube_url');
        }
        if($request->has('search') && $request->search != ''){
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(15);

        return view('products.index', compact('products'));
    }

    public function searchYoutube($id, YouTubeClient $youtubeClient, AiVerifier $aiVerifier)
    {
        $product = Product::findOrFail($id);
        $videos = $youtubeClient->searchVideo($product->name);

        if ($videos && is_array($videos)) {
            $candidatesList = collect();

            foreach ($videos as $videoData) {
                if (!isset($videoData['id']['videoId'])) {
                    continue;
                }

                $videoId = $videoData['id']['videoId'];

                $candidate = VideoCandidate::create([
                    'product_id' => $product->id,
                    'video_id' => $videoId,
                    'title' => $videoData['snippet']['title'] ?? null,
                    'channel' => $videoData['snippet']['channelTitle'] ?? null,
                    'published_at' => isset($videoData['snippet']['publishedAt'])
                        ? date('Y-m-d H:i:s', strtotime($videoData['snippet']['publishedAt']))
                        : null,
                    'description_snippet' => $videoData['snippet']['description'] ?? null,
                    'raw_payload' => json_encode($videoData),
                ]);

                $candidatesList->push($candidate);
            }

            if ($candidatesList->isNotEmpty()) {
                $aiResult = $aiVerifier->verifyCandidates($product, $candidatesList);

                $isMatch = $aiResult['verified'] ?? false;
                $selectedId = $aiResult['selected_video_id'] ?? null;

                $product->update([
                    'youtube_url' => ($isMatch && $selectedId) ? 'https://www.youtube.com/watch?v=' . $selectedId : null,
                    'youtube_video_id' => ($isMatch && $selectedId) ? $selectedId : null,
                    'youtube_found_at' => now(),
                    'ai_verified' => $isMatch,
                    'ai_accuracy' => $aiResult['accuracy'] ?? 0,
                    'ai_explanation' => $aiResult['explanation'] ?? 'Error during AI vaildations',
                ]);
            }
        }

        return redirect()->back();
    }
}
