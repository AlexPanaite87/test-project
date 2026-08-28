<?php

namespace App\Http\Controllers;

use App\Models\VideoCandidate;
use App\Services\AiVerifier;
use App\Services\YouTubeClient;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Jobs\SearchYoutubeAndVerifyJob;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;

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

    public function searchYoutube($id)
    {
        $product = Product::query()->findOrFail($id);

        $executed = RateLimiter::attempt(
            'search-youtube:' . request()->ip(),
            5,
            function () use ($product) {
                Cache::put('product_processing_' . $product->id, true, now()->addMinutes(5));
                SearchYoutubeAndVerifyJob::dispatch($product);
            },
            60
        );

        if (!$executed) {
            return redirect()->back()->with('error', 'Too many requests. Please try again later.');
        }

        return redirect()->back()->with('status', 'pending');
    }

    public function manualOverride(Product $product, $video)
    {
        $product->update([
            'youtube_url' => 'https://www.youtube.com/watch?v=' . $video,
            'youtube_video_id' => $video,
            'youtube_found_at' => now(),
            'ai_verified' => true,
            'ai_accuracy' => 100,
            'ai_explanation' => 'Manually verified by user',
        ]);

        return redirect()->back();
    }
}
