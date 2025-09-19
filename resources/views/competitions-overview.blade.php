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
</head>

<body class="bg-gray-50 font-sans">

  <!-- Navbar -->
  <header class="flex justify-between items-center p-6 bg-white shadow">
    <div class="flex items-center space-x-2">
      <img src="/{{ $settings['admin_sidebar_logo'] }}" class="w-44" alt="logo">
    </div>
    <nav class="flex items-center space-x-6">
      <a href="/competitions-overview" class="text-gray-700 hover:text-emerald-600 font-medium">Competitions</a>
      <a href="/login" class="px-5 py-2 bg-emerald-700 text-white rounded-lg font-semibold shadow hover:bg-emerald-800">
        Sign Up
      </a>
    </nav>
  </header>

  <!-- Page Heading -->
  <section class="text-center mt-10">
    <h1 class="text-4xl font-bold">Competition List</h1>
  </section>

  <!-- Filters -->
  <div class="flex justify-center mt-6 space-x-3">
    <button id="btn-All" onclick="filterCards('All')"
      class="filter-btn px-4 py-2 bg-emerald-700 text-white rounded-lg shadow">All</button>
    <button id="btn-Upcoming" onclick="filterCards('Upcoming')"
      class="filter-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Upcoming</button>
    <button id="btn-InProgress" onclick="filterCards('In Progress')"
      class="filter-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">In Progress</button>
    <button id="btn-Finished" onclick="filterCards('Finished')"
      class="filter-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Finished</button>
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

      <div class="bg-white p-6 rounded-2xl shadow-md text-center border hover:shadow-lg transition"
        data-status="{{ $status }}">
        <!-- Content -->
        <div class="{{ $status !== 'Upcoming' ? 'opacity-50' : '' }}">
          @if ($status == 'Upcoming')
            <img src="/assets/images/competition-trophies.svg" alt="" class="mb-6">
          @else
            <img src="/assets/images/trophies-gray.svg" alt="" class="mb-6">
          @endif

          <h2 class="text-xl font-semibold mb-4">{{ Str::upper($competition->ac_name) }}</h2>

          <p class="text-sm text-gray-600 flex justify-center items-center space-x-2">
            <span>Demo</span>
            <span
              class="px-2 py-1 {{ $status == 'In Progress' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-200 text-gray-700' }} text-xs font-medium rounded">
              {{ $status }}
            </span>
          </p>

          <p class="mt-4 text-gray-800 font-bold">CONTESTANTS</p>
          <p class="text-lg font-medium">{{ $competition->accounts ? $competition->accounts->count() : 0 }}</p>

          <div class="bg-gray-50 border rounded-xl py-4 mt-6">
            @if ($status == 'Upcoming')
              <p class="text-gray-800 font-bold">Starts At</p>
              <p class="text-lg mt-1">{{ \Carbon\Carbon::parse($competition->competition_start_date)->format('F jS, Y') }}</p>
              <p id="countdown-{{ $competition->id }}" class="text-sm text-emerald-600 font-medium mt-2"></p>
            @elseif($status == 'In Progress')
              <p class="text-gray-800 font-bold">Finishes At</p>
              <p class="text-lg mt-1">{{ \Carbon\Carbon::parse($competition->competition_end_date)->format('F jS, Y') }}</p>
            @elseif($status == 'Finished')
              <p class="text-gray-800 font-bold">Finished At</p>
              <p class="text-lg mt-1">{{ \Carbon\Carbon::parse($competition->competition_end_date)->format('F jS, Y') }}</p>
            @endif
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex gap-3">
          <button class="w-1/2 border border-emerald-700 text-emerald-700 py-2 rounded-lg font-medium hover:bg-emerald-50"
            onclick="openRulesModal('{{ $competition->prize }}')">Rules</button>
          <button class="w-1/2 border border-emerald-700 text-emerald-700 py-2 rounded-lg font-medium hover:bg-emerald-50"
            onclick="openLeaderboard('{{ $competition->id }}')">Standings</button>
        </div>

        @if ($status == 'Upcoming')
          <a href="/login"
            class="w-full mt-4 block text-center bg-emerald-600 text-white py-2 rounded-lg font-medium hover:bg-emerald-700 transition">
            Register
          </a>
        @else
          <button
            class="w-full mt-4 bg-gray-300 text-gray-700 py-2 rounded-lg font-medium cursor-not-allowed">Registration
            Finished</button>
        @endif
      </div>
    @endforeach
  </section>

  <!-- Rules Modal -->
  <div id="rulesModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-2xl shadow-lg w-[600px] max-w-2xl p-6 relative">
      <button onclick="closeRulesModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">✖</button>
      <h2 class="text-xl font-semibold mb-4 text-emerald-800">Competition Rules</h2>
      <div id="rulesContent" class="text-gray-700"></div>
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

      @if ($status == 'Upcoming')
        (function() {
          const startDate = new Date("{{ \Carbon\Carbon::parse($competition->competition_start_date)->toIso8601ZuluString() }}").getTime();
          const elementId = "countdown-{{ $competition->id }}";

          let interval;

          function updateCountdown() {
            const now = Date.now(); // UTC in ms
            const distance = startDate - now;

            console.log("startDate", startDate);
            console.log("endDate", endDate);
            console.log("now", now);

            if (distance <= 0) {
              const el = document.getElementById(elementId);
              if (el) el.innerHTML = "Competition Started";
              clearInterval(interval);
              return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const el = document.getElementById(elementId);
            if (el) {
              el.innerHTML = `Starts in: ${days}d ${hours}h ${minutes}m ${seconds}s`;
            }
          }

          updateCountdown();
          interval = setInterval(updateCountdown, 1000);
        })();
      @endif
    @endforeach
  });
</script>





</body>
</html>
