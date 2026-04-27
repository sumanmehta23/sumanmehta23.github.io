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
            background-color: #198754;
            border-color: #198754;
            color: #fff;
        }

        .calendar-filter .btn-filter-default {
            background: #f2f3f5;
            color: #444a56;
        }

        .event-metric-label {
            color: #6b7280;
            font-size: 10px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin-bottom: 4px;
            display: block;
            text-align: start;
        }

        .event-metric-value {
            font-weight: 600;
            font-size: 13px;
            color: #111827;
            line-height: 1.4;
            min-height: 1.35em;
            display: block;
            overflow: visible;
            white-space: normal;
            word-break: break-word;
            text-align: start;
        }

        .event-metric-value--na {
            color: #6b7280;
            font-weight: 600;
        }

        .news-event-row {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 0.75rem 1rem;
        }

        .news-event-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 0 0 auto;
            min-width: 0;
        }

        .news-event-time {
            min-width: 2.75rem;
            color: #6b7280;
            font-weight: 600;
            font-size: 0.875rem;
            font-variant-numeric: tabular-nums;
        }

        .news-event-title {
            flex: 1 1 220px;
            min-width: 0;
        }

        /* Forecast + Previous: one sub-row, start-aligned */
        .news-event-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            column-gap: 1rem;
            row-gap: 0.25rem;
            align-items: start;
            justify-items: stretch;
            text-align: start;
            min-width: 0;
        }

        .news-metric-cell {
            min-width: 0;
            text-align: start !important;
        }

        .news-metric-cell .event-metric-label,
        .news-metric-cell .event-metric-value {
            text-align: start !important;
        }

        .news-metric-cell .event-metric-value {
            overflow: visible;
            white-space: normal;
            word-break: break-word;
        }

        .news-event-impact {
            flex: 0 0 auto;
            align-self: center;
        }

        /* Mobile: compact card — Forecast | Previous stay side-by-side in one row */
        @media (max-width: 767.98px) {
            .news-event-row {
                display: grid;
                grid-template-columns: 1fr auto;
                grid-template-areas:
                    "meta impact"
                    "title title"
                    "metrics metrics";
                gap: 0.5rem 0.75rem;
                align-items: center;
            }

            .news-event-meta {
                grid-area: meta;
            }

            .news-event-impact {
                grid-area: impact;
                justify-self: end;
                align-self: center;
            }

            .news-event-title {
                grid-area: title;
                flex: none;
            }

            .news-event-metrics {
                grid-area: metrics;
                margin-left: 0;
                width: 100%;
                column-gap: 0.65rem;
                row-gap: 0.35rem;
                border-top: 1px solid #eef0f4;
                padding-top: 0.65rem;
                margin-top: 0.15rem;
            }

            .news-metric-cell {
                min-width: 0;
                padding-right: 0;
                padding-left: 0;
            }

            .news-metric-cell + .news-metric-cell {
                padding-top: 0;
                border-top: none;
            }

            .news-metric-cell .event-metric-label {
                font-size: 9px;
                margin-bottom: 1px;
            }

            .news-metric-cell .event-metric-value {
                font-size: 12px;
                display: block;
                -webkit-line-clamp: unset;
                line-clamp: unset;
                -webkit-box-orient: unset;
                overflow: visible;
                white-space: normal;
                word-break: break-word;
            }

            .news-event-card {
                padding-left: 0.875rem !important;
                padding-right: 0.875rem !important;
                padding-top: 0.65rem !important;
                padding-bottom: 0.65rem !important;
            }

            .calendar-filter .btn {
                padding: 5px 10px;
                font-size: 11px;
                margin-right: 4px;
            }
        }

        /* Tablet + desktop: one row so Forecast/Previous line up across the list */
        @media (min-width: 768px) {
            .news-event-row {
                display: grid;
                grid-template-columns: auto minmax(0, 1fr) minmax(14rem, 32%) auto;
                grid-template-areas: "meta title metrics impact";
                gap: 0.5rem 0.85rem;
                align-items: start;
            }

            .news-event-meta {
                grid-area: meta;
                padding-top: 0.1rem;
            }

            .news-event-title {
                grid-area: title;
                min-width: 0;
            }

            .news-event-metrics {
                grid-area: metrics;
                margin-left: 0;
                width: 100%;
                max-width: none;
                border-top: none;
                padding-top: 0;
                margin-top: 0;
            }

            .news-event-impact {
                grid-area: impact;
                justify-self: end;
                align-self: center;
                padding-top: 0.1rem;
            }
        }

        /* Tablet only: page chrome */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .news-page-content {
                max-width: 100%;
                overflow-x: hidden;
            }

            .news-main-card .card-body {
                padding: 1rem 1.15rem !important;
            }

            .news-page-title {
                font-size: clamp(1.25rem, 2.2vw + 0.5rem, 1.5rem);
            }

            .news-page-subtitle {
                font-size: 0.9375rem;
                line-height: 1.45;
            }

            .news-filter-toolbar {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.65rem 1rem;
            }

            .news-filter-toolbar .calendar-filter {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.4rem;
            }

            .news-filter-toolbar .calendar-filter .btn {
                margin-right: 0;
                margin-bottom: 0;
                padding: 6px 12px;
                font-size: 12px;
            }

            .news-event-title a {
                font-size: 0.9375rem;
                line-height: 1.4;
                display: block;
            }

            .news-metric-cell .event-metric-label {
                font-size: 10px;
            }

            .news-metric-cell .event-metric-value {
                display: block;
                overflow: visible;
                -webkit-line-clamp: unset;
                line-clamp: unset;
                -webkit-box-orient: unset;
                white-space: normal;
                word-break: break-word;
                font-size: 12px;
            }

            .news-event-card {
                padding: 0.8rem 1rem !important;
            }

            .news-pagination {
                justify-content: center !important;
            }
        }

        @media (min-width: 992px) {
            .news-event-row {
                grid-template-columns: auto minmax(0, 1fr) minmax(15rem, 30%) auto;
            }

            .news-pagination {
                justify-content: flex-end !important;
            }
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
                    <div class="card news-main-card">
                        <div class="card-body">
                            <span class="badge bg-light-success text-success mb-3">This Week's Events</span>
                            <h2 class="mb-1 news-page-title">Economic Calendar</h2>
                            <p class="mb-4 text-muted news-page-subtitle">Track major economic events and data releases affecting global markets</p>

                            <div class="mb-3 d-flex align-items-center justify-content-between news-filter-toolbar">
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

                                    @php
                                        $fc = $item->forecast ?? 'N/A';
                                        $pv = $item->previous ?? 'N/A';
                                        $fcIsNa = strtoupper(trim((string) $fc)) === 'N/A';
                                        $pvIsNa = strtoupper(trim((string) $pv)) === 'N/A';
                                    @endphp
                                    <div class="news-event-card px-3 py-3 mb-2">
                                        <div class="news-event-row">
                                            <div class="news-event-meta">
                                                <span class="news-event-time">{{ $item->time_label ?? '-' }}</span>
                                                <span class="fw-semibold text-nowrap">
                                                    <span class="news-dot {{ $dotClass }}"></span>{{ $currency }}
                                                </span>
                                            </div>
                                            <div class="news-event-title">
                                                <a href="{{ $item->link }}" target="_blank" rel="noopener noreferrer"
                                                    class="text-body text-decoration-none fw-semibold d-block">
                                                    {{ html_entity_decode($item->title ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') }}
                                                </a>
                                            </div>
                                            <div class="news-event-metrics text-start">
                                                <div class="news-metric-cell">
                                                    <span class="event-metric-label">Forecast</span>
                                                    <span class="event-metric-value{{ $fcIsNa ? ' event-metric-value--na' : '' }}" title="{{ $fc }}">{{ $fc }}</span>
                                                </div>
                                                <div class="news-metric-cell">
                                                    <span class="event-metric-label">Previous</span>
                                                    <span class="event-metric-value{{ $pvIsNa ? ' event-metric-value--na' : '' }}" title="{{ $pv }}">{{ $pv }}</span>
                                                </div>
                                            </div>
                                            <div class="news-event-impact text-md-end">
                                                <span class="impact-pill {{ $impactClass }}">{{ ucfirst($impact) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="mt-4 mb-2 d-flex justify-content-end news-pagination">
                                    {{ $newsItems->links('pagination::bootstrap-5') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{-- </div> --}}
@endsection
