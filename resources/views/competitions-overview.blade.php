<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition List</title>

    <!-- Tailwind for main content -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap for footer -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @media (max-width: 640px) {
            .filter-btn {
                flex: 1 1 45%; /* buttons wrap nicely on small screens */
            }
            .container-fluid .row {
                text-align: left; /* center footer on mobile */
            }
        }
    </style>

</head>

<body class="font-sans bg-gray-50">

    <!-- Navbar -->
    <header class="flex flex-col items-center justify-between p-6 bg-white shadow md:flex-row">
        <div class="flex items-center justify-center mb-3 space-x-2 md:justify-start md:mb-0">
            <a href='https://www.lqhmarkets.com/'>
                <img src="/{{ $settings['admin_sidebar_logo'] }}" class="w-36 md:w-44" alt="logo">
            </a>
        </div>
        <nav class="flex flex-col items-center gap-3 md:flex-row md:space-x-6">
            <a href="/competitions-overview" class="font-medium text-gray-700 hover:text-emerald-600">Competitions</a>
            @guest
                <a href="/login" class="w-full px-5 py-2 font-semibold text-center text-white rounded-lg shadow bg-emerald-700 hover:bg-emerald-800 md:w-auto">
                    Sign Up
                </a>
            @else
                <a href="/dashboard" class="w-full px-5 py-2 font-semibold text-center text-white rounded-lg shadow bg-emerald-700 hover:bg-emerald-800 md:w-auto">
                    Dashboard
                </a>
            @endguest
        </nav>
    </header>

    <!-- Page Heading -->
    <section class="mt-10 text-center">
        <h1 class="text-4xl font-bold">Competition List</h1>
    </section>

    <!-- Filters -->
    <div class="flex flex-wrap justify-center gap-2 p-2 mt-6 md:space-x-3">
        <button id="btn-All" onclick="filterCards('All')" class="px-4 py-2 text-white rounded-lg shadow filter-btn bg-emerald-700">All</button>
        <button id="btn-Upcoming" onclick="filterCards('Upcoming')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg filter-btn hover:bg-gray-300">Upcoming</button>
        <button id="btn-InProgress" onclick="filterCards('In Progress')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg filter-btn hover:bg-gray-300">In Progress</button>
        <button id="btn-Finished" onclick="filterCards('Finished')" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg filter-btn hover:bg-gray-300">Finished</button>
    </div>

    <!-- Contest Cards -->
    <section class="grid grid-cols-1 gap-6 px-4 mt-12 sm:grid-cols-2 lg:grid-cols-4 md:gap-8 md:px-10">
        @foreach ($competitions as $competition)
            @php
                if ($competition->competition_end_date < now('UTC')) {
                    $status = 'Finished';
                } elseif ($competition->competition_start_date > now('UTC')) {
                    $status = 'Upcoming';
                } elseif ($competition->competition_start_date <= now('UTC') && $competition->competition_end_date >= now('UTC')) {
                    $status = 'In Progress';
                }
            @endphp

            <div class="p-6 text-center transition bg-white border shadow-md rounded-2xl hover:shadow-lg" data-status="{{ $status }}">
                <!-- Content -->
                <div class="{{ ($status == 'Finished') ? 'opacity-50' : '' }}">
                    @if ($status != 'Finished')
                        <img src="/assets/images/competition-trophies.svg" alt="" class="mb-6">
                    @else
                        <img src="/assets/images/trophies-gray.svg" alt="" class="mb-6">
                    @endif

                    <h2 class="mb-4 text-xl font-semibold">{{ Str::upper($competition->ac_name) }}</h2>

                    <p class="flex items-center justify-center space-x-2 text-sm text-gray-600">
                        <span>Demo</span>
                        <span class="px-2 py-1 {{ $status == 'In Progress' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-200 text-gray-700' }} text-xs font-medium rounded">
                            {{ $status }}
                        </span>
                    </p>

                    <p class="mt-4 font-bold text-gray-800">CONTESTANTS</p>
                    <p class="text-lg font-medium">{{ $competition->accounts ? $competition->accounts->count() : 0 }}</p>

                    <div class="py-4 mt-6 border bg-gray-50 rounded-xl">
                        @if ($status == 'Upcoming')
                            <p class="font-bold text-gray-800">Starts At</p>
                            <p class="mt-1 text-lg">{{ \Carbon\Carbon::parse($competition->competition_start_date)->format('F jS, Y') }}</p>
                            <p id="countdown-{{ $competition->id }}" class="mt-2 text-sm font-medium text-emerald-600"></p>
                        @elseif($status == 'In Progress')
                            <p class="font-bold text-gray-800">Finishes At</p>
                            <p class="mt-1 text-lg">{{ \Carbon\Carbon::parse($competition->competition_end_date)->format('F jS, Y') }}</p>
                            <p id="countdown-{{ $competition->id }}" class="mt-2 text-sm font-medium text-emerald-600"></p>
                        @elseif($status == 'Finished')
                            <p class="font-bold text-gray-800">Finished At</p>
                            <p class="mt-1 text-lg">{{ \Carbon\Carbon::parse($competition->competition_end_date)->format('F jS, Y') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 mt-6">
                    <button class="w-1/2 py-2 font-medium border rounded-lg border-emerald-700 text-emerald-700 hover:bg-emerald-50" onclick="openRulesModal('{{ $competition->prize }}')">Prize Pool</button>
                    <button class="w-1/2 py-2 font-medium border rounded-lg border-emerald-700 text-emerald-700 hover:bg-emerald-50" onclick="openLeaderboard('{{ $competition->id }}')">Standings</button>
                </div>

                @if ($status == 'Upcoming')
                    @guest
                        <a href="/login" class="block w-full py-2 mt-4 font-medium text-center text-white transition rounded-lg bg-emerald-600 hover:bg-emerald-700">
                            Register
                        </a>
                    @else
                        <a href="/createCompetition" class="block w-full py-2 mt-4 font-medium text-center text-white transition rounded-lg bg-emerald-600 hover:bg-emerald-700">
                            Register
                        </a>
                    @endguest
                @else
                    <button class="w-full py-2 mt-4 font-medium text-gray-700 bg-gray-300 rounded-lg cursor-not-allowed">Registration
                        Finished</button>
                @endif
            </div>
        @endforeach
    </section>

    <!-- Rules Modal -->
    <div id="rulesModal" class="fixed inset-0 z-50 items-center justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-2xl shadow-lg w-[600px] max-w-2xl p-6 relative">
            <button onclick="closeRulesModal()" class="absolute text-gray-500 top-3 right-3 hover:text-gray-700">✖</button>
            <h2 class="mb-4 text-xl font-semibold text-emerald-800">Competition Rules</h2>
            <div id="rulesContent" class="text-gray-700"></div>
        </div>
    </div>

    <!-- Footer (Bootstrap) -->
    <div class="pt-5 mt-5 container-fluid bg-light">
        <div class="px-6 py-5">
            <div class="row gy-4">

                <!-- Logo & Info -->
                <div class="col-lg-4 col-md-6">
                    <a href="/" class="mb-3 d-inline-block">
                        <img src="/{{ $settings['admin_sidebar_logo'] }}" alt="LQH Markets Logo" class="img-fluid" style="max-height: 45px;">
                    </a>
                    <p class="mb-1 text-muted">
                        <span class="notranslate">LQH Integrated Ltd</span> <br> Ground Floor, Rodney Court Building, Rodney Bay, Gros Islet, Saint Lucia.
                    </p>
                    <p class="mb-1">Email: <a href="mailto:support@lqhmarkets.com" class="text-success text-decoration-none">support@lqhmarkets.com</a></p>
                    <p class="mb-0 text-muted">© {{ date('Y') }} LQH Markets | All rights reserved.</p>
                </div>

                <!-- Explore -->
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="mb-3 fw-bold">Explore</h6>
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
                    <h6 class="mb-3 fw-bold">Disclosures</h6>
                    <ul class="list-unstyled">
                        <li><a href="https://www.lqhmarkets.com/risk-disclaimer" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">Risk Disclaimer</a></li>
                        <li><a href="https://www.lqhmarkets.com/terms-conditions" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">Terms &amp; Conditions</a></li>
                        <li><a href="https://www.lqhmarkets.com/privacy-policy" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="mb-3 fw-bold">Company</h6>
                    <ul class="list-unstyled">
                        <li><a href="https://www.lqhmarkets.com/about-us" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">About</a></li>
                        <li><a href="https://www.lqhmarkets.com/contact-us" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-dark">Contact</a></li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="mb-3 fw-bold">Social Media</h6>
                    <ul class="gap-2 list-unstyled d-flex flex-column">
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
        <div class="py-4 border-top">
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

    <!-- Scripts -->
    <script>
        function openRulesModal(prize) {
            document.getElementById("rulesContent").innerHTML = prize;
            const rulesContent = document.getElementById("rulesContent");
            rulesContent.querySelectorAll("ul").forEach(ul => {
                ul.classList.add("list-disc", "pl-5", "space-y-2");
            });
            rulesContent.querySelectorAll("li").forEach(li => {
                li.classList.add("text-gray-700");
            });
            document.getElementById("rulesModal").classList.remove("hidden");
            document.getElementById("rulesModal").classList.add("flex");
        }

        function closeRulesModal() {
            document.getElementById("rulesModal").classList.add("hidden");
            document.getElementById("rulesModal").classList.remove("flex");
        }

        function openLeaderboard(competitionId) {
            window.location.href = `/competitions-overview/leaderboard/${competitionId}`;
        }

        function filterCards(filter) {
            const cards = document.querySelectorAll("[data-status]");
            const buttons = document.querySelectorAll(".filter-btn");
            buttons.forEach(btn => {
                btn.classList.remove("bg-emerald-700", "text-white", "shadow");
                btn.classList.add("bg-gray-200", "text-gray-700", "hover:bg-gray-300");
            });
            const activeBtn = document.querySelector(`#btn-${filter.replace(" ", "")}`);
            if (activeBtn) {
                activeBtn.classList.remove("bg-gray-200", "text-gray-700", "hover:bg-gray-300");
                activeBtn.classList.add("bg-emerald-700", "text-white", "shadow");
            }
            cards.forEach(card => {
                if (filter === "All" || card.getAttribute("data-status") === filter) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            function initCountdown(targetDate, elementId, endMsg, prefix) {
                const target = new Date(targetDate + " UTC").getTime();
                let interval;

                function updateCountdown() {
                    const now = Date.now();
                    const distance = target - now;

                    if (distance <= 0) {
                    const el = document.getElementById(elementId);
                    if (el) el.innerHTML = endMsg;
                    clearInterval(interval);
                    return;
                    }

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    const el = document.getElementById(elementId);
                    if (el) el.innerHTML = `${prefix} ${days}d ${hours}h ${minutes}m ${seconds}s`;
                }

                updateCountdown();
                interval = setInterval(updateCountdown, 1000);
            }

            @foreach ($competitions as $competition)
                @php
                    if ($competition->competition_end_date < now('UTC')) {
                        $status = 'Finished';
                    } elseif ($competition->competition_start_date > now('UTC')) {
                        $status = 'Upcoming';
                    } else {
                        $status = 'In Progress';
                    }
                @endphp

                @if ($status == 'Upcoming')
                    initCountdown("{{ $competition->competition_start_date }}", "countdown-{{ $competition->id }}", "Competition Started", "Starts in:");
                @elseif ($status == 'In Progress')
                    initCountdown("{{ $competition->competition_end_date }}", "countdown-{{ $competition->id }}", "Competition Ended", "End in:");
                @else
                    initCountdown("{{ $competition->competition_end_date }}", "countdown-{{ $competition->id }}", "Competition Ended", "End in:");
                @endif
            @endforeach
        });
    </script>
</body>
</html>
