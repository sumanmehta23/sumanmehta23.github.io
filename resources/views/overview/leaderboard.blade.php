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
            height: 300px;
            margin-top: 20px;
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
    </style>
</head>

<body class="bg-gray-50 font-sans">
    <header class="flex justify-between items-center p-6 bg-white shadow">
        <div class="flex items-center space-x-2">
            <img src="/{{ $settings['admin_sidebar_logo'] }}" class="w-44" alt="logo">
        </div>
        <nav class="flex items-center space-x-6">
            {{-- <a href="#" class="text-gray-700 hover:text-emerald-600 font-medium">Leaderboard</a> --}}
            <a href="/competitions-overview" class="text-gray-700 hover:text-emerald-600 font-medium">Competitions</a>
            <a href="/login" class="px-5 py-2 bg-emerald-700 text-white rounded-lg font-semibold shadow hover:bg-emerald-800">
                Sign Up
            </a>
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
                            <tbody class="text-sm">
                                @foreach ($rankings as $rank)
                                    <tr class="bg-white border-b hover:bg-gray-50 cursor-pointer competitor-row"
                                        data-name="{{ $rank['name'] ?? 'N/A' }}"
                                        data-balance="{{ $rank['balance'] ?? 0 }}"
                                        data-equity="{{ $rank['equity'] ?? 0 }}"
                                        data-profit="{{ $rank['total_profit'] ?? 0 }}"
                                        data-top-trade="{{ $rank['top_trade'] ?? 0 }}"
                                        data-account-code="{{ $rank['account_code'] ?? '' }}"
                                        data-trades="{{ $rank['total_trades'] ?? 0 }}">
                                        <td class="p-3 font-bold">
                                            <span
                                                class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-bold">
                                                {{ $rank['rank'] ?? 'N/A'}}
                                            </span>
                                        </td>
                                        <td class="p-3">{{ $rank['name'] ?? 'N/A' }}</td>
                                        <td class="p-3">${{ $rank['balance'] ?? 'N/A'}}</td>
                                        <td class="p-3">${{ $rank['equity'] ?? 'N/A'}}</td>
                                        <td class="p-3">${{ $rank['total_profit'] ?? 'N/A'}}</td>
                                        <td class="p-3">{{ $rank['total_trades'] ?? 'N/A'}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
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
                        {{-- <p class="mb-2"><strong>Starting Balance:</strong> <span id="startingBalance">${{ $rankings[0]['balance'] ?? '' }}</span></p> --}}
                        <p class="mb-2"><strong>Current Balance:</strong> <span id="currentBalance">${{ $rankings[0]['balance'] ?? '' }}</span></p>
                        <p class="mb-2"><strong>Cumulative P/L:</strong> <span id="cumulativePL">${{ $rankings[0]['total_profit'] ?? '' }}</span></p>
                        <p class="mb-2"><strong>Biggest Trade:</strong> <span id="largestTrade">${{ $rankings[0]['top_trade'] ?? '' }}</span></p>
                        <p class="mb-2"><strong>Equity:</strong> <span id="equity">${{ $rankings[0]['equity'] ?? '' }}</span></p>
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
                <span id="paginationInfo">Page 1 of 1 | Items {{ count($stats['top_performer']->trades ?? []) }}</span>
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
            <img src="/{{ $settings['admin_sidebar_logo'] }}" alt="LQH Markets Logo" class="img-fluid"
              style="max-height: 45px;">
          </a>
          <p class="text-muted mb-1">
            LQH Integrated Ltd <br> Hamchako, Mutsamudu, Autonomous Island of Anjouan, Union of Comoros.
          </p>
          <p class="mb-1">Email: <a href="mailto:support@lqhmarkets.com"
              class="text-success text-decoration-none">support@lqhmarkets.com</a></p>
          <p class="mb-0 text-muted">© 2025 LQH Markets | All rights reserved.</p>
        </div>

        <!-- Explore -->
        <div class="col-6 col-md-3 col-lg-2">
          <h6 class="fw-bold mb-3">Explore</h6>
          <ul class="list-unstyled">
            <li><a href="https://www.lqhmarkets.com/" class="text-decoration-none text-dark">Home</a></li>
            <li><a href="https://www.lqhmarkets.com/mt5" class="text-decoration-none text-dark">MetaTrader 5</a></li>
            <li><a href="https://www.lqhmarkets.com/about-us" class="text-decoration-none text-dark">About Us</a></li>
            <li><a href="https://www.lqhmarkets.com/help-center" class="text-decoration-none text-dark">Help Center</a>
            </li>
            <li><a href="https://www.lqhmarkets.com/lot-size-calculator"
                class="text-decoration-none text-dark">Lot Size Calculator</a></li>
          </ul>
        </div>

        <!-- Disclosures -->
        <div class="col-6 col-md-3 col-lg-2">
          <h6 class="fw-bold mb-3">Disclosures</h6>
          <ul class="list-unstyled">
            <li><a href="https://www.lqhmarkets.com/risk-disclaimer"
                class="text-decoration-none text-dark">Risk Disclaimer</a></li>
            <li><a href="https://www.lqhmarkets.com/terms-conditions"
                class="text-decoration-none text-dark">Terms &amp; Conditions</a></li>
            <li><a href="https://www.lqhmarkets.com/privacy-policy"
                class="text-decoration-none text-dark">Privacy Policy</a></li>
          </ul>
        </div>

        <!-- Company -->
        <div class="col-6 col-md-3 col-lg-2">
          <h6 class="fw-bold mb-3">Company</h6>
          <ul class="list-unstyled">
            <li><a href="https://www.lqhmarkets.com/about-us" class="text-decoration-none text-dark">About</a></li>
            <li><a href="https://www.lqhmarkets.com/contact-us" class="text-decoration-none text-dark">Contact</a></li>
          </ul>
        </div>

        <!-- Social Media -->
        <div class="col-6 col-md-3 col-lg-2">
          <h6 class="fw-bold mb-3">Social Media</h6>
          <ul class="list-unstyled d-flex flex-column gap-2">
            <li>
              <a href="https://discord.gg/lqhmarkets" target="_blank"
                class="d-flex align-items-center text-decoration-none text-dark">
                <img
                  src="https://cdn.prod.website-files.com/66d6faa07d7bd55c6f3ca508/683d55e6248e95183cea86a5_icons8-discord-500.png"
                  alt="Discord" class="me-2" style="height: 20px; width: 20px;">
                Discord
              </a>
            </li>
            <li>
              <a href="https://instagram.com/lqhmarkets" target="_blank"
                class="d-flex align-items-center text-decoration-none text-dark">
                <img
                  src="https://cdn.prod.website-files.com/66d6faa07d7bd55c6f3ca508/683d5538ee0b29783635919a_icons8-instagram-500.png"
                  alt="Instagram" class="me-2" style="height: 20px; width: 20px;">
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
        <p><strong>Legal:</strong> LQH Integrated Ltd is LQHMarkets.com and the LQH Markets brand and trademark is owned
          by LQH Integrated Ltd.</p>
        <p>LQH Integrated Ltd holds an International Brokerage and Clearing House License in Comoros with license number
          L15833/LIL.</p>
        <p>LQH Integrated Ltd holds a license in St. Lucia as an International Business Company with registration number
          2023-00570.</p>
        <p><strong>Risk Warning:</strong> An investment in derivatives may mean investors may lose an amount even greater
          than their original investment. Anyone wishing to invest in any of the products mentioned in
          <a href="https://www.LQHMarkets.com" class="text-success">www.LQHMarkets.com</a> should seek their own
          financial or professional advice.</p>
        <p><strong>Restricted Regions:</strong> LQH Integrated Limited does not provide services for
          citizens/residents of the United States, Cuba, Iran, Myanmar, North Korea, Sudan, China, Singapore and to
          jurisdictions on the FATF, OFAC and EU/UN sanctions lists.</p>
        <p class="mb-0">© 2025 LQH Markets. All rights reserved.</p>
      </div>
    </div>
  </div>
    <script>
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
    </script>


    <script>
        // Init Chart (only once)
        const ctx = document.getElementById('performanceChart').getContext('2d');

        let performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Balance',
                    data: [],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
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
                const name = row.dataset.name;
                const balance = row.dataset.balance;
                const equity = row.dataset.equity;
                const profit = row.dataset.profit;
                const topTrade = row.dataset.topTrade;
                const trades = row.dataset.trades;
                const account = row.dataset.accountCode;

                // Update performer card
                document.getElementById('performerName').innerText = name;
                // document.getElementById('startingBalance').innerText = balance;
                document.getElementById('currentBalance').innerText = `$${balance}`;
                document.getElementById('cumulativePL').innerText = `$${profit}`;
                document.getElementById('largestTrade').innerText = `$${topTrade}`;
                document.getElementById('equity').innerText = `$${equity}`;

                // Update Trading Log title
                document.getElementById('tradingLogTitle').innerText = `${name} - Trading Log`;
                await updateTraderData(account);
            });
        });

        async function updateTraderData(accountCode) {
            try {
                const res = await fetch(`/competitions-overview/trader-data/${accountCode}`);
                const tradesData = await res.json();
                const tbody = document.getElementById('tradingLogBody');
                tbody.innerHTML = "";


                (tradesData.trades).forEach(trade => {
                    // console.log(trade);
                    console.log(trade.open_time);
                    const openTime = toGmt(trade.open_time);
                    const closeTime = trade.close_time ? toGmt(trade.close_time) : "";
                    const tr = document.createElement('tr');
                    tr.classList.add("hover:bg-gray-50");
                    tr.innerHTML = `
                        <td class="px-4 py-3">${openTime}</td>
                        <td class="px-4 py-3">${trade.symbol}</td>
                        <td class="px-4 py-3">#${trade.position}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-semibold ${trade.type == 'buy' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100'} rounded">
                                ${trade.type}
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

                document.getElementById('paginationInfo').innerText = `Page 1 of 1 | Items ${tradesData.length}`;
                document.getElementById('lastUpdate').innerText = `Last Update: just now`;

            } catch (e) {
                console.error("Error fetching trades:", e);
            }
        }
        function formatUtc(dateString) {
            // if already like "2025-09-23 17:35:01", just return it
            if (dateString.includes("T")) {
                return dateString.replace("T", " ").replace("Z", "").split(".")[0];
            }
            return dateString; // already correct format from backend
        }
        function toGmt(dateString) {
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
