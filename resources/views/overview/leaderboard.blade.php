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
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card p-3">
                    <p class="text-muted mb-1">Time Remaining</p>
                    <h5>11:11 - 26:42</h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3">
                    <p class="text-muted mb-1">Prize Pool</p>
                    <h5>Free Challenge Account</h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3">
                    <p class="text-muted mb-1">Contestants</p>
                    <h5>{{ $stats['participants'] }}</h5>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3">
                    <p class="text-muted mb-1">Current Leader</p>
                    <h5>PETER S.</h5>
                </div>
            </div>
        </div>

        <!-- Competition Standings -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Competition Standings</h5>
                <div class="input-group mb-3" style="max-width: 300px;">
                    <input type="text" class="form-control" placeholder="Search">
                    <button class="btn btn-outline-secondary" type="button">Search</button>
                </div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#Rank</th>
                            <th>Name</th>
                            <th>Return %</th>
                            <th>Back %</th>
                            <th>Prize</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Peter S.</td>
                            <td>68.59%</td>
                            <td>-</td>
                            <td>1st Place - 100% Challenge</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Admin M.</td>
                            <td>6.11%</td>
                            <td>8.8%</td>
                            <td>2nd Place - 50% Challenge</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Store S.</td>
                            <td>57.53%</td>
                            <td>11.12%</td>
                            <td>3rd Place - 25% Challenge</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Trader L.</td>
                            <td>52.11%</td>
                            <td>16.6%</td>
                            <td>4th Place - 10% Challenge</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Mata M.</td>
                            <td>50.34%</td>
                            <td>18.8%</td>
                            <td>5th Place - 10% Challenge</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Ashkay B.</td>
                            <td>41.12%</td>
                            <td>25.4%</td>
                            <td>6th Place - 10% Challenge</td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>Vind R.</td>
                            <td>41.26%</td>
                            <td>25.4%</td>
                            <td>7th Place - 10% Challenge</td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>Fazi U.</td>
                            <td>41.7%</td>
                            <td>25.4%</td>
                            <td>8th Place - 10% Challenge</td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td>Maza H.</td>
                            <td>40.7%</td>
                            <td>27.3%</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>Norman K.</td>
                            <td>38.27%</td>
                            <td>30.3%</td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
                <p class="text-muted">Last Update: 2 minutes ago</p>
            </div>
        </div>

        <!-- Peter S. Performance -->
        <div class="row">
            <div class="col-md-4">
                <div class="card p-3">
                    <h5 class="card-title">Peter S. - Performance</h5>
                    <p><strong>Starting Balance:</strong> $100,000.00</p>
                    <p><strong>Current Balance:</strong> $168,594.34</p>
                    <p><strong>Cumulative P/L:</strong> $68,594.34</p>
                    <p><strong>Largest Winning Trade:</strong> $72,584.86</p>
                    <p><strong>Return %:</strong> 68.59%</p>
                    <p><strong>Equity:</strong> $168,594.34</p>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Performance Chart</h5>
                        <div class="chart-container">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Peter S. Trading Log -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Peter S. - Trading Log</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Open Time (UTC)</th>
                            <th>Symbol</th>
                            <th>Position ID</th>
                            <th>Type</th>
                            <th>Volume</th>
                            <th>Open Price</th>
                            <th>Close Time (UTC)</th>
                            <th>Close Price</th>
                            <th>Profit</th>
                            <th>Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Aug 20, 2025 09:58</td>
                            <td>GBPNZD</td>
                            <td>#7822206101473433</td>
                            <td>BUY</td>
                            <td>5</td>
                            <td>2.3073</td>
                            <td>Aug 20, 2025 09:32</td>
                            <td>2.31047</td>
                            <td>$31,684.83</td>
                            <td>-0.2%</td>
                        </tr>
                        <tr>
                            <td>Aug 23, 2025 12:56</td>
                            <td>GBPAUD</td>
                            <td>#7822206101470575</td>
                            <td>SELL</td>
                            <td>10</td>
                            <td>2.0881</td>
                            <td>Aug 23, 2025 14:20</td>
                            <td>2.09556</td>
                            <td>-$2,096.40</td>
                            <td>-0.4%</td>
                        </tr>
                        <tr>
                            <td>Aug 22, 2025 12:34</td>
                            <td>GBPAUD</td>
                            <td>#7822206101470568</td>
                            <td>BUY</td>
                            <td>10</td>
                            <td>2.0889</td>
                            <td>Aug 22, 2025 12:48</td>
                            <td>2.08754</td>
                            <td>$467.72</td>
                            <td>-0.1%</td>
                        </tr>
                        <tr>
                            <td>Aug 21, 2025 09:34</td>
                            <td>USDCHF</td>
                            <td>#7822206101470463</td>
                            <td>SELL</td>
                            <td>10</td>
                            <td>0.85019</td>
                            <td>Aug 21, 2025 11:36</td>
                            <td>0.80464</td>
                            <td>$51,952.23</td>
                            <td>-1.5%</td>
                        </tr>
                        <tr>
                            <td>Aug 19, 2025 07:52</td>
                            <td>GBPNZD</td>
                            <td>#7822206101470866</td>
                            <td>BUY</td>
                            <td>38</td>
                            <td>2.1811</td>
                            <td>Aug 20, 2025 00:47</td>
                            <td>2.3137</td>
                            <td>$72,584.86</td>
                            <td>1.4%</td>
                        </tr>
                        <tr>
                            <td>Aug 18, 2025 19:10</td>
                            <td>EURUSD</td>
                            <td>#7822206101470284</td>
                            <td>BUY</td>
                            <td>38</td>
                            <td>1.1641</td>
                            <td>Aug 19, 2025 09:37</td>
                            <td>1.1676</td>
                            <td>-$2,070.90</td>
                            <td>-0.5%</td>
                        </tr>
                    </tbody>
                </table>
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
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        title: { display: true, text: 'Balance ($)' }
                    },
                    x: {
                        title: { display: true, text: 'Date' }
                    }
                }
            }
        });
    </script>
</body>
</html>
