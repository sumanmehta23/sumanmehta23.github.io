<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contest List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-50 font-sans">

    <!-- Navbar -->
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

    <!-- Page Heading -->
    <section class="text-center mt-10">
        <h1 class="text-4xl font-bold">Contest List</h1>
    </section>

    <!-- Filters -->
    <div class="flex justify-center mt-6 space-x-3">
        <button id="btn-All" onclick="filterCards('All')" class="filter-btn px-4 py-2 bg-emerald-700 text-white rounded-lg shadow">All</button>
        <button id="btn-Upcoming" onclick="filterCards('Upcoming')" class="filter-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Upcoming</button>
        <button id="btn-InProgress" onclick="filterCards('In Progress')" class="filter-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">In Progress</button>
        <button id="btn-Finished" onclick="filterCards('Finished')" class="filter-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Finished</button>
    </div>

    <!-- Contest Cards -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mt-12 px-10">

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

            <div class="bg-white p-6 rounded-2xl shadow-md text-center border hover:shadow-lg transition" data-status="{{ $status }}">


                <!-- Content wrapper with conditional opacity -->
                <div class="{{ $status !== 'Upcoming' ? 'opacity-50' : '' }}">
                    @if ($status == 'Upcoming')
                        <img src="/assets/images/competition-trophies.svg" alt="" style="margin-bottom: 30px;">
                    @else
                        <img src="/assets/images/trophies-gray.svg" alt="" style="margin-bottom: 30px;">
                    @endif

                    <!-- Title -->
                    <h2 class="text-xl font-semibold" style="margin-bottom: 20px;">{{ Str::upper($competition->ac_name) }}</h2>

                    <!-- Demo + Status -->
                    <p class="text-sm text-gray-600 mt-1 flex justify-center items-center space-x-2">
                        <span>Demo</span>
                        <span class="px-2 py-1
                {{ $status == 'In Progress' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-200 text-gray-700' }}
                text-xs font-medium rounded">
                            {{ $status }}
                        </span>
                    </p>

                    <!-- Contestants -->
                    <p class="mt-4 text-gray-800 font-bold">CONTESTANTS</p>
                    <p class="text-lg font-medium">{{ $competition->accounts ? $competition->accounts->count() : 0 }}</p>

                    <!-- Date Box -->
                    <div class="bg-gray-50 border rounded-xl py-4 mt-6">
                        @if ($status == 'Upcoming')
                            <p class="text-gray-800 font-bold">Starts At</p>
                            <p class="text-lg mt-1">
                                {{ \Carbon\Carbon::parse($competition->competition_start_date)->format('F jS, Y') }}
                            </p>
                        @elseif($status == 'In Progress')
                            <p class="text-gray-800 font-bold">Finishes At</p>
                            <p class="text-lg mt-1">
                                {{ \Carbon\Carbon::parse($competition->competition_end_date)->format('F jS, Y') }}
                            </p>
                        @elseif($status == 'Finished')
                            <p class="text-gray-800 font-bold">Finished At</p>
                            <p class="text-lg mt-1">
                                {{ \Carbon\Carbon::parse($competition->competition_end_date)->format('F jS, Y') }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons (not affected by opacity) -->
                <div class="mt-6 flex gap-3">
                    <button class="w-1/2 border border-emerald-700 text-emerald-700 py-2 rounded-lg font-medium hover:bg-emerald-50" onclick="openRulesModal('{{ $competition->prize }}')">
                        Rules
                    </button>
                    <button class="w-1/2 border border-emerald-700 text-emerald-700 py-2 rounded-lg font-medium hover:bg-emerald-50" onclick="openLeaderboard('{{ $competition->id }}')">
                        Standings
                    </button>
                </div>

                <!-- Register / Finished -->
                @if ($status == 'Upcoming')
                    <a href="/login" class="w-full mt-4 block text-center bg-emerald-600 text-white py-2 rounded-lg font-medium hover:bg-emerald-700 transition">
                        Register
                    </a>
                @else
                    <button class="w-full mt-4 bg-gray-300 text-gray-700 py-2 rounded-lg font-medium cursor-not-allowed">
                        Registration Finished
                    </button>
                @endif

            </div>
        @endforeach
    </section>


    <!-- Rules Modal -->
    <div id="rulesModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
        <div class="bg-white rounded-2xl shadow-lg w-[600px] max-w-2xl p-6 relative">

            <!-- Close Button -->
            <button onclick="closeRulesModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
                ✖
            </button>

            <h2 class="text-xl font-semibold mb-4 text-emerald-800">Competition Rules</h2>
            <div id="rulesContent" class="text-gray-700">
                <!-- Prize text will be injected here -->
            </div>
        </div>
    </div>



    <script>
        function openRulesModal(prize) {
            document.getElementById("rulesContent").innerHTML = prize;

            // Apply Tailwind styles to any <ul>/<li> inside
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

        // function openLeaderboard(competitionId) {
        //     fetch(`/competitions-overview/leaderboard/${competitionId}`, {
        //         method: "GET",
        //         headers: {
        //             "X-Requested-With": "XMLHttpRequest",
        //             "Accept": "text/html"
        //         }
        //     })
        //     .then(response => response.text()) // Laravel will return Blade HTML
        //     .then(html => {
        //         // Replace the whole body with leaderboard page
        //         document.body.innerHTML = html;
        //     })
        //     .catch(error => {
        //         console.error("Error:", error);
        //     });
        // }

        function openLeaderboard(competitionId) {
            window.location.href = `/competitions-overview/leaderboard/${competitionId}`;
        }



        function closeRulesModal() {
            document.getElementById("rulesModal").classList.add("hidden");
            document.getElementById("rulesModal").classList.remove("flex");
        }
    </script>
    <script>
        function filterCards(filter) {
            const cards = document.querySelectorAll("[data-status]");
            const buttons = document.querySelectorAll(".filter-btn");

            // Reset all buttons to default (gray)
            buttons.forEach(btn => {
                btn.classList.remove("bg-emerald-700", "text-white", "shadow");
                btn.classList.add("bg-gray-200", "text-gray-700", "hover:bg-gray-300");
            });

            // Highlight the active button
            const activeBtn = document.querySelector(`#btn-${filter.replace(" ", "")}`);
            if (activeBtn) {
                activeBtn.classList.remove("bg-gray-200", "text-gray-700", "hover:bg-gray-300");
                activeBtn.classList.add("bg-emerald-700", "text-white", "shadow");
            }

            // Filter the cards
            cards.forEach(card => {
                if (filter === "All" || card.getAttribute("data-status") === filter) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        }
    </script>

</body>

</html>
