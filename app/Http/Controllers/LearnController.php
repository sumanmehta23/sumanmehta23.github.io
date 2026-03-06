<?php

namespace App\Http\Controllers;

use App\Models\LearnSection;
use Illuminate\Http\Request;

class LearnController extends Controller
{
    public function index(Request $request)
    {
        $allowedPerPage = [3, 5, 10, 15];
        $perPage = (int) $request->input('per_page', 3);
        $perPage = in_array($perPage, $allowedPerPage, true) ? $perPage : 3;
        $search = trim((string) $request->input('search', ''));

        $sections = LearnSection::query()
            ->active()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'like', '%' . $search . '%')
                        ->orWhereHas('videos', function ($videoQuery) use ($search) {
                            $videoQuery->active()->where('title', 'like', '%' . $search . '%');
                        });
                });
            })
            ->with([
                'videos' => function ($query) {
                    $query->active()->orderBy('sort_order')->orderBy('id');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->appends($request->query());

        return view('learn', compact('sections', 'perPage', 'search'));
    }
}

