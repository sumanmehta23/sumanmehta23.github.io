<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contest List</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">

  <!-- Navbar -->
  <header class="flex justify-between items-center p-6 bg-white shadow">
    <div class="flex items-center space-x-2">
      <img src="/{{ $settings['admin_sidebar_logo'] }}" class="w-44" alt="logo">
    </div>
    <nav class="flex items-center space-x-6">
      <a href="#" class="text-gray-700 hover:text-blue-600 font-medium">Leaderboard</a>
      <a href="#" class="text-gray-700 hover:text-blue-600 font-medium">Competitions</a>
      <button class="px-5 py-2 bg-blue-700 text-white rounded-lg font-semibold shadow">
        Sign Up
      </button>
    </nav>
  </header>

  <!-- Page Heading -->
  <section class="text-center mt-10">
    <h1 class="text-4xl font-bold">Contest List</h1>
  </section>

  <!-- Filters -->
  <div class="flex justify-center mt-6 space-x-3">
    <button class="px-4 py-2 bg-blue-700 text-white rounded-lg shadow">All</button>
    <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Finished</button>
    <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">In Progress</button>
  </div>

  <!-- Contest Cards -->
  <!-- Contest Cards -->
<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mt-12 px-10">

  @foreach($competitions as $competition)
  @php
      if ($competition->competition_end_date < now('UTC')) {
          $status = 'Finished';
      } elseif ($competition->competition_start_date > now('UTC')) {
          $status = 'Upcoming';
      } elseif ($competition->competition_start_date <= now('UTC') && $competition->competition_end_date >= now('UTC')) {
          $status = 'In Progress';
      }
  @endphp

  <div class="bg-white p-6 rounded-2xl shadow-md text-center border hover:shadow-lg transition">

      <!-- Title -->
      <h2 class="text-xl font-semibold">{{ $competition->ac_name }}</h2>

      <!-- Demo + Status -->
      <p class="text-sm text-gray-600 mt-1 flex justify-center items-center space-x-2">
          {{-- <span>{{ $competition->ac_group }}</span> --}}
          <span>Demo</span>
          <span class="px-2 py-1
              {{ $status == 'In Progress' ? 'bg-blue-100 text-blue-600' : 'bg-gray-200 text-gray-700' }}
              text-xs font-medium rounded">
              {{ $status }}
          </span>
      </p>

      <!-- Contestants -->
      <p class="mt-4 text-gray-800 font-bold">CONTESTANTS</p>
      <p class="text-lg font-medium">{{ $competition->accounts ? $competition->accounts->count() : 0 }}</p>

      <!-- Date Box -->
      <div class="bg-gray-50 border rounded-xl py-4 mt-6">
        @if($status == 'Upcoming')
            <p class="text-gray-800 font-bold">Starts At</p>
            <p class="text-lg mt-1">{{ $competition->competition_start_date }}</p>
        @elseif($status == 'In Progress')
            <p class="text-gray-800 font-bold">Finishes At</p>
            <p class="text-lg mt-1">{{ $competition->competition_end_date }}</p>
        @elseif($status == 'Finished')
            <p class="text-gray-800 font-bold">Finished At</p>
            <p class="text-lg mt-1">{{ $competition->competition_end_date }}</p>
        @endif
      </div>

      <!-- Action Buttons -->
      <div class="mt-6 flex gap-3">
        <button
          class="w-1/2 border border-blue-700 text-blue-700 py-2 rounded-lg font-medium hover:bg-blue-50"
          onclick="openRulesModal('{{ $competition->prize }}')"
        >
          Rules
        </button>
        <button class="w-1/2 border border-blue-700 text-blue-700 py-2 rounded-lg font-medium hover:bg-blue-50">
          Standings
        </button>
      </div>

      <!-- Register / Finished -->
      @if($status == 'Upcoming')
        <button class="w-full mt-4 bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 transition">
          Register
        </button>
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
  <div class="bg-white rounded-2xl shadow-lg w-96 p-6 relative">

    <!-- Close Button -->
    <button onclick="closeRulesModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
      ✖
    </button>

    <h2 class="text-xl font-semibold mb-4">Competition Rules</h2>
    <div id="rulesContent" class="text-gray-700">
      <!-- Prize text will be injected here -->
    </div>
  </div>
</div>

<script>
  function openRulesModal(prize) {
    document.getElementById("rulesContent").innerHTML = prize;
    document.getElementById("rulesModal").classList.remove("hidden");
    document.getElementById("rulesModal").classList.add("flex");
  }

  function closeRulesModal() {
    document.getElementById("rulesModal").classList.add("hidden");
    document.getElementById("rulesModal").classList.remove("flex");
  }
</script>


</body>
</html>
