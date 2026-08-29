@php use Illuminate\Support\Facades\Cache; @endphp
    <!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List: YouTube Verifier</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">Product List</h1>

    <form method="GET" action="{{ route('products.index') }}" class="mb-6 flex gap-4 items-center">
        <input type="text" name="search" placeholder="Search by name" value="{{ request('search') }}"
               class="border border-gray-300 rounded px-4 py-2 w-1/3">

        <label class="flex items-center gap-2">
            <input type="checkbox" name="no_video" value="1" {{ request('no_video') ? 'checked' : '' }}>
            Show no videos only
        </label>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 transition-colors">
            Apply filter
        </button>

        <a href="{{ route('products.index') }}" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700 transition-colors">
            Reset
        </a>

        <a href="{{ route('audit.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 transition-colors">
            View Audit Log
        </a>    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-100">
            <tr>
                <th class="p-3 border-b font-semibold text-left">ID</th>
                <th class="p-3 border-b font-semibold text-left">Name</th>
                <th class="p-3 border-b font-semibold text-center">Category</th>
                <th class="p-3 border-b font-semibold text-center">YouTube URL</th>
                <th class="p-3 border-b font-semibold text-center">AI Verdict</th>
                <th class="p-3 border-b font-semibold text-center">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($products as $product)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3">{{ $product->id }}</td>
                    <td class="p-3 font-semibold">{{ $product->name }}</td>
                    <td class="p-3 text-sm text-gray-600 text-center">{{ $product->category }}</td>
                    <td class="p-3 text-sm text-center">
                        @if($product->youtube_url)
                            <a href="{{ $product->youtube_url }}" target="_blank" class="text-blue-500 underline">Watch
                                video</a>
                        @else
                            <span class="text-red-500">Video is missing</span>
                        @endif
                    </td>
                    <td class="p-3 text-sm text-center">
                        @if($product->ai_verified)
                            <span class="text-green-600 font-bold">Confirmed ({{ $product->ai_accuracy }}%)</span>
                        @else
                            <span class="text-gray-400">Unknown</span>
                        @endif
                    </td>
                    <td class="p-3 flex flex-col items-center">
                        @if(Cache::has('product_processing_' . $product->id))
                            <span class="js-pending-badge inline-block px-3 py-1 mb-2 text-sm font-semibold text-green-700 bg-green-100 rounded">
                            Pending...
                        </span>
                        @elseif(!$product->youtube_url)
                            <form action="{{ route('products.search-youtube', $product->id) }}" method="POST"
                                  class="mb-2 w-full flex justify-center">
                                @csrf
                                <button type="submit"
                                        class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                    Search Video
                                </button>
                            </form>
                        @endif

                        @if($product->ai_explanation)
                            <div
                                class="mt-3 bg-slate-50 border border-slate-200 rounded-lg p-4 shadow-sm w-full min-w-[400px] text-left">
                                <h4 class="text-sm font-bold text-slate-700 mb-3 border-b border-slate-200 pb-2">
                                    AI Verification Details
                                </h4>

                                <div class="mb-3">
                                <span
                                    class="block text-slate-500 text-[10px] uppercase tracking-wider font-semibold">Status</span>
                                    <span
                                        class="text-sm font-medium {{ $product->ai_verified ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $product->ai_verified ? 'Match Found' : 'No Match' }}
                                    <span class="text-slate-600 font-normal">(Accuracy: {{ $product->ai_accuracy }}%)</span>
                                    </span>
                                </div>

                                <div class="mb-4">
                                <span
                                    class="block text-slate-500 text-[10px] uppercase tracking-wider font-semibold">Explanation</span>
                                    <p class="text-sm text-slate-700 mt-1 leading-relaxed">
                                        {{ $product->ai_explanation }}
                                    </p>
                                </div>

                                <div>
                                <span
                                    class="block text-slate-500 text-[10px] uppercase tracking-wider font-semibold mb-2">Analyzed Candidates</span>
                                    <ul class="divide-y divide-slate-200 border border-slate-200 rounded-md bg-white">
                                        @foreach($product->videoCandidates as $candidate)
                                            <li class="p-2.5 flex flex-col lg:flex-row lg:items-center justify-between gap-3 hover:bg-slate-50 transition-colors">
                                                <div class="truncate max-w-xs xl:max-w-md">
                                                    <a href="https://youtube.com/watch?v={{ $candidate->video_id }}"
                                                       target="_blank"
                                                       class="text-blue-600 hover:text-blue-800 hover:underline text-sm font-medium block truncate">
                                                        {{ $candidate->title }}
                                                    </a>
                                                    <span
                                                        class="text-xs text-slate-500">by {{ $candidate->channel }}</span>
                                                </div>

                                                <form
                                                    action="{{ route('products.override', ['product' => $product->id, 'video' => $candidate->video_id]) }}"
                                                    method="POST" class="shrink-0">
                                                    @csrf
                                                    <button type="submit"
                                                            class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700 transition-colors">
                                                        Override
                                                    </button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $products->appends(request()->query())->links() }}
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let scrollpos = sessionStorage.getItem('scrollpos');
        if (scrollpos) {
            window.scrollTo(0, scrollpos);
            sessionStorage.removeItem('scrollpos');
        }

        const pendingItems = document.querySelectorAll('.js-pending-badge');
        if (pendingItems.length > 0) {
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        }
    });

    window.addEventListener("beforeunload", function () {
        sessionStorage.setItem('scrollpos', window.scrollY);
    });
</script>
</body>
</html>
