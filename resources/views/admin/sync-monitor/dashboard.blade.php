@extends('layouts.app')

@section('title', 'Sync Monitor Dashboard')

@section('styles')
    <style>
        .health-score.excellent {
            background: linear-gradient(45deg, #28a745, #20c997);
        }

        .health-score.good {
            background: linear-gradient(45deg, #17a2b8, #20c997);
        }

        .health-score.fair {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
        }

        .health-score.poor {
            background: linear-gradient(45deg, #fd7e14, #dc3545);
        }

        .health-score.critical {
            background: linear-gradient(45deg, #dc3545, #6f42c1);
        }

        .metric-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #007bff;
            transition: transform 0.2s ease;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
        }

        .metric-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .progress-ring {
            width: 80px;
            height: 80px;
            position: relative;
        }

        .queue-status {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .queue-name {
            font-weight: 600;
            color: #495057;
        }

        .queue-count {
            background: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .account-item {
            padding: 12px;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .account-code {
            font-weight: 600;
            color: #495057;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-flagged {
            background: #dc3545;
            color: white;
        }

        .status-pending {
            background: #ffc107;
            color: #212529;
        }

        .status-retry {
            background: #17a2b8;
            color: white;
        }

        .auto-refresh-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            z-index: 1000;
        }

        .section-header {
            background: #f8f9fa;
            padding: 15px 20px;
            margin: -20px -20px 20px -20px;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="auto-refresh-indicator" id="refreshIndicator" style="display: none;">
            Auto-refreshing...
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0">Trade Sync Monitor Dashboard</h1>
                <p class="text-muted">Comprehensive monitoring of all sync operations</p>
            </div>
        </div>

        <!-- System Health Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="section-header">System Health</div>
                    <div class="text-center">
                        <div class="health-score {{ strtolower($syncData['system_health']['status']) }} d-inline-flex align-items-center justify-content-center"
                            style="width: 100px; height: 100px; border-radius: 50%; color: white; font-size: 1.5rem; font-weight: bold;">
                            {{ $syncData['system_health']['score'] }}%
                        </div>
                        <div class="mt-2">
                            <strong>{{ $syncData['system_health']['status'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="metric-card">
                    <div class="section-header">Quick Stats</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="metric-value">{{ $syncData['system_health']['total_queue_jobs'] }}</div>
                            <div class="metric-label">Total Queue Jobs</div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-value">{{ $syncData['system_health']['flagged_accounts'] }}</div>
                            <div class="metric-label">Flagged Accounts</div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-value">{{ $syncData['system_health']['stuck_accounts'] }}</div>
                            <div class="metric-label">Stuck Accounts</div>
                        </div>
                        <div class="col-md-3">
                            <div class="metric-value">{{ $syncData['cache_stats']['total_cache_markers'] }}</div>
                            <div class="metric-label">Active Syncs</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Priority Sync Stats -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="metric-card">
                    <div class="section-header">Priority Sync (Live Accounts)</div>
                    <div class="row">
                        <div class="col-6">
                            <div class="metric-value">{{ number_format($syncData['priority_sync']['total_accounts']) }}
                            </div>
                            <div class="metric-label">Total Accounts</div>
                        </div>
                        <div class="col-6">
                            <div class="metric-value">{{ $syncData['priority_sync']['sync_percentage'] }}%</div>
                            <div class="metric-label">Synced Today</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-4">
                            <div class="metric-value text-warning">{{ $syncData['priority_sync']['retry_accounts'] }}</div>
                            <div class="metric-label">Need Retry</div>
                        </div>
                        <div class="col-4">
                            <div class="metric-value text-info">{{ $syncData['priority_sync']['pending_accounts'] }}</div>
                            <div class="metric-label">Pending</div>
                        </div>
                        <div class="col-4">
                            <div class="metric-value text-primary">{{ $syncData['priority_sync']['queue_jobs'] }}</div>
                            <div class="metric-label">Queue Jobs</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="metric-card">
                    <div class="section-header">Demo Sync</div>
                    <div class="row">
                        <div class="col-6">
                            <div class="metric-value">{{ number_format($syncData['demo_sync']['total_accounts']) }}</div>
                            <div class="metric-label">Demo Accounts</div>
                        </div>
                        <div class="col-6">
                            <div class="metric-value">{{ $syncData['demo_sync']['sync_percentage'] }}%</div>
                            <div class="metric-label">Synced Today</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-4">
                            <div class="metric-value text-warning">{{ $syncData['demo_sync']['retry_accounts'] }}</div>
                            <div class="metric-label">Need Retry</div>
                        </div>
                        <div class="col-4">
                            <div class="metric-value text-info">{{ $syncData['demo_sync']['synced_today'] }}</div>
                            <div class="metric-label">Synced Today</div>
                        </div>
                        <div class="col-4">
                            <div class="metric-value text-primary">{{ $syncData['demo_sync']['queue_jobs'] }}</div>
                            <div class="metric-label">Queue Jobs</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Status -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="metric-card">
                    <div class="section-header">Queue Status</div>
                    <div class="row">
                        @foreach($syncData['queue_status'] as $queueName => $jobCount)
                            <div class="col-md-4 col-lg-2 mb-3">
                                <div class="queue-status">
                                    <div class="queue-name">{{ ucfirst(str_replace('_', ' ', $queueName)) }}</div>
                                    <div class="queue-count">{{ $jobCount }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Cache Stats -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="metric-card">
                    <div class="section-header">Cache Status</div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="metric-value text-success">
                                {{ $syncData['cache_stats']['live_accounts_in_progress'] }}</div>
                            <div class="metric-label">Live Accounts Syncing</div>
                        </div>
                        <div class="col-md-4">
                            <div class="metric-value text-info">{{ $syncData['cache_stats']['demo_accounts_in_progress'] }}
                            </div>
                            <div class="metric-label">Demo Accounts Syncing</div>
                        </div>
                        <div class="col-md-4">
                            <div class="metric-value text-primary">{{ $syncData['cache_stats']['total_cache_markers'] }}
                            </div>
                            <div class="metric-label">Total Active Markers</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Problematic Accounts -->
        <div class="row">
            <div class="col-md-6">
                <div class="metric-card">
                    <div class="section-header">
                        Flagged Accounts ({{ count($syncData['flagged_accounts']) }})
                        <small class="text-muted">- Recently flagged first</small>
                    </div>
                    @forelse($syncData['flagged_accounts'] as $account)
                        <div class="account-item">
                            <div>
                                <span class="account-code">{{ $account->code }}</span>
                                @if($account->demo)
                                    <span class="badge badge-secondary">DEMO</span>
                                @endif
                                <div class="small text-muted">{{ $account->sync_flag_reason }}</div>
                                <div class="small text-muted">Flagged: {{ $account->sync_flagged_at->diffForHumans() }}</div>
                            </div>
                            <div>
                                <span class="status-badge status-flagged">Flagged</span>
                                <button class="btn btn-sm btn-outline-success ml-2" onclick="unflagAccount({{ $account->id }})">
                                    Unflag
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            No flagged accounts
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="col-md-6">
                <div class="metric-card">
                    <div class="section-header">
                        Stuck Accounts ({{ count($syncData['stuck_accounts']) }})
                        <small class="text-muted">- Oldest stuck first</small>
                    </div>
                    @forelse($syncData['stuck_accounts'] as $account)
                        <div class="account-item">
                            <div>
                                <span class="account-code">{{ $account->code }}</span>
                                @if($account->demo)
                                    <span class="badge badge-secondary">DEMO</span>
                                @endif
                                <div class="small text-muted">Last attempt:
                                    {{ $account->last_sync_attempt_at->diffForHumans() }}</div>
                                @if($account->sync_error)
                                    <div class="small text-danger">{{ Str::limit($account->sync_error, 50) }}</div>
                                @endif
                            </div>
                            <div>
                                <span class="status-badge status-pending">Stuck</span>
                                <button class="btn btn-sm btn-outline-danger ml-2"
                                    onclick="clearAccountCache({{ $account->id }})">
                                    Clear Cache
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            No stuck accounts
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let autoRefreshInterval;

        $(document).ready(function () {
            // Start auto-refresh every 30 seconds
            startAutoRefresh();

            // Add manual refresh button
            $('.container-fluid').prepend(`
            <div class="row mb-3">
                <div class="col-12 text-right">
                    <button class="btn btn-outline-primary btn-sm" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i> Refresh Now
                    </button>
                    <button class="btn btn-outline-secondary btn-sm ml-2" onclick="toggleAutoRefresh()">
                        <i class="fas fa-clock"></i> <span id="autoRefreshText">Disable Auto-refresh</span>
                    </button>
                </div>
            </div>
        `);
        });

        function startAutoRefresh() {
            autoRefreshInterval = setInterval(refreshData, 30000); // 30 seconds
        }

        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        }

        function toggleAutoRefresh() {
            if (autoRefreshInterval) {
                stopAutoRefresh();
                $('#autoRefreshText').text('Enable Auto-refresh');
            } else {
                startAutoRefresh();
                $('#autoRefreshText').text('Disable Auto-refresh');
            }
        }

        function refreshData() {
            $('#refreshIndicator').show();

            $.ajax({
                url: '/admin/sync-monitor/data',
                method: 'GET',
                success: function (data) {
                    // Update the page content without full reload
                    updateDashboard(data);
                    $('#refreshIndicator').hide();
                },
                error: function () {
                    $('#refreshIndicator').hide();
                    console.error('Failed to refresh data');
                }
            });
        }

        function updateDashboard(syncData) {
            // Update system health
            $('.health-score').text(syncData.system_health.score + '%');
            $('.health-score').next().find('strong').text(syncData.system_health.status);

            // Update quick stats
            const quickStats = $('.metric-card .section-header:contains("Quick Stats")').parent().find('.metric-value');
            quickStats.eq(0).text(syncData.system_health.total_queue_jobs);
            quickStats.eq(1).text(syncData.system_health.flagged_accounts);
            quickStats.eq(2).text(syncData.system_health.stuck_accounts);
            quickStats.eq(3).text(syncData.cache_stats.total_cache_markers);

            // Update queue counts
            $('.queue-count').each(function (index, element) {
                const queueName = Object.keys(syncData.queue_status)[index];
                if (queueName) {
                    $(element).text(syncData.queue_status[queueName]);
                }
            });
        }

        function unflagAccount(accountId) {
            if (!confirm('Are you sure you want to unflag this account?')) {
                return;
            }

            $.ajax({
                url: `/admin/sync-monitor/accounts/${accountId}/unflag`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.message);
                        refreshData();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function () {
                    alert('Failed to unflag account');
                }
            });
        }

        function clearAccountCache(accountId) {
            if (!confirm('Are you sure you want to clear the cache for this account?')) {
                return;
            }

            $.ajax({
                url: `/admin/sync-monitor/accounts/${accountId}/clear-cache`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.message);
                        refreshData();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function () {
                    alert('Failed to clear account cache');
                }
            });
        }
    </script>
@endsection