<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List: YouTube Verifier</title>

    <script src="https://cdn.tailwindcss.com"></script>
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

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Apply filter</button>
        <a href="{{ route('products.index') }}" class="text-gray-500 hover:underline">Reset</a>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
            <tr class="bg-gray-200 text-gray-700">
                <th class="p-3 border-b">ID</th>
                <th class="p-3 border-b">Name</th>
                <th class="p-3 border-b">Category</th>
                <th class="p-3 border-b">YouTube URL</th>
                <th class="p-3 border-b">AI Verdict</th>
                <th class="p-3 border-b">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($products as $product)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3">{{ $product->id }}</td>
                    <td class="p-3 font-semibold">{{ $product->name }}</td>
                    <td class="p-3 text-sm text-gray-600">{{ $product->category }}</td>
                    <td class="p-3 text-sm">
                        @if($product->youtube_url)
                            <a href="{{ $product->youtube_url }}" target="_blank" class="text-blue-500 underline">Watch video</a>
                        @else
                            <span class="text-red-500">Video is missing</span>
                        @endif
                    </td>
                    <td class="p-3 text-sm">
                        @if($product->ai_verified)
                            <span class="text-green-600 font-bold">Confirmed ({{ $product->ai_accuracy }}%)</span>
                        @else
                            <span class="text-gray-400">Unknown</span>
                        @endif
                    </td>
                    <td class="p-3">
                        @if(!$product->youtube_url)
                            <form action="{{ route('products.search-youtube', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                    Search Video
                                </button>
                            </form>
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

</body>
</html>
