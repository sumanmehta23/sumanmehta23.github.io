<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>August Competition - Leaderboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap 5 Bundle JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .container-fluid {
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table thead th {
            background-color: #e9ecef;
            border-bottom: none;
        }

        .chart-container {
            position: relative;
            height: 100%;
            margin-left: -10%;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        #performanceChart {
            width: 100% !important;
            height: 100% !important;
        }


        /* Responsive Mobile Styles */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: start;
                gap: 10px;
            }
            header nav {
                flex-wrap: wrap;
                gap: 12px;
            }

            .container-fluid {
                padding: 10px;
            }

            section h1 {
                font-size: 1.75rem; /* smaller title */
            }

            /* Grid sections stack into 1 column */
            .grid {
                grid-template-columns: 1fr !important;
            }

            /* Tables: horizontal scroll on mobile */
            table {
                font-size: 0.85rem;
            }
            .overflow-x-auto {
                overflow-x: scroll;
            }

            /* Performer card layout */
            #performerCard .grid {
                grid-template-columns: 1fr !important;
            }
            #performerCard .p-6 {
                padding: 1rem !important;
            }

            /* Chart responsiveness */
            .chart-container {
                margin-left: 0 !important;
                height: 300px !important;
            }

            /* Footer */
            .container-fluid .row {
                flex-direction: column;
            }
            .container-fluid .col-lg-4,
            .container-fluid .col-md-6,
            .container-fluid .col-lg-2 {
                width: 100%;
                margin-bottom: 20px;
            }
            .container-fluid ul {
                padding-left: 0;
            }
        }




    </style>
</head>

