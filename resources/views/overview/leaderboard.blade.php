<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>August Competition - Leaderboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
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
    <div class="container-fluid">
        <!-- Page Heading -->
        <section class="text-center mt-10 mb-10">
            <h1 class="text-4xl font-bold capitalize">{{ $competition->ac_name }}</h1>
        </section>
        {{-- {{ dd($stats) }} --}}
        <!-- Header Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div class="card p-3 bg-white shadow rounded-lg">
                <p class="text-muted mb-1">Time Remaining</p>
                <h5>11:11 - 26:42</h5>
            </div>
            <div class="card p-3 bg-white shadow rounded-lg">
                <p class="text-muted mb-1">Prize Pool</p>
                <h5>Free Challenge Account</h5>
            </div>
            <div class="card p-3 bg-white shadow rounded-lg">
                <p class="text-muted mb-1">Contestants</p>
                <h5>{{ $stats['participants'] }}</h5>
            </div>
            <div class="card p-3 bg-white shadow rounded-lg">
                <p class="text-muted mb-1">Current Leader</p>
                <h5>PETER S.</h5>
            </div>
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Competition Standings -->
            <div class="bg-white shadow rounded-lg mt-6 overflow-hidden">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h5 class="card-title font-bold">Competition Standings</h5>
                        <div class="relative">
                            <input type="text" class="form-control pr-10" placeholder="Search...">
                            <span class="absolute right-3 top-2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-left text-sm font-semibold">
                                    <th class="p-3">#Rank</th>
                                    <th class="p-3">Name</th>
                                    <th class="p-3">Return %</th>
                                    <th class="p-3">Back %</th>
                                    <th class="p-3">Prize</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="p-3 font-bold">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-400 text-white text-xs font-bold">1</span>
                                    </td>
                                    <td class="p-3">Peter S.</td>
                                    <td class="p-3">68.59%</td>
                                    <td class="p-3">-</td>
                                    <td class="p-3 text-orange-500 font-semibold">1st Place - 100k Challenge</td>
                                </tr>
                                <tr class="bg-gray-50 border-b hover:bg-gray-100">
                                    <td class="p-3 font-bold">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-400 text-white text-xs font-bold">2</span>
                                    </td>
                                    <td class="p-3">Advise M.</td>
                                    <td class="p-3">60.1%</td>
                                    <td class="p-3">8.49%</td>
                                    <td class="p-3 text-gray-600">2nd Place - 50k Challenge</td>
                                </tr>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="p-3 font-bold">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-orange-400 text-white text-xs font-bold">3</span>
                                    </td>
                                    <td class="p-3">Stone S.</td>
                                    <td class="p-3">57.53%</td>
                                    <td class="p-3">11.07%</td>
                                    <td class="p-3 text-orange-500">3rd Place - 50k Challenge</td>
                                </tr>
                                <!-- Continue rows the same way -->
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
                        <div class="flex items-center space-x-2">
                            <button class="px-2 py-1 border rounded">&laquo;</button>
                            <button class="px-2 py-1 border rounded">1</button>
                            <button class="px-2 py-1 border rounded">2</button>
                            <button class="px-2 py-1 border rounded">&raquo;</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Peter S. Performance -->

            <!-- Stats -->
            <div class="bg-white shadow rounded-lg mt-6 overflow-hidden">
                <h5 class="font-bold p-4">Peter S. - Performance</h5>
                <div class="grid grid-cols-1 lg:grid-cols-12">
                    <div class="p-6 lg:col-span-3 rounded-r-none">
                        <p class="mb-2"><strong>Starting Balance:</strong> $100,000.00</p>
                        <p class="mb-2"><strong>Current Balance:</strong> $168,594.34</p>
                        <p class="mb-2"><strong>Cumulative P/L:</strong> $68,594.34</p>
                        <p class="mb-2"><strong>Largest Winning Trade:</strong> $72,584.86</p>
                        <p class="mb-2"><strong>Return %:</strong> 68.59%</p>
                        <p class="mb-2"><strong>Equity:</strong> $168,594.34</p>
                    </div>

                    <!-- Chart -->
                    <div class="lg:col-span-9 rounded-l-none">
                        <div class="card-body p-6">
                            {{-- <h5 class="card-title font-bold mb-4">Performance Chart</h5> --}}
                            <div class="chart-container h-100">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg mt-6 overflow-hidden">
            <div class="p-4 border-b">
                <h5 class="font-bold text-lg">Peter S. - Trading Log</h5>
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
                            <th class="px-4 py-3">CHANGE</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">Aug 26, 2025 @ 03:59:38</td>
                            <td class="px-4 py-3">GBPNZD</td>
                            <td class="px-4 py-3">#7782220156104734533</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded">BUY</span>
                            </td>
                            <td class="px-4 py-3">5</td>
                            <td class="px-4 py-3">2.30753</td>
                            <td class="px-4 py-3">Aug 26, 2025 @ 07:21:32</td>
                            <td class="px-4 py-3">2.30147</td>
                            <td class="px-4 py-3 text-red-600">-$1,768.63</td>
                            <td class="px-4 py-3">-0.26%</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">Aug 22, 2025 @ 12:05:06</td>
                            <td class="px-4 py-3">GBPAUD</td>
                            <td class="px-4 py-3">#7782220156104690575</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded">BUY</span>
                            </td>
                            <td class="px-4 py-3">10</td>
                            <td class="px-4 py-3">2.08891</td>
                            <td class="px-4 py-3">Aug 22, 2025 @ 14:00:29</td>
                            <td class="px-4 py-3">2.08566</td>
                            <td class="px-4 py-3 text-red-600">-$2,096.90</td>
                            <td class="px-4 py-3">-0.15%</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">Aug 19, 2025 @ 07:05:24</td>
                            <td class="px-4 py-3">GBPNZD</td>
                            <td class="px-4 py-3">#7782220156104610866</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded">BUY</span>
                            </td>
                            <td class="px-4 py-3">38</td>
                            <td class="px-4 py-3">2.28111</td>
                            <td class="px-4 py-3">Aug 20, 2025 @ 06:44:57</td>
                            <td class="px-4 py-3">2.31397</td>
                            <td class="px-4 py-3 text-green-600 font-bold">$72,854.86</td>
                            <td class="px-4 py-3">+1.44%</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">Aug 18, 2025 @ 11:28:02</td>
                            <td class="px-4 py-3">EURUSD</td>
                            <td class="px-4 py-3">#7782220156104587796</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded">SELL</span>
                            </td>
                            <td class="px-4 py-3">38</td>
                            <td class="px-4 py-3">1.16841</td>
                            <td class="px-4 py-3">Aug 18, 2025 @ 19:10:07</td>
                            <td class="px-4 py-3">1.16638</td>
                            <td class="px-4 py-3 text-green-600 font-bold">$7,714.00</td>
                            <td class="px-4 py-3">+0.17%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center p-4 text-xs text-gray-500 border-t">
                <span>Page 1 of 1 | Items 1 - 8</span>
                <span>Last Update: 5 minutes ago</span>
            </div>
        </div>
    </div>

    <script>
        // Performance Chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Aug 18', 'Aug 19', 'Aug 20', 'Aug 21', 'Aug 22', 'Aug 23'],
                datasets: [{
                    label: 'Balance',
                    data: [100000, 120000, 150000, 160000, 165000, 168594],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
                    fill: true,
                    tension: 0.1
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
                        title: {
                            display: true,
                            text: 'Balance ($)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>
