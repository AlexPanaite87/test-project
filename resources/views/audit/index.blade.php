<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Audit Log</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">AI Verification Audit Log</h1>
        <a href="{{ route('products.index') }}" class="text-blue-600 hover:underline font-medium">&larr; Back to Products</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
            <tr class="bg-slate-100 text-slate-700 text-sm uppercase tracking-wider">
                <th class="p-3 border-b font-semibold">Date</th>
                <th class="p-3 border-b font-semibold">Product</th>
                <th class="p-3 border-b font-semibold">Verdict & Score</th>
                <th class="p-3 border-b font-semibold">AI Explanation</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
            @forelse($audits as $audit)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-3 text-sm text-slate-500 whitespace-nowrap">
                        {{ $audit->updated_at->format('Y-m-d H:i') }}
                    </td>
                    <td class="p-3">
                        <span class="font-semibold text-slate-800 block">{{ $audit->name }}</span>
                        <span class="text-xs text-slate-500">{{ $audit->category }}</span>
                    </td>
                    <td class="p-3">
                        @if($audit->ai_verified)
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium bg-green-500 text-white">
                                Match ({{ $audit->ai_accuracy }}%)
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium bg-red-500 text-white">
                                No Match ({{ $audit->ai_accuracy }}%)
                            </span>
                        @endif
                    </td>
                    <td class="p-3 text-sm text-slate-700 max-w-md">
                        <p class="truncate hover:whitespace-normal hover:break-words cursor-pointer" title="{{ $audit->ai_explanation }}">
                            {{ $audit->ai_explanation }}
                        </p>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-6 text-center text-slate-500">No AI audits found yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $audits->links() }}
    </div>
</div>

</body>
</html>
