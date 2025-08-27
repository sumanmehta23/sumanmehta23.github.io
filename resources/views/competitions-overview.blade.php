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
      <img src="/{{ $settings['admin_sidebar_logo'] }}" class="img-fluid logo-lg 1 " alt="logo" style="width: 180px;">
    </div>
    <nav class="flex items-center space-x-6">
      <a href="#" class="text-gray-700 hover:text-blue-600">Leaderboard</a>
      <a href="#" class="text-gray-700 hover:text-blue-600">Competitions</a>
      <button class="px-4 py-2 bg-blue-700 text-white rounded-lg">Sign Up</button>
    </nav>
  </header>

  <!-- Page Heading -->
  <section class="text-center mt-10">
    <h1 class="text-4xl font-bold">Contest List</h1>
  </section>

  <!-- Filters -->
  <div class="flex justify-center mt-6 space-x-3">
    <button class="px-4 py-2 bg-blue-700 text-white rounded-lg">All</button>
    <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Finished</button>
    <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">In Progress</button>
  </div>

  <!-- Contest Cards -->
  <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-12 px-6">

    @foreach($competitions as $competition)
    {{-- {{ dd($competition) }} --}}
    @php
        if ($competition->competition_start_date >= now() && $competition->competition_end_date < now()){
            $status = 'In Progress';
        }
        else{
            $status = 'Finished';
        }
    @endphp

        <div class="bg-white p-6 rounded-2xl shadow-md text-center">
            <h2 class="text-xl font-semibold">{{ $competition->ac_name }}</h2>
            <p class="text-sm text-gray-600 mt-1">
                {{ $competition->ac_group }}
                <span class="ml-2 px-2 py-1
                    {{ $status == 'In Progress' ? 'bg-blue-100 text-blue-600' : 'bg-gray-200 text-gray-700' }}
                    text-xs rounded">
                    {{ $status }}
                </span>
            </p>

            <p class="mt-4 text-gray-800 font-bold">Prize Pool</p>
            <p class="text-lg">{!! $competition->prize !!}</p>

            <p class="mt-4 text-gray-800 font-bold">Contestants</p>
            <p class="text-lg">{{ $competition->account_count }}</p>

            <p class="mt-4 text-gray-800 font-bold">
                {{ $status == 'In Progress' ? 'Finishes At' : 'Finished At' }}
            </p>
            <p class="text-lg">{{ $competition->competition_end_date }}</p>

            <div class="mt-6 flex flex-col gap-3">
                <button class="border border-blue-700 text-blue-700 py-2 rounded-lg">Rules</button>
                <button class="border border-blue-700 text-blue-700 py-2 rounded-lg">Standings</button>

                @if($status == 'In Progress')
                    <button class="bg-blue-600 text-white py-2 rounded-lg">Register</button>
                @else
                    <button class="bg-gray-300 text-gray-700 py-2 rounded-lg cursor-not-allowed">
                        Registration Finished
                    </button>
                @endif
            </div>
        </div>
    @endforeach


  </section>

</body>
</html>
