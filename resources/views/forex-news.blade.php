@extends('layouts.crm.crm')

@section('styles')
    <style>
        .news-event-card {
            border: 1px solid #ebecef;
            border-radius: 10px;
            background-color: #fff;
            transition: all 0.15s ease;
        }

        .news-event-card:hover {
            box-shadow: 0 6px 20px rgba(18, 22, 33, 0.06);
            border-color: #e2e5ec;
        }

        .news-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 8px;
        }

        .news-dot-high {
            background-color: #ff3b30;
        }

        .news-dot-medium {
            background-color: #f5b301;
        }

        .news-dot-low {
            background-color: #8c95a8;
        }

        .impact-pill {
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            line-height: 1;
            display: inline-block;
        }

        .impact-pill-high {
            background: #fff0f0;
            color: #cf2c2c;
        }

        .impact-pill-medium {
            background: #fff8e6;
            color: #cc9600;
        }

        .impact-pill-low {
            background: #f3f4f7;
            color: #6b7280;
        }

        .calendar-filter .btn {
            border-radius: 8px !important;
            margin-right: 6px;
            font-size: 12px;
            padding: 6px 12px;
            border: 1px solid transparent;
        }

        .calendar-filter .btn-filter-active {
            background-color: #ff1f32;
            border-color: #ff1f32;
            color: #fff;
        }

        .calendar-filter .btn-filter-default {
            background: #f2f3f5;
            color: #444a56;
        }

        .event-metric-label {
            color: #9aa1b2;
            font-size: 10px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin-bottom: 2px;
            display: block;
        }

        .event-metric-value {
            font-weight: 600;
            font-size: 13px;
            color: #111827;
            line-height: 1.2;
        }

        .news-page-content {
            padding-bottom: 36px;
        }
    </style>
@endsection

@section('content')
    <div class="pc-container">
        <div class="pc-content news-page-content">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <span class="badge bg-light-danger text-danger mb-3">This Week's Events</span>
                            <h2 class="mb-1">Economic Calendar</h2>
                            <p class="mb-4 text-muted">Track major economic events and data releases affecting global markets</p>

                            <div class="mb-3 d-flex align-items-center justify-content-between">
                                <div class="calendar-filter d-flex align-items-center flex-wrap">
                                    <a href="{{ route('forex-news.index', ['impact' => 'all']) }}"
                                        class="btn btn-sm {{ $impact === 'all' ? 'btn-filter-active' : 'btn-filter-default' }}">All</a>
                                    <a href="{{ route('forex-news.index', ['impact' => 'high']) }}"
                                        class="btn btn-sm {{ $impact === 'high' ? 'btn-filter-active' : 'btn-filter-default' }}">High</a>
                                    <a href="{{ route('forex-news.index', ['impact' => 'medium']) }}"
                                        class="btn btn-sm {{ $impact === 'medium' ? 'btn-filter-active' : 'btn-filter-default' }}">Medium</a>
                                    <a href="{{ route('forex-news.index', ['impact' => 'low']) }}"
                                        class="btn btn-sm {{ $impact === 'low' ? 'btn-filter-active' : 'btn-filter-default' }}">Low</a>
                                </div>
                                <small class="text-muted">{{ $newsItems->total() }} events</small>
                            </div>

                            @if ($newsItems->isEmpty())
                                <div class="py-5 text-center">
                                    <img src="/assets/images/empty.png" class="mb-3" style="max-width: 90px;" alt="No feed data">
                                    @if ($hasStoredItems)
                                        <h6 class="mb-1">No events found for this filter</h6>
                                        <p class="mb-0 text-muted">Try a different impact filter to view available feed items.</p>
                                    @else
                                        <h6 class="mb-1">Feed unavailable right now</h6>
                                        <p class="mb-0 text-muted">No stored events are available yet. Please try again later.</p>
                                    @endif
                                </div>
                            @else
                                @php
                                    $lastDate = null;
                                @endphp

                                @foreach ($newsItems as $item)
                                    @if ($item->date_label !== $lastDate)
                                        <div class="mt-3 mb-2 text-muted fw-semibold">{{ $item->date_label ?? '-' }}</div>
                                        @php
                                            $lastDate = $item->date_label;
                                        @endphp
                                    @endif

                                    @php
                                        $impact = strtolower($item->impact ?? 'low');
                                        $dotClass = $impact === 'high' ? 'news-dot-high' : ($impact === 'medium' ? 'news-dot-medium' : 'news-dot-low');
                                        $impactClass = $impact === 'high' ? 'impact-pill-high' : ($impact === 'medium' ? 'impact-pill-medium' : 'impact-pill-low');
                                        $currency = strtoupper($item->currency ?: 'FX');
                                    @endphp

                                    <div class="news-event-card px-3 py-3 mb-2">
                                        <div class="row align-items-center g-2">
                                            <div class="col-md-2 col-lg-1 text-muted fw-semibold">
                                                {{ $item->time_label ?? '-' }}
                                            </div>
                                            <div class="col-md-2 col-lg-1 fw-semibold">
                                                <span class="news-dot {{ $dotClass }}"></span>{{ $currency }}
                                            </div>
                                            <div class="col-md-8 col-lg-7">
                                                <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer"
                                                    class="text-body text-decoration-none fw-semibold">
                                                    {{ $item->title }}
                                                </a>
                                            </div>
                                            <div class="col-md-6 col-lg-1 text-lg-end">
                                                <span class="event-metric-label">Forecast</span>
                                                <span class="event-metric-value">{{ $item->forecast ?? 'N/A' }}</span>
                                            </div>
                                            <div class="col-md-4 col-lg-1 text-lg-end">
                                                <span class="event-metric-label">Previous</span>
                                                <span class="event-metric-value">{{ $item->previous ?? 'N/A' }}</span>
                                            </div>
                                            <div class="col-md-2 col-lg-1 text-lg-end">
                                                <span class="impact-pill {{ $impactClass }}">{{ ucfirst($impact) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="mt-4 mb-2 d-flex justify-content-end">
                                    {{ $newsItems->links('pagination::bootstrap-5') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

