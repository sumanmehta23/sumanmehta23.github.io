@extends('layouts.app')

@section('title', 'Account Details - ' . $account->code)

@section('styles')
    <style>
        .account-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .account-code {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .account-type {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .info-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #007bff;
        }

        .info-card.sync-info {
            border-left-color: #28a745;
        }

        .info-card.cache-info {
            border-left-color: #17a2b8;
        }

        .info-card.trade-info {
            border-left-color: #ffc107;
        }

        .info-card.api-info {
            border-left-color: #dc3545;
        }

        .card-header {
            background: #f8f9fa;
            padding: 15px 25px;
            margin: -25px -25px 20px -25px;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-flagged {
            background: #f8d7da;
            color: #721c24;
        }

        .status-retry {
            background: #d1ecf1;
            color: #0c5460;
        }

        .metric-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .metric-row:last-child {
            border-bottom: none;
        }

        .metric-label {
            font-weight: 600;
            color: #6c757d;
        }

        .metric-value {
            color: #495057;
            font-weight: 500;
        }

        .api-button {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 5px;
        }

        .api-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
        }

        .api-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .api-result {
            margin-top: 15px;
            padding: 15px;
            border-radius: 6px;
            display: none;
        }

        .api-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .api-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .trade-item {
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .trade-id {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }

        .trade-details {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .profit-positive {
            color: #28a745;
            font-weight: 600;
        }

        .profit-negative {
            color: #dc3545;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-admin {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-admin:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .data-table th,
        .data-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .timestamp {
            color: #6c757d;
            font-size: 0.85rem;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Account Header -->
        <div class="account-header">
            <div class="row">
                <div class="col-md-8">
                    <div class="account-code">{{ $account->code ?? 'No MT5 Code' }}</div>
                    <div class="account-type">
                        @if($account->demo)
                            <i class="fas fa-play-circle"></i> Demo Account
                        @else
                            <i class="fas fa-coins"></i> Live Account
                        @endif
                        • ID: {{ $account->id }}
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <div style="margin-top: 20px;">
                        <span class="status-badge status-{{ $accountData['sync_info']['sync_status'] ?? 'unknown' }}">
                            {{ ucfirst($accountData['sync_info']['sync_status'] ?? 'Unknown') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sync Information -->
            <div class="col-md-6">
                <div class="info-card sync-info">
                    <div class="card-header">
                        <span><i class="fas fa-sync-alt"></i> Sync Information</span>
                        @if($accountData['sync_info']['sync_status'] === 'flagged')
                            <button class="btn btn-sm btn-outline-success" onclick="unflagAccount()">
                                Unflag Account
                            </button>
                        @endif
                    </div>

                    <div class="metric-row">
                        <span class="metric-label">Sync Status</span>
                        <span class="metric-value">
                            <span class="status-badge status-{{ $accountData['sync_info']['sync_status'] ?? 'unknown' }}">
                                {{ ucfirst($accountData['sync_info']['sync_status'] ?? 'Unknown') }}
                            </span>
                        </span>
                    </div>

                    <div class="metric-row">
                        <span class="metric-label">Last Sync Attempt</span>
                        <span class="metric-value">
                            {{ $accountData['sync_info']['last_sync_attempt'] ?
        $accountData['sync_info']['last_sync_attempt']->diffForHumans() : 'Never' }}
                        </span>
                    </div>

                    <div class="metric-row">
                        <span class="metric-label">Last Balance Sync</span>
                        <span class="metric-value">
                            {{ $accountData['sync_info']['last_balance_sync'] ?
        $accountData['sync_info']['last_balance_sync']->diffForHumans() : 'Never' }}
                        </span>
                    </div>

                    @if($accountData['sync_info']['sync_error'])
                        <div class="metric-row">
                            <span class="metric-label">Sync Error</span>
                            <span class="metric-value text-danger">
                                {{ Str::limit($accountData['sync_info']['sync_error'], 80) }}
                            </span>
                        </div>
                    @endif

                    @if($accountData['sync_info']['stuck_count'] > 0)
                        <div class="metric-row">
                            <span class="metric-label">Stuck Count</span>
                            <span class="metric-value text-warning">
                                {{ $accountData['sync_info']['stuck_count'] }}
                            </span>
                        </div>
                    @endif

                    @if($accountData['sync_info']['flagged_at'])
                        <div class="metric-row">
                            <span class="metric-label">Flagged At</span>
                            <span class="metric-value text-danger">
                                {{ $accountData['sync_info']['flagged_at']->diffForHumans() }}
                            </span>
                        </div>
                    @endif

                    @if($accountData['sync_info']['flag_reason'])
                        <div class="metric-row">
                            <span class="metric-label">Flag Reason</span>
                            <span class="metric-value text-danger">
                                {{ $accountData['sync_info']['flag_reason'] }}
                            </span>
                        </div>
                    @endif

                    <div class="action-buttons">
                        @if($accountData['cache_status']['live_sync_in_progress'] || $accountData['cache_status']['demo_sync_in_progress'])
                            <button class="btn-admin btn-warning" onclick="clearAccountCache()">
                                Clear Sync Cache
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cache Status -->
            <div class="col-md-6">
                <div class="info-card cache-info">
                    <div class="card-header">
                        <span><i class="fas fa-database"></i> Cache Status</span>
                    </div>

                    <div class="metric-row">
                        <span class="metric-label">Live Sync In Progress</span>
                        <span class="metric-value">
                            @if($accountData['cache_status']['live_sync_in_progress'])
                                <span class="text-warning"><i class="fas fa-clock"></i> Yes</span>
                            @else
                                <span class="text-success"><i class="fas fa-check"></i> No</span>
                            @endif
                        </span>
                    </div>

                    <div class="metric-row">
                        <span class="metric-label">Demo Sync In Progress</span>
                        <span class="metric-value">
                            @if($accountData['cache_status']['demo_sync_in_progress'])
                                <span class="text-warning"><i class="fas fa-clock"></i> Yes</span>
                            @else
                                <span class="text-success"><i class="fas fa-check"></i> No</span>
                            @endif
                        </span>
                    </div>

                    @if($accountData['cache_status']['cache_expiry'])
                        <div class="metric-row">
                            <span class="metric-label">Cache Expires</span>
                            <span class="metric-value">{{ $accountData['cache_status']['cache_expiry'] }}</span>
                        </div>
                    @endif

                    <div class="metric-row">
                        <span class="metric-label">Balance Activity</span>
                        <span class="metric-value">
                            @if($accountData['sync_info']['has_balance_activity'])
                                <span class="text-success"><i class="fas fa-chart-line"></i> Active</span>
                            @else
                                <span class="text-muted"><i class="fas fa-minus-circle"></i> Inactive</span>
                            @endif
                        </span>
                    </div>

                    @if($accountData['sync_info']['last_balance_changed'])
                        <div class="metric-row">
                            <span class="metric-label">Last Balance Change</span>
                            <span class="metric-value">
                                {{ $accountData['sync_info']['last_balance_changed']->diffForHumans() }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Database Trade Statistics -->
            <div class="col-md-6">
                <div class="info-card trade-info">
                    <div class="card-header">
                        <span><i class="fas fa-chart-bar"></i> Database Trade Statistics</span>
                    </div>

                    <div class="metric-row">
                        <span class="metric-label">Total Trades</span>
                        <span class="metric-value">{{ number_format($accountData['trade_stats']['total_trades']) }}</span>
                    </div>

                    <div class="metric-row">
                        <span class="metric-label">Trades (Last 30 Days)</span>
                        <span
                            class="metric-value">{{ number_format($accountData['trade_stats']['trades_last_30_days']) }}</span>
                    </div>

                    <div class="metric-row">
                        <span class="metric-label">Profit (Last 30 Days)</span>
                        <span class="metric-value">
                            <span
                                class="{{ $accountData['trade_stats']['profit_last_30_days'] >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                ${{ number_format($accountData['trade_stats']['profit_last_30_days'], 2) }}
                            </span>
                        </span>
                    </div>

                    <div class="metric-row">
                        <span class="metric-label">Volume (Last 30 Days)</span>
                        <span
                            class="metric-value">{{ number_format($accountData['trade_stats']['volume_last_30_days'], 2) }}</span>
                    </div>

                    @if($accountData['trade_stats']['last_trade_date'])
                        <div class="metric-row">
                            <span class="metric-label">Last Trade</span>
                            <span class="metric-value">
                                {{ Carbon\Carbon::parse($accountData['trade_stats']['last_trade_date'])->diffForHumans() }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Live MT5 API Data -->
            <div class="col-md-6">
                <div class="info-card api-info">
                    <div class="card-header">
                        <span><i class="fas fa-server"></i> Live MT5 API Data</span>
                        <small class="text-muted">On-demand API calls</small>
                    </div>

                    <div style="text-align: center; padding: 20px 0;">
                        <button class="api-button" onclick="getCurrentBalance()" id="balanceBtn">
                            <i class="fas fa-dollar-sign"></i> Get Current Balance
                        </button>

                        <button class="api-button" onclick="getCurrentPositions()" id="positionsBtn">
                            <i class="fas fa-chart-line"></i> Get Open Positions
                        </button>

                        <button class="api-button" onclick="getRecentTradeStats()" id="tradesBtn">
                            <i class="fas fa-history"></i> Get Recent Trades (7 days)
                        </button>
                    </div>

                    <div id="balanceResult" class="api-result"></div>
                    <div id="positionsResult" class="api-result"></div>
                    <div id="tradesResult" class="api-result"></div>
                </div>
            </div>
        </div>

        <!-- Recent Trades from Database -->
        <div class="row">
            <div class="col-12">
                <div class="info-card">
                    <div class="card-header">
                        <span><i class="fas fa-list"></i> Recent Trades from Database</span>
                        <span class="text-muted">(Last {{ count($account->trades) }} trades)</span>
                    </div>

                    @if($account->trades->count() > 0)
                        @foreach($account->trades as $trade)
                            <div class="trade-item">
                                <div class="trade-id">
                                    Position ID: {{ $trade->position_id }}
                                    @if($trade->symbol)
                                        • {{ $trade->symbol }}
                                    @endif
                                </div>
                                <div class="trade-details">
                                    <span>Volume: {{ number_format($trade->volume, 2) }}</span>
                                    <span class="{{ $trade->profit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                        Profit: ${{ number_format($trade->profit, 2) }}
                                    </span>
                                    <span class="timestamp">{{ $trade->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            No trades found in database
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let apiCallInProgress = false;

        function setApiButtonLoading(buttonId, loading = true) {
            const button = document.getElementById(buttonId);
            if (loading) {
                button.disabled = true;
                button.innerHTML = '<span class="loading-spinner"></span>' + button.innerHTML.replace(/<.*?>/g, '').trim();
            } else {
                button.disabled = false;
                button.innerHTML = button.innerHTML.replace('<span class="loading-spinner"></span>', '');
            }
        }

        function showApiResult(resultId, success, data, message = null) {
            const resultDiv = document.getElementById(resultId);
            resultDiv.className = success ? 'api-result api-success' : 'api-result api-error';

            if (success) {
                resultDiv.innerHTML = formatApiData(data);
            } else {
                resultDiv.innerHTML = '<strong>Error:</strong> ' + (message || 'API call failed');
            }

            resultDiv.style.display = 'block';

            // Hide after 30 seconds for success, 45 seconds for errors
            setTimeout(() => {
                resultDiv.style.display = 'none';
            }, success ? 30000 : 45000);
        }

        function formatApiData(data) {
            let html = '<div class="data-table-container"><table class="data-table">';

            for (const [key, value] of Object.entries(data)) {
                if (key === 'positions' && Array.isArray(value)) {
                    html += `<tr><td><strong>${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</strong></td><td>${value.length} positions</td></tr>`;
                } else if (key === 'stats' && typeof value === 'object') {
                    html += `<tr><td><strong>Statistics</strong></td><td>`;
                    for (const [statKey, statValue] of Object.entries(value)) {
                        html += `${statKey.replace(/_/g, ' ')}: ${statValue}<br>`;
                    }
                    html += `</td></tr>`;
                } else {
                    html += `<tr><td><strong>${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</strong></td><td>${value}</td></tr>`;
                }
            }

            html += '</table></div>';
            return html;
        }

        function getCurrentBalance() {
            if (apiCallInProgress) return;

            apiCallInProgress = true;
            setApiButtonLoading('balanceBtn', true);

            $.ajax({
                url: `/admin/accounts/{{ $account->id }}/current-balance`,
                method: 'GET',
                success: function (response) {
                    showApiResult('balanceResult', response.success, response, response.message);
                },
                error: function () {
                    showApiResult('balanceResult', false, null, 'Failed to connect to API');
                },
                complete: function () {
                    setApiButtonLoading('balanceBtn', false);
                    apiCallInProgress = false;
                }
            });
        }

        function getCurrentPositions() {
            if (apiCallInProgress) return;

            apiCallInProgress = true;
            setApiButtonLoading('positionsBtn', true);

            $.ajax({
                url: `/admin/accounts/{{ $account->id }}/current-positions`,
                method: 'GET',
                success: function (response) {
                    showApiResult('positionsResult', response.success, response, response.message);
                },
                error: function () {
                    showApiResult('positionsResult', false, null, 'Failed to connect to API');
                },
                complete: function () {
                    setApiButtonLoading('positionsBtn', false);
                    apiCallInProgress = false;
                }
            });
        }

        function getRecentTradeStats() {
            if (apiCallInProgress) return;

            apiCallInProgress = true;
            setApiButtonLoading('tradesBtn', true);

            $.ajax({
                url: `/admin/accounts/{{ $account->id }}/recent-trade-stats`,
                method: 'GET',
                success: function (response) {
                    showApiResult('tradesResult', response.success, response, response.message);
                },
                error: function () {
                    showApiResult('tradesResult', false, null, 'Failed to connect to API');
                },
                complete: function () {
                    setApiButtonLoading('tradesBtn', false);
                    apiCallInProgress = false;
                }
            });
        }

        function clearAccountCache() {
            if (!confirm('Are you sure you want to clear the sync cache for this account?')) {
                return;
            }

            $.ajax({
                url: `/admin/sync-monitor/accounts/{{ $account->id }}/clear-cache`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function () {
                    alert('Failed to clear account cache');
                }
            });
        }

        function unflagAccount() {
            if (!confirm('Are you sure you want to unflag this account?')) {
                return;
            }

            $.ajax({
                url: `/admin/sync-monitor/accounts/{{ $account->id }}/unflag`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function () {
                    alert('Failed to unflag account');
                }
            });
        }
    </script>
@endsection