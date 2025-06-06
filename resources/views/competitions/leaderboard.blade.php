@extends(
    isset(auth()->user()->role)
        && (auth()->user()->role->name === 'Admin' || auth()->user()->role->name === 'Super Admin')
            ? 'layouts.admin.admin'
            : 'layouts.crm.crm'
)

@section('content')
    @if(isset(auth()->user()->role))
        <div class="main-content app-content">
            <div class="container-fluid">
    @else
        <div class="pc-container">
            <div class="pc-content">
    @endif
        <!-- Page header -->
        <div class="page-header border-0 rounded-3 shadow-sm mb-4 d-block">
            <div class="page-block py-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="page-header-title px-4">
                            <h4 class="mb-2 fw-bold">{{ $currentMonth }} {{ $currentYear }} Competition</h4>
                            <ul class="breadcrumb bg-transparent mb-0 p-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Competition Leaderboard</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <!-- Competition Period Selector -->
                        <form id="periodSelector" class="d-flex justify-content-md-end align-items-center gap-2">
                            <div class="form-group mb-0">
                                <select name="month" class="form-select form-select-sm">
                                    @foreach($months as $m)
                                        <option value="{{ $m }}" {{ $m === $currentMonth ? 'selected' : '' }}>
                                            {{ $m }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <select name="year" class="form-select form-select-sm">
                                    @foreach($years as $y)
                                        <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fe fe-refresh-cw"></i> Update
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page content -->
        <div class="row">
            <div class="col-12">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Stats Cards Row -->
                <div class="row mb-4">
                      <div class="col-sm-6 col-xl-3">
                        <div class="card overflow-hidden">
                            <div class="card-body p-0">
                                <div class="bg-primary px-3 pt-3 pb-2 rounded-top {{ !isset(auth()->user()->role) ? 'pt-4' : '' }}">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avatar bg-opacity-25">
                                                <span class="avatar-title rounded">
                                                    <i class="fe fe-trending-up {{ isset(auth()->user()->role) ? 'text-white' : 'text-muted' }}"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0 text-white {{ isset(auth()->user()->role) ? '' : 'p-2' }}">{{ $competitionStatus }}</h6>
                                        </div>
                                    </div>
                                </div>
                                @if($showTimer)
                                {{-- {{ dd($showTimer) }} --}}
                                <div class="px-4 py-2 ">
                                    <div id="competitionTimer" class="d-flex justify-content-between">
                                        <div class="text-center">
                                            <span id="days" class="h4 mb-0 fw-bold text-primary">00</span>
                                            <div class="small text-muted">Days</div>
                                        </div>
                                        <div class="text-center">
                                            <span id="hours" class="h4 mb-0 fw-bold text-primary">00</span>
                                            <div class="small text-muted">Hours</div>
                                        </div>
                                        <div class="text-center">
                                            <span id="minutes" class="h4 mb-0 fw-bold text-primary">00</span>
                                            <div class="small text-muted">Minutes</div>
                                        </div>
                                        <div class="text-center">
                                            <span id="seconds" class="h4 mb-0 fw-bold text-primary">00</span>
                                            <div class="small text-muted">Seconds</div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <x-competition.stats-card
                            title="Total Participants"
                            :value="$stats['participants']"
                            icon="users"
                        />
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <x-competition.stats-card
                            title="Prize Pool"
                            value="Challange Account"
                            icon="bar-chart-2"
                        />
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <x-competition.stats-card
                            title="Top performer"
                            :value="$stats['top_performer']->name ?? 'N/A'"
                            icon="dollar-sign"
                        />
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="row">
                    <!-- Rankings List -->
                    <div class="col-lg-4 col-md-12 mb-4">
                        <div class="card rankings-card">
                            <div class="card-header bg-gradient-dark">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <i class="fe fe-award me-2"></i>
                                    Top Performers
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    @forelse($rankings as $rank)
                                        <a href="#" class="list-group-item list-group-item-action trader-select py-3"
                                           data-account="{{ $rank['account_code'] }}">
                                            <div class="d-flex align-items-center">
                                                <!-- Rank Badge -->
                                                <div class="flex-shrink-0 position-relative">
                                                    <div class="avatar avatar-sm {{ $rank['rank'] <= 3 ? 'rank-badge-'.$rank['rank'] : '' }}">
                                                        <span class="avatar-title rounded-circle {{ $rank['rank'] <= 3 ? 'bg-gradient-primary' : 'bg-gradient-dark' }}">
                                                            @if($rank['rank'] === 1)
                                                                <i class="fe fe-star text-warning"></i>
                                                            @elseif($rank['rank'] === 2)
                                                                <i class="fe fe-star text-light"></i>
                                                            @elseif($rank['rank'] === 3)
                                                                <i class="fe fe-star text-orange"></i>
                                                            @else
                                                                {{ $rank['rank'] }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                                <!-- Trader Info -->
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <medium class=" trader-text">{{ $rank['name'] }}</medium>
                                                        <span class="trader-text px-2 py-1">
                                                            ${{ number_format($rank['equity'], 2) }}
                                                        </span>
                                                    </div>
                                                    <div class="d-flex align-items-center mb-1">
                                                        <small class="trader-text me-3">
                                                            <i class="fe fe-mail me-1"></i>
                                                            {{ $rank['email'] }}
                                                        </small>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <small class="trader-text me-3">
                                                                <i class="fe fe-hash me-1"></i>
                                                                {{ $rank['account_code'] }}
                                                            </small>
                                                            <small class="trader-text me-3">
                                                                <i class="fe fe-bar-chart-2 me-1"></i>
                                                                {{ number_format($rank['volume'], 2) }} Lots
                                                            </small>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <small class="trader-text me-3">
                                                                <i class="fe fe-activity me-1"></i>
                                                                {{ $rank['total_trades'] }} Trades
                                                            </small>
                                                            <small class="{{ $rank['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                <i class="fe fe-trending-{{ $rank['total_profit'] >= 0 ? 'up' : 'down' }} me-1"></i>
                                                                ${{ number_format($rank['total_profit'], 2) }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0 ms-2">
                                                    <i class="fe fe-chevron-right text-muted"></i>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="fe fe-users text-muted" style="font-size: 48px;"></i>
                                            </div>
                                            <h6 class="text-muted">No participants yet</h6>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart and Trading Logs -->
                    <div class="col-lg-8 col-md-12">
                        <!-- Performance Chart -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Performance Chart</h5>
                                <div class="card-tools">
                                    <button class="btn btn-sm btn-light" id="toggleChart">
                                        <i class="fe fe-maximize-2"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="position: relative; height: 300px;">
                                    <canvas id="performanceChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Trading Logs -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Trading History</h5>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" id="prevPage" disabled>
                                        <i class="fe fe-chevron-left"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" id="nextPage" disabled>
                                        <i class="fe fe-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Position Id</th>
                                                <th>Open Time</th>
                                                <th>Close Time</th>
                                                <th>Symbol</th>
                                                <th>Volume</th>
                                                <th class="text-end">Profit</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tradingLogs">
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fe fe-info me-1"></i>
                                                        Select a trader to view their trading history
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    @push('styles')
        <style>
        .avatar {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-sm {
            width: 24px;
            height: 24px;
            font-size: 12px;
        }

        .avatar-title {
            width: 100%;
            height: 100%;
            background-color: #5E35B1;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Dark theme styles */
        .competition-card {
            background: #1a1a1a;
            border: 1px solid #2d2d2d;
            border-radius: 12px;
        }

        .competition-card .card-header {
            background: #1a1a1a;
            border-bottom: 1px solid #2d2d2d;
            color: #fff;
        }

        .competition-list {
            background: #1a1a1a;
        }

        .trader-select {
            transition: all 0.2s ease;
            border: none !important;
            background: #1a1a1a;
            color: #fff;
            border-bottom: 1px solid #2d2d2d !important;
        }

        .trader-select:hover {
            background-color: rgba(94, 53, 177, 0.1);
        }

        .trader-select.active {
            background-color: rgba(94, 53, 177, 0.2);
            border-left: 3px solid #5E35B1 !important;
        }

        .trader-text {
            color: #6c757d !important; /* text-muted color */
        }

        .trader-select.active .trader-text {
            color: #fff !important;
        }

        .chart-container {
            transition: height 0.3s ease;
            background: #1a1a1a;
            padding: 15px;
            border-radius: 8px;
        }

        .chart-container.expanded {
            height: 500px !important;
        }

        /* Custom scrollbar with dark theme */
        .list-group {
            max-height: 600px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #5E35B1 #1a1a1a;
        }

        .list-group::-webkit-scrollbar {
            width: 6px;
        }

        .list-group::-webkit-scrollbar-track {
            background: #1a1a1a;
        }

        .list-group::-webkit-scrollbar-thumb {
            background: #5E35B1;
            border-radius: 3px;
        }

        .list-group::-webkit-scrollbar-thumb:hover {
            background: #4527A0;
        }

        .competition-stats {
            border-radius: 12px;
            padding: 20px;
            background: linear-gradient(45deg, #2d2d2d, #1a1a1a);
            color: #fff;
        }

        .table {
            color: #fff;
        }

        .table th {
            border-bottom-color: #2d2d2d;
        }

        .table td {
            border-color: #2d2d2d;
        }

        .trading-history {
            background: #1a1a1a;
        }

        /* Rankings card styles */
        .rankings-card {
            background: #121212;
            border: 1px solid #2d2d2d;
            border-radius: 12px;
        }

        .rankings-card .card-header {
            background: linear-gradient(90deg, #5E35B1 0%, #2d2d2d 100%);
            color: #fff;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .rankings-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .top-rank {
            position: relative;
        }

        .top-rank::before {
            content: '';
            position: absolute;
            top: 50%;
            left: -8px;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #5E35B1;
            transform: translateY(-50%);
        }

        .bg-gradient-dark {
            background: linear-gradient(45deg, #1a1c1e, #2c2e30);
        }

        .bg-success-gradient {
            background: linear-gradient(45deg, #28a745, #20c997);
        }

        .bg-gradient-primary {
            background: linear-gradient(45deg, #2196f3, #1e88e5);
        }

        .list-group-item {
            background-color: #1a1c1e;
            border-color: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .list-group-item:hover,
        .list-group-item.active {
            background-color: #2c2e30;
        }

        .list-group-item .text-white {
            color: #e5e5e5 !important;
        }

        .list-group-item:hover .text-white,
        .list-group-item.active .text-white {
            color: #fff !important;
        }

        .avatar {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-title {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .rank-badge-1 {
            border: 2px solid #ffd700;
        }

        .rank-badge-2 {
            border: 2px solid #c0c0c0;
        }

        .rank-badge-3 {
            border: 2px solid #cd7f32;
        }

        .text-orange {
            color: #fd7e14;
        }

        .trader-select {
            cursor: pointer;
            color: #fff;
            text-decoration: none;
        }

        .trader-select:hover {
            color: #fff;
            text-decoration: none;
        }

        .trader-select:hover h6.text-white {
                color: rgb(107, 107, 107) !important;
            }

        @media (max-width: 991.98px) {
            .list-group {
                max-height: 400px;
            }
        }
        </style>
    @endpush
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let chart;
        let currentPage = 1;
        const perPage = 10;

        // Initialize Chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Equity',
                    data: [],
                    borderColor: '#5E35B1',
                    backgroundColor: 'rgba(94, 53, 177, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Toggle chart size
        document.getElementById('toggleChart').addEventListener('click', function() {
            const container = document.querySelector('.chart-container');
            container.classList.toggle('expanded');
            this.querySelector('i').classList.toggle('fe-maximize-2');
            this.querySelector('i').classList.toggle('fe-minimize-2');
            chart.resize();
        });

        // Handle period selection
        document.getElementById('periodSelector').addEventListener('submit', function(e) {
            e.preventDefault();
            const month = this.month.value;
            const year = this.year.value;
            window.location.href = `?month=${month}&year=${year}`;
        });

        // Handle trader selection
        document.querySelectorAll('.trader-select').forEach(item => {
            item.addEventListener('click', async (e) => {
                e.preventDefault();
                document.querySelectorAll('.trader-select').forEach(el => el.classList.remove('active'));
                item.classList.add('active');

                const accountNo = item.dataset.account;
                // console.log(item);
                await updateTraderData(accountNo);
            });
        });

        async function updateTraderData(accountNo) {
            try {
                // Using a static test account number for development
                const testAccountNo = accountNo;  // Replace with a real account number from your database
                console.log('Using test account:', testAccountNo);

                const isAdmin = @json(isset(auth()->user()->role));

                // Get selected month and year from period selector
                const selectedMonth = document.querySelector('select[name="month"]').value;
                const selectedYear = document.querySelector('select[name="year"]').value;

                // Use the appropriate endpoint based on user role
                const endpoint = isAdmin
                    ? `/admin/competition/trader-data/${testAccountNo}/${selectedMonth}/${selectedYear}`
                    : `/competition/trader/?account${testAccountNo}?month=${selectedMonth}&year=${selectedYear}`;

                const response = await fetch(endpoint);

                if (!response.ok) throw new Error('Network response was not ok');

                const data = await response.json();
                // console.log(data.chart_data);
                // Update chart
                chart.data.labels = data.chart_data.labels;
                chart.data.datasets[0].data = data.chart_data.equity;
                chart.update();

                // Update trading logs
                updateTradingLogs(data.trades);

                // Update pagination buttons
                const maxPages = Math.ceil(data.trades.length / perPage);
                document.getElementById('prevPage').disabled = currentPage <= 1;
                document.getElementById('nextPage').disabled = currentPage >= maxPages;
            } catch (error) {
                console.error('Error fetching trader data:', error);
                // Show error message to user
                const tbody = document.getElementById('tradingLogs');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-danger">
                                <i class="fe fe-alert-triangle me-1"></i>
                                Error loading trading data. Please try again.
                            </div>
                        </td>
                    </tr>
                `;
            }
        }

        function updateTradingLogs(trades) {
            const tbody = document.getElementById('tradingLogs');
            tbody.innerHTML = '';

            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            const pageData = trades.slice(start, end);

            if (pageData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fe fe-inbox me-1"></i>
                                No trading history available
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            pageData.forEach(trade => {
                const row = document.createElement('tr');
                const profitClass = trade.profit >= 0 ? 'text-success' : 'text-danger';
                const profitIcon = trade.profit >= 0 ? 'trending-up' : 'trending-down';

                row.innerHTML = `
                    <td>${trade.position}</td>
                    <td>${new Date(trade.open_time).toLocaleString()}</td>
                    <td>${new Date(trade.close_time).toLocaleString()}</td>
                    <td>${trade.symbol}</td>
                    <td>${trade.volume}</td>
                    <td class="text-end ${profitClass}">
                        <i class="fe fe-${profitIcon} me-1"></i>
                        $${parseFloat(trade.profit).toFixed(2)}
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Pagination handlers
        document.getElementById('prevPage').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                const activeTrader = document.querySelector('.trader-select.active');

                if (activeTrader) {
                    updateTraderData(activeTrader.dataset.account);
                }
            }
        });

        document.getElementById('nextPage').addEventListener('click', () => {
            currentPage++;
            const activeTrader = document.querySelector('.trader-select.active');
            if (activeTrader) {
                updateTraderData(activeTrader.dataset.account);
            }
        });

        // Competition Timer
        if (@json($showTimer)) {

            const targetDate = new Date(@json($targetDate)).getTime();

            function updateTimer() {
                const now = new Date().getTime();
                const distance = targetDate - now;

                // If timer has expired, reload the page to update status
                if (distance < 0) {
                    location.reload();
                    return;
                }

                // Calculate time units
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Update timer display
                document.getElementById('days').textContent = String(days).padStart(2, '0');
                document.getElementById('hours').textContent = String(hours).padStart(2, '0');
                document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
                document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
            }

            // Update timer immediately and then every second
            updateTimer();
            setInterval(updateTimer, 1000);
        }
        // Load initial data for the first trader
        document.querySelector('.trader-select')?.click();
    </script>
@endsection