<body class="bg-gray-50 font-sans">
    <header class="flex justify-between items-center p-6 bg-white shadow">
        <div class="flex items-center space-x-2">
            <a href='https://www.lqhmarkets.com/'>
                <img src="/{{ $settings['admin_sidebar_logo'] }}" class="w-36 md:w-44" alt="logo">
            </a>
        </div>
        <nav class="flex items-center space-x-6">
            {{-- <a href="#" class="text-gray-700 hover:text-emerald-600 font-medium">Leaderboard</a> --}}
            <a href="/competitions-overview" class="text-gray-700 hover:text-emerald-600 font-medium">Competitions</a>
            @guest
                <a href="/login" class="px-5 py-2 bg-emerald-700 text-white rounded-lg font-semibold shadow hover:bg-emerald-800 w-full md:w-auto text-center">
                    Sign Up
                </a>
            @else
                <a href="/dashboard" class="px-5 py-2 bg-emerald-700 text-white rounded-lg font-semibold shadow hover:bg-emerald-800 w-full md:w-auto text-center">
                    Dashboard
                </a>
            @endguest
        </nav>
    </header>
    <div class="container-fluid px-10">
        <!-- Page Heading -->
        <section class="text-center mt-10 mb-10">
            <h1 class="text-4xl font-bold capitalize">{{ $competition->ac_name }}</h1>
        </section>
        {{-- {{ dd($competition) }} --}}
        {{-- {{ dd($stats) }} --}}
        {{-- {{ dd($rankings) }} --}}
        <!-- Header Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <!-- Time Remaining -->
            <div class="card p-3 bg-white shadow rounded-lg">
                <p class="text-muted mb-1 font-bold">Time Remaining</p>
                <h5 id="countdown">Loading...</h5>
            </div>

            <!-- Prize Pool -->
            <div class="card p-3 bg-white shadow rounded-lg">
                <p class="text-muted mb-1 font-bold">Prize Pool</p>

                <!-- Button to trigger modal -->
                <button type="button" style="width: fit-content;"
                    class="px-3 py-1 text-sm bg-emerald-700 text-white rounded-md font-medium shadow hover:bg-emerald-800"
                    data-bs-toggle="modal" data-bs-target="#prizeModal">
                    More Info
                </button>
            </div>

            <!-- Bootstrap Modal -->
            <div class="modal fade" id="prizeModal" tabindex="-1" aria-labelledby="prizeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="prizeModalLabel">Prize Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            {!! $competition->prize !!}
                        </div>
                        <div class="modal-footer">
                            <button type="button"
                                class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300"
                                data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contestants -->
            <div class="card p-3 bg-white shadow rounded-lg">
                <p class="text-muted mb-1 font-bold">Contestants</p>
                <h5>{{ $stats['participants'] }}</h5>
            </div>

            <!-- Current Leader -->
            <div class="card p-3 bg-white shadow rounded-lg">
                <p class="text-muted mb-1 font-bold">Current Leader</p>
                <h5>{{ $rankings[0]['name'] ?? 'N/A' }}</h5>
            </div>
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Competition Standings -->
            <div class="bg-white shadow rounded-lg mt-6 overflow-hidden">
                <div class="card-body p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h5 class="card-title font-bold">Competition Standings</h5>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-left text-sm font-semibold">
                                    <th class="p-3">#Rank</th>
                                    <th class="p-3">Name</th>
                                    <th class="p-3">Balance</th>
                                    <th class="p-3">Equity</th>
                                    <th class="p-3">Profit</th>
                                    <th class="p-3">Total trades</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm" id="rankingsTableBody">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="p-4 border-t">
                                        <div class="flex justify-between items-center text-xs text-gray-500">
                                            <div class="flex items-center gap-2">
                                                <button id="prevRankPage" class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50">&lt; Prev</button>
                                                <span id="rankingsPaginationInfo">Page 1 of 1</span>
                                                <button id="nextRankPage" class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50">Next &gt;</button>
                                            </div>
                                            <span>Showing 5 entries per page</span>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Peter S. Performance -->

            <!-- Stats -->
            <div id="performerCard" class="bg-white shadow rounded-lg mt-6 overflow-hidden">
                <h5 id="performerName" class="font-bold p-4">{{ $rankings[0]['name'] ?? '' }}</h5>
                <div class="grid grid-cols-1 lg:grid-cols-12">
                    <div class="p-6 lg:col-span-3 rounded-r-none">
                        <p class="mb-2">
                            <strong>Current Balance:</strong><br>
                            <span id="currentBalance">${{ $rankings[0]['balance'] ?? '' }}</span>
                        </p>
                        <p class="mb-2"><strong>Cumulative P/L:</strong><br> <span id="cumulativePL">${{ $rankings[0]['total_profit'] ?? '' }}</span></p>
                        <p class="mb-2"><strong>Biggest Trade:</strong><br> <span id="largestTrade">${{ $rankings[0]['top_trade'] ?? '' }}</span></p>
                        <p class="mb-2"><strong>Equity:</strong><br> <span id="equity">${{ $rankings[0]['equity'] ?? '' }}</span></p>
                    </div>

                    <!-- Chart -->
                    <div class="lg:col-span-9 rounded-l-none">
                        <div class="card-body p-6">
                            <div class="chart-container h-100">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="bg-white shadow rounded-lg mt-6 overflow-hidden" id="tradingLogCard">
            <div class="p-4 border-b">
                <h5 id="tradingLogTitle" class="font-bold text-lg">
                    {{ $stats['top_performer']->name ?? '' }} - Trading Log
                </h5>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">OPEN TIME (UTC)</th>
                            <th class="px-4 py-3">SYMBOL</th>
                            <th class="px-4 py-3">POSITION ID</th>
                            <th class="px-4 py-3">TYPE</th>
                            <th class="px-4 py-3">VOLUME</th>
                            <th class="px-4 py-3">OPEN PRICE</th>
                            <th class="px-4 py-3">CLOSE TIME (UTC)</th>
                            <th class="px-4 py-3">CLOSE PRICE</th>
                            <th class="px-4 py-3">PROFIT</th>
                            {{-- <th class="px-4 py-3">CHANGE</th> --}}
                        </tr>
                    </thead>
                    <tbody id="tradingLogBody" class="divide-y">
                        {{-- {{ dd($stats) }} --}}
                        {{-- default trades loaded here --}}
                        {{-- @foreach($stats['top_performer']->trades ?? [] as $trade)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3"> {{ \Carbon\Carbon::parse($trade->open_time)->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-3">{{ $trade->symbol }}</td>
                                <td class="px-4 py-3">#{{ $trade->position_id }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-semibold {{ $trade->type == 'BUY' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100' }} rounded">
                                        {{ strtoupper($trade->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $trade->volume }}</td>
                                <td class="px-4 py-3">{{ $trade->open_price }}</td>
                                <td class="px-4 py-3"> {{ \Carbon\Carbon::parse($trade->close_time)->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-3">{{ $trade->close_price }}</td>
                                <td class="px-4 py-3 {{ $trade->profit >= 0 ? 'text-green-600 font-bold' : 'text-red-600' }}">
                                    {{ $trade->profit }}
                                </td>
                            </tr>
                        @endforeach --}}
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center p-4 text-xs text-gray-500 border-t">
                <div class="flex items-center gap-2">
                    <button id="prevPage" class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50">&lt; Prev</button>
                    <span id="paginationInfo">Page 1 of 1 | Items {{ count($stats['top_performer']->trades ?? []) }}</span>
                    <button id="nextPage" class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50">Next &gt;</button>
                </div>
                <span id="lastUpdate">Last Update: 5 minutes ago</span>
            </div>
        </div>
    </div>

    <!-- Footer (Bootstrap) -->
  <div class="container-fluid bg-light mt-5 pt-5">
        <div class="px-6 py-5">
            <div class="row gy-4">

                <!-- Logo & Info -->
                <div class="col-lg-4 col-md-6">
                    <a href="/" class="d-inline-block mb-3">
                        <img src="/{{ $settings['admin_sidebar_logo'] }}" alt="LQH Markets Logo" class="img-fluid" style="max-height: 45px;">
                    </a>
                    <p class="text-muted mb-1">
                        <span class="notranslate">LQH Integrated Ltd</span> <br> Ground Floor, Rodney Court Building, Rodney Bay, Gros Islet, Saint Lucia.
                    </p>
                    <p class="mb-1">Email: <a href="mailto:support@lqhmarkets.com" class="text-success text-decoration-none">support@lqhmarkets.com</a></p>
                    <p class="mb-0 text-muted">© {{ date('Y') }} LQH Markets | All rights reserved.</p>
                </div>

                <!-- Explore -->
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="fw-bold mb-3">Explore</h6>
                    <ul class="list-unstyled">
                        <li><a href="https://www.lqhmarkets.com/" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">Home</a></li>
                        <li><a href="https://www.lqhmarkets.com/mt5" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">MetaTrader 5</a></li>
                        <li><a href="https://www.lqhmarkets.com/about-us" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">About Us</a></li>
                        <li><a href="https://www.lqhmarkets.com/help-center" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">Help Center</a>
                        </li>
                        <li><a href="https://www.lqhmarkets.com/lot-size-calculator" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">Lot Size Calculator</a></li>
                    </ul>
                </div>

                <!-- Disclosures -->
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="fw-bold mb-3">Disclosures</h6>
                    <ul class="list-unstyled">
                        <li><a href="https://www.lqhmarkets.com/risk-disclaimer" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">Risk Disclaimer</a></li>
                        <li><a href="https://www.lqhmarkets.com/terms-conditions" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">Terms &amp; Conditions</a></li>
                        <li><a href="https://www.lqhmarkets.com/privacy-policy" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="fw-bold mb-3">Company</h6>
                    <ul class="list-unstyled">
                        <li><a href="https://www.lqhmarkets.com/about-us" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">About</a></li>
                        <li><a href="https://www.lqhmarkets.com/contact-us" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">Contact</a></li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="fw-bold mb-3">Social Media</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li>
                            <a href="https://discord.gg/lqhmarkets" target="_blank" class="d-flex align-items-center text-decoration-none text-dark">
                                <img src="https://cdn.prod.website-files.com/66d6faa07d7bd55c6f3ca508/683d55e6248e95183cea86a5_icons8-discord-500.png" alt="Discord" class="me-2" style="height: 20px; width: 20px;">
                                Discord
                            </a>
                        </li>
                        <li>
                            <a href="https://instagram.com/lqhmarkets" target="_blank" class="d-flex align-items-center text-decoration-none text-dark">
                                <img src="https://cdn.prod.website-files.com/66d6faa07d7bd55c6f3ca508/683d5538ee0b29783635919a_icons8-instagram-500.png" alt="Instagram" class="me-2" style="height: 20px; width: 20px;">
                                Instagram
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Legal -->
        <div class="border-top py-4">
            <div class="px-6 text-muted small">
                <p><strong>Legal:</strong> <span class="notranslate">LQH Integrated Ltd</span> is LQHMarkets.com and the LQH Markets brand and trademark is owned
                    by <span class="notranslate">LQH Integrated Ltd</span>.</p>
                <p><span class="notranslate">LQH Integrated Ltd</span> holds an International Brokerage and Clearing House License in Comoros with license number
                    L15833/LIL.</p>
                <p><span class="notranslate">LQH Integrated Ltd</span> holds a license in St. Lucia as an International Business Company with registration number
                    2023-00570.</p>
                <p><strong>Risk Warning:</strong> An investment in derivatives may mean investors may lose an amount even greater
                    than their original investment. Anyone wishing to invest in any of the products mentioned in
                    <a href="https://www.LQHMarkets.com" class="text-success">www.LQHMarkets.com</a> should seek their own
                    financial or professional advice.
                </p>
                <p><strong>Restricted Regions:</strong> <span class="notranslate">LQH Integrated Limited</span> does not provide services for
                    citizens/residents of the United States, Cuba, Iran, Myanmar, North Korea, Sudan, China, Singapore and to
                    jurisdictions on the FATF, OFAC and EU/UN sanctions lists.</p>
                <p class="mb-0">© {{ date('Y') }} LQH Markets. All rights reserved.</p>
            </div>
        </div>
    </div>
    <script>
        // Rankings pagination
        const rankings = @json($rankings);
        let currentRankPage = 1;
        const ranksPerPage = 5;

        function renderRankings(page) {
            const startIndex = (page - 1) * ranksPerPage;
            const endIndex = startIndex + ranksPerPage;
            const ranksToShow = rankings.slice(startIndex, endIndex);

            const tbody = document.getElementById('rankingsTableBody');
            tbody.innerHTML = '';

            ranksToShow.forEach(rank => {
                const tr = document.createElement('tr');
                tr.className = 'bg-white border-b hover:bg-gray-50 cursor-pointer competitor-row';
                tr.dataset.name = rank.name ?? 'N/A';
                tr.dataset.balance = rank.balance ?? 0;
                tr.dataset.equity = rank.equity ?? 0;
                tr.dataset.profit = rank.total_profit ?? 0;
                tr.dataset.topTrade = rank.top_trade ?? 0;
                tr.dataset.accountCode = rank.account_code ?? '';
                tr.dataset.startDate = rank.start_date ?? '';
                tr.dataset.endDate = rank.end_date ?? '';
                tr.dataset.trades = rank.total_trades ?? 0;

                tr.innerHTML = `
                    <td class="p-3 font-bold">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-bold">
                            ${rank.rank ?? 'N/A'}
                        </span>
                    </td>
                    <td class="p-3">${rank.name ?? 'N/A'}</td>
                    <td class="p-3">$${rank.balance ?? 'N/A'}</td>
                    <td class="p-3">$${rank.equity ?? 'N/A'}</td>
                    <td class="p-3">$${rank.total_profit ?? 'N/A'}</td>
                    <td class="p-3">${rank.total_trades ?? 'N/A'}</td>
                `;

                tbody.appendChild(tr);
            });

            // Update pagination info and buttons
            const totalPages = Math.ceil(rankings.length / ranksPerPage);
            document.getElementById('rankingsPaginationInfo').innerText =
                `Page ${page} of ${totalPages}`;

            // Enable/disable pagination buttons
            document.getElementById('prevRankPage').disabled = page <= 1;
            document.getElementById('nextRankPage').disabled = page >= totalPages;

            // Reattach click handlers for competitor rows
            attachCompetitorRowHandlers();
        }

        // Add pagination event listeners for rankings
        document.getElementById('prevRankPage').addEventListener('click', () => {
            if (currentRankPage > 1) {
                currentRankPage--;
                renderRankings(currentRankPage);
            }
        });

        document.getElementById('nextRankPage').addEventListener('click', () => {
            const totalPages = Math.ceil(rankings.length / ranksPerPage);
            if (currentRankPage < totalPages) {
                currentRankPage++;
                renderRankings(currentRankPage);
            }
        });

        // Initial render of rankings
        renderRankings(1);

        // Function to attach click handlers to competitor rows
        function attachCompetitorRowHandlers() {
            document.querySelectorAll('.competitor-row').forEach(row => {
                row.addEventListener('click', async () => {
                    const name = row.dataset.name;
                    const balance = row.dataset.balance;
                    const equity = row.dataset.equity;
                    const profit = row.dataset.profit;
                    const topTrade = row.dataset.topTrade;
                    const account = row.dataset.accountCode;
                    const startDate = row.dataset.startDate;
                    const endDate = row.dataset.endDate;

                    // Update performer card
                    document.getElementById('performerName').innerText = name;
                    document.getElementById('currentBalance').innerText = `$${balance}`;
                    document.getElementById('cumulativePL').innerText = `$${profit}`;
                    document.getElementById('largestTrade').innerText = `$${topTrade}`;
                    document.getElementById('equity').innerText = `$${equity}`;

                    // Update Trading Log title
                    document.getElementById('tradingLogTitle').innerText = `${name} - Trading Log`;
                    await updateTraderData(account, startDate, endDate);
                });
            });
        }

        // Parse PHP dates safely into JS Date objects (UTC)
        const startDate = new Date("{{ $stats['competition_start_date'] }} UTC").getTime();
        const endDate   = new Date("{{ $stats['competition_end_date'] }} UTC").getTime();

        let interval; // declare first so it’s accessible everywhere

        function updateCountdown() {
            const now = Date.now(); // current UTC in ms

            let distance;
            let label;

            if (now < startDate) {
                // Before competition starts
                distance = startDate - now;
                label = "Starts in: ";
            } else if (now >= startDate && now <= endDate) {
                // Competition is running
                distance = endDate - now;
                label = "Ends in: ";
            } else {
                // Competition ended
                document.getElementById("countdown").innerHTML = "Competition Ended";
                clearInterval(interval);
                return;
            }

            // Calculate time parts
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Show countdown
            document.getElementById("countdown").innerHTML =
                `${label} ${days}d ${hours}h ${minutes}m ${seconds}s`;
        }

        // Run immediately and then every second
        updateCountdown();
        interval = setInterval(updateCountdown, 1000);

        // Add pagination event listeners
        document.getElementById('prevPage').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderTrades(currentPage);
            }
        });

        document.getElementById('nextPage').addEventListener('click', () => {
            const totalPages = Math.ceil(allTrades.length / tradesPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderTrades(currentPage);
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const firstRanking = rankings[0];
            if (firstRanking) {
                // Update initial performer card with top ranking trader
                document.getElementById('performerName').innerText = firstRanking.name;
                document.getElementById('currentBalance').innerText = `$${firstRanking.balance}`;
                document.getElementById('cumulativePL').innerText = `$${firstRanking.total_profit}`;
                document.getElementById('largestTrade').innerText = `$${firstRanking.top_trade}`;
                document.getElementById('equity').innerText = `$${firstRanking.equity}`;

                // Load initial trader data for the chart
                await updateTraderData(
                    firstRanking.account_code,
                    firstRanking.start_date,
                    firstRanking.end_date
                );
            }
        });
    </script>


    <script>
        // Init Chart (only once)
        const ctx = document.getElementById('performanceChart').getContext('2d');

        let performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Equity',
                    data: [],
                    borderColor: '#5E35B1',
                    backgroundColor: 'rgba(94, 53, 177, 0.1)',
                    tension: 0.5,
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
                        },
                        ticks: {
                            maxTicksLimit: 6 // <-- Add this line to limit Y axis intervals
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

        // Handle row clicks
        document.querySelectorAll('.competitor-row').forEach(row => {
            row.addEventListener('click', async () => {
                // console.log(row.dataset);
                const name = row.dataset.name;
                const balance = row.dataset.balance;
                const equity = row.dataset.equity;
                const profit = row.dataset.profit;
                const topTrade = row.dataset.topTrade;
                const trades = row.dataset.trades;
                const account = row.dataset.accountCode;
                const startDate = row.dataset.startDate;
                const endDate = row.dataset.endDate;

                // Update performer card
                document.getElementById('performerName').innerText = name;
                // document.getElementById('startingBalance').innerText = balance;
                document.getElementById('currentBalance').innerText = `$${balance}`;
                document.getElementById('cumulativePL').innerText = `$${profit}`;
                document.getElementById('largestTrade').innerText = `$${topTrade}`;
                document.getElementById('equity').innerText = `$${equity}`;

                // Update Trading Log title
                document.getElementById('tradingLogTitle').innerText = `${name} - Trading Log`;
                await updateTraderData(account,startDate,endDate);
            });
        });

        let currentPage = 1;
        let tradesPerPage = 10;
        let allTrades = [];

        function renderTrades(page) {
            const startIndex = (page - 1) * tradesPerPage;
            const endIndex = startIndex + tradesPerPage;
            const tradesToShow = allTrades.slice(startIndex, endIndex);

            const tbody = document.getElementById('tradingLogBody');
            tbody.innerHTML = "";

            tradesToShow.forEach(trade => {
                const openTime = toUtcString(trade.open_time);
                const closeTime = trade.close_time ? toUtcString(trade.close_time) : "";

                const tr = document.createElement('tr');
                tr.classList.add("hover:bg-gray-50");
                tr.innerHTML = `
                    <td class="px-4 py-3">${openTime}</td>
                    <td class="px-4 py-3">${trade.symbol}</td>
                    <td class="px-4 py-3">#${trade.position}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-semibold ${trade.type.toLowerCase() == 'buy' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100'} rounded">
                            ${trade.type.toUpperCase()}
                        </span>
                    </td>
                    <td class="px-4 py-3">${trade.volume}</td>
                    <td class="px-4 py-3">${trade.open_price}</td>
                    <td class="px-4 py-3">${closeTime}</td>
                    <td class="px-4 py-3">${trade.close_price}</td>
                    <td class="px-4 py-3 ${trade.profit >= 0 ? 'text-green-600 font-bold' : 'text-red-600'}">${trade.profit}</td>
                `;
                tbody.appendChild(tr);
            });

            // Update pagination info and buttons
            const totalPages = Math.ceil(allTrades.length / tradesPerPage);
            document.getElementById('paginationInfo').innerText =
                `Page ${page} of ${totalPages} | Items ${allTrades.length}`;

            // Enable/disable pagination buttons
            document.getElementById('prevPage').disabled = page <= 1;
            document.getElementById('nextPage').disabled = page >= totalPages;
        }

        async function updateTraderData(accountCode,startDate,endDate) {
            try {
                const res = await fetch(`/competitions-overview/trader-data/${accountCode}/${startDate}/${endDate}`);
                const tradesData = await res.json();

                performanceChart.data.labels = tradesData.chart_data.labels;
                performanceChart.data.datasets[0].data = tradesData.chart_data.equity;
                performanceChart.update();

                // Store all trades and render first page
                allTrades = tradesData.trades;
                currentPage = 1;
                renderTrades(currentPage);

                document.getElementById('lastUpdate').innerText = ``;

            } catch (e) {
                console.error("Error fetching trades:", e);
            }
        }

        // Converts any date string to "YYYY-MM-DD HH:mm:ss" in UTC
        function toUtcString(dateString) {
            const date = new Date(dateString);
            const year = date.getUTCFullYear();
            const month = String(date.getUTCMonth() + 1).padStart(2, "0");
            const day = String(date.getUTCDate()).padStart(2, "0");
            const hours = String(date.getUTCHours()).padStart(2, "0");
            const minutes = String(date.getUTCMinutes()).padStart(2, "0");
            const seconds = String(date.getUTCSeconds()).padStart(2, "0");
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        }
    </script>


</body>

</html>
