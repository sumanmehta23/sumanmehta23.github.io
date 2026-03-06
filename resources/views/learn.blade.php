@extends('layouts.crm.crm')

@section('content')
    <div class="pc-container learn-page">
        <div class="pc-content">
            <style>
                .learn-page .learn-video-card .card-body {
                    padding: 0.85rem;
                }

                .learn-page .learn-video-title {
                    min-height: 42px;
                    line-height: 1.3;
                }
            </style>

            <div class="pb-0 mb-3 page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title h2">
                                <h4 class="mb-0">Learn</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-lg-8">
                    <div class="card mb-0">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <h6 class="mb-1">Tutorial Sections</h6>
                                    <p class="mb-0 text-muted small">
                                        @if ($sections->count())
                                            Showing {{ $sections->firstItem() }}-{{ $sections->lastItem() }} of {{ $sections->total() }} sections
                                        @else
                                            No sections found
                                        @endif
                                    </p>
                                </div>
                                <span class="badge bg-light-primary text-primary">
                                    Page {{ $sections->currentPage() }} / {{ max($sections->lastPage(), 1) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card mb-0">
                        <div class="card-body py-3">
                            @php
                                $isFiltered = !empty($search) || ((int) $perPage !== 3);
                            @endphp
                            <form id="learnFilterForm" method="GET" action="{{ route('learn') }}" class="d-flex align-items-center gap-2 flex-wrap justify-content-lg-end">
                                <input
                                    type="text"
                                    id="learnSearchInput"
                                    name="search"
                                    value="{{ $search ?? '' }}"
                                    class="form-control form-control-sm"
                                    style="max-width: 180px;"
                                    placeholder="Search tutorials"
                                >
                                <label for="per_page" class="mb-0 text-muted small">Sections per page</label>
                                <select name="per_page" id="per_page" class="form-select form-select-sm w-auto">
                                    <option value="3" {{ $perPage == 3 ? 'selected' : '' }}>3</option>
                                    <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                                </select>
                                @if ($isFiltered)
                                    <a href="{{ route('learn') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @forelse ($sections as $section)
                <div class="mb-4">
                    <h5 class="mb-3">{{ $section->title }}</h5>

                    @if ($section->videos->isEmpty())
                        <div class="alert alert-warning py-2 mb-2">No active videos in this section.</div>
                    @else
                        <div class="row g-3">
                            @foreach ($section->videos as $video)
                                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                    <div class="card h-100 border learn-video-card">
                                        <div class="card-body">
                                            <h6 class="mb-3 learn-video-title">{{ $video->title }}</h6>
                                            <div class="ratio ratio-16x9">
                                                <iframe
                                                    src="https://fast.wistia.net/embed/iframe/{{ $video->wistia_id }}?seo=false&videoFoam=true"
                                                    allowtransparency="true"
                                                    frameborder="0"
                                                    scrolling="no"
                                                    class="w-100 h-100 rounded"
                                                    allowfullscreen
                                                ></iframe>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info mb-0">No learn content available yet.</div>
                    </div>
                </div>
            @endforelse

            @if ($sections->hasPages())
                <div class="mt-2">
                    {{ $sections->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
    <script>
        (function () {
            var form = document.getElementById('learnFilterForm');
            var searchInput = document.getElementById('learnSearchInput');
            var perPageInput = document.getElementById('per_page');
            var debounceTimer = null;

            if (!form || !searchInput || !perPageInput) {
                return;
            }

            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    form.submit();
                }, 350);
            });

            perPageInput.addEventListener('change', function () {
                form.submit();
            });
        })();
    </script>
@endsection

