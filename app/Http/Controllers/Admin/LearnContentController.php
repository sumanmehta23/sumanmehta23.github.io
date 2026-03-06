<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearnSection;
use App\Models\LearnVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LearnContentController extends Controller
{
    public function index(Request $request): View
    {
        $allowedPerPage = [10, 25, 50];
        $sectionsPerPage = (int) $request->input('sections_per_page', 10);
        $videosPerPage = (int) $request->input('videos_per_page', 10);
        $sectionsPerPage = in_array($sectionsPerPage, $allowedPerPage, true) ? $sectionsPerPage : 10;
        $videosPerPage = in_array($videosPerPage, $allowedPerPage, true) ? $videosPerPage : 10;

        $filterSectionId = $request->input('video_section_id');

        $sections = LearnSection::query()
            ->withCount('videos')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($sectionsPerPage, ['*'], 'sections_page')
            ->appends($request->query());

        $videos = LearnVideo::query()
            ->with('section')
            ->when($filterSectionId, function ($query) use ($filterSectionId) {
                $query->where('learn_section_id', $filterSectionId);
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($videosPerPage, ['*'], 'videos_page')
            ->appends($request->query());

        $allSections = LearnSection::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.learn-content.index', compact(
            'sections',
            'videos',
            'allSections',
            'sectionsPerPage',
            'videosPerPage',
            'filterSectionId'
        ));
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('learn_sections', 'slug')],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $baseSlug = $validated['slug'] ?? Str::slug($validated['title']);
        $slug = $this->makeUniqueSectionSlug($baseSlug);

        LearnSection::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()->back()->with('success', 'Learn section created successfully.');
    }

    public function updateSection(Request $request, LearnSection $learnSection): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('learn_sections', 'slug')->ignore($learnSection->id)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $baseSlug = $validated['slug'] ?? Str::slug($validated['title']);
        $slug = $this->makeUniqueSectionSlug($baseSlug, $learnSection->id);

        $learnSection->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()->back()->with('success', 'Learn section updated successfully.');
    }

    public function destroySection(LearnSection $learnSection): RedirectResponse
    {
        $learnSection->delete();

        return redirect()->back()->with('success', 'Learn section deleted successfully.');
    }

    public function storeVideo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'learn_section_id' => ['required', 'exists:learn_sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'wistia_id' => ['required', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        LearnVideo::create([
            'learn_section_id' => $validated['learn_section_id'],
            'title' => $validated['title'],
            'wistia_id' => trim($validated['wistia_id']),
            'tags' => $this->parseTags($validated['tags'] ?? null),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()->back()->with('success', 'Learn video created successfully.');
    }

    public function updateVideo(Request $request, LearnVideo $learnVideo): RedirectResponse
    {
        $validated = $request->validate([
            'learn_section_id' => ['required', 'exists:learn_sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'wistia_id' => ['required', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $learnVideo->update([
            'learn_section_id' => $validated['learn_section_id'],
            'title' => $validated['title'],
            'wistia_id' => trim($validated['wistia_id']),
            'tags' => $this->parseTags($validated['tags'] ?? null),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()->back()->with('success', 'Learn video updated successfully.');
    }

    public function destroyVideo(LearnVideo $learnVideo): RedirectResponse
    {
        $learnVideo->delete();

        return redirect()->back()->with('success', 'Learn video deleted successfully.');
    }

    private function makeUniqueSectionSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = Str::of($baseSlug)->slug('-')->toString();
        $slug = $slug !== '' ? $slug : 'learn-section';

        $original = $slug;
        $counter = 1;

        while (
            LearnSection::query()
                ->when($ignoreId, function ($query) use ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                })
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function parseTags(?string $tags): ?array
    {
        if ($tags === null || trim($tags) === '') {
            return null;
        }

        $parsed = collect(explode(',', $tags))
            ->map(function ($item) {
                return trim($item);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        return empty($parsed) ? null : $parsed;
    }
}

