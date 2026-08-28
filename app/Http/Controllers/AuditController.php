<?php

namespace App\Http\Controllers;

use App\Models\Product;

class AuditController extends Controller
{
    public function index()
    {
        $audits = Product::whereNotNull('ai_explanation')
            ->with('videoCandidates')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('audit.index', compact('audits'));
    }
}
