<?php

namespace App\Http\Controllers;

use App\Models\ForexNewsItem;
use Illuminate\Http\Request;

class ForexNewsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $impact = strtolower((string) $request->query('impact', 'all'));
        if (!in_array($impact, ['all', 'high', 'medium', 'low'], true)) {
            $impact = 'all';
        }

        $query = ForexNewsItem::query()->orderByDesc('published_at')->orderByDesc('id');

        if ($impact !== 'all') {
            $query->where('impact', $impact);
        }

        $newsItems = $query->paginate(20)->withQueryString();
        $hasStoredItems = ForexNewsItem::query()->exists();

        return view('forex-news', [
            'newsItems' => $newsItems,
            'impact' => $impact,
            'hasStoredItems' => $hasStoredItems,
        ]);
    }
}

