<?php

namespace App\Http\Controllers;

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

    public function searchYoutube($id, YouTubeClient $youtubeClient)
    {
        $product = Product::findOrFail($id);
        $videoData = $youtubeClient->searchVideo($product->name);

        if ($videoData) {
            $videoId = $videoData['id']['videoId'];
            $youtubeUrl = 'https://www.youtube.com/watch?v=' . $videoId;

            $product->update([
                'youtube_url' => $youtubeUrl,
                'youtube_video_id' => $videoId,
                'youtube_found_at' => now(),
            ]);

            VideoCandidate::create([
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

            return redirect()->back();
        }

        return redirect()->back();
    }
}
