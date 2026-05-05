@extends('layouts.crm.crm')
@section('content')
    <div class="pc-container">
        <div class="pc-content">
            @if (session('error'))
                <div class="mt-4 alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="pb-0 mb-0 page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title h2">
                                <h4 class="mb-0">Competition</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                {{-- @include('mt5_accounts_tab') --}}
                <div class="col-md-12 col-lg-9">
                    <div class="card">
                        <div class="pb-0 card-body border-bottom">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">My Competition Accounts</h5>
                                <div class="dropdown">
                                    <a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none"
                                        href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        <i class="ti ti-dots-vertical f-18"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="{{ route('showCompetitionForm') }}">Join Competition</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-content" id="myTabContent">
                            <div>
                                <div class="table-responsive ps-2">
                                    <table class="table" id="">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Competition start date</th>
                                                <th>Competition end date</th>
                                                <th>Nick Name</th>
                                                <th>Rank</th>
                                                <th>Leverage</th>
                                                <th class="text-end">Balance</th>
                                                <th class="text-end">Equity</th>
                                                <th class="text-end"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($results as $acc)
                                                <tr>
                                                    <td>
                                                        <div class="row align-items-center">
                                                            <div class="col-auto pe-0">
                                                                <img src="/assets/images/mt5.png" alt="user-image"
                                                                    class="rounded wid-50 hei-50">
                                                            </div>
                                                            <div class="col">
                                                                @if ($acc->code && $acc->code != 'Rejected')
                                                                    <h4 class="mb-2 ms-2">
                                                                        {{ $acc->code }}
                                                                    </h4>
                                                                @elseif($acc->code == 'Rejected')
                                                                    <h4 class="mb-2 ms-2 text-danger">
                                                                        {{ 'Rejected' }}
                                                                    </h4>
                                                                @else
                                                                    <h4 class="mb-2 ms-2 text-warning">
                                                                        {{ 'Pending' }}
                                                                    </h4>
                                                                @endif
                                                                {{-- <h4 class="mb-2 ms-2">
                                                                    {{ $acc->code ?? 'Pending' }}
                                                                </h4> --}}
                                                                <p class="mb-0 text-muted ms-2 f-12">
                                                                    {{-- <span class="text-truncate w-100">{{
                                                                        $acc->accountType->ac_name }}</span> --}}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="f-w-400 f-16">{{ $acc->competition_start_date }}</td>
                                                    <td class="f-w-400 f-16">{{ $acc->competition_end_date }}</td>
                                                    <td class="f-w-400 f-16">{{ $acc->account_nick_name }}</td>
                                                    {{-- {{ dump($acc->code) }} --}}
                                                    <td class="f-w-400 f-16">
                                                        {{ isset($acc->code) ? $acc->rank : 'Competition Not Started' }}
                                                    </td>
                                                    <td class="f-w-400 f-16">1:{{ $acc->leverage }}</td>
                                                    <td class="text-end f-w-400 f-16">${{ $acc->balance }}</td>
                                                    <td class="text-end f-w-400 f-16">${{ $acc->equity }}</td>
                                                    <td class="text-end f-w-200">
                                                        @if ($acc->code && $acc->code != 'Rejected')
                                                                                                    <div class="gap-2 d-flex align-items-center">
                                                                                                        <button class="btn btn-sm btn-outline-secondary d-grid me-2">
                                                                                                            <a href="{{ route('view-account-details', $acc->id) }}">
                                                                                                                <span class="">View <svg class="pc-icon">
                                                                                                                        <use xlink:href="#custom-login"></use>
                                                                                                                    </svg></span>
                                                                                                            </a>
                                                                                                        </button>
                                                                                                        {{-- <a href="{{ url('/trade-deposit') }}"
                                                                                                            class="btn btn-sm btn-outline-secondary d-grid">
                                                                                                            <span class="">Deposit <i
                                                                                                                    class="ti ti-database-import"></i></span>
                                                                                                        </a>
                                                                                                        <a href="{{ route('trade-withdrawal') }}"
                                                                                                            class="btn btn-sm btn-outline-secondary d-grid">
                                                                                                            <span class="">Withdraw <i
                                                                                                                    class="ti ti-database-import"></i></span>
                                                                                                        </a> --}}
                                                                                                        <a href="{{ route('competition.leaderboard', [
                                                                'competition_id' => $acc->accountType->id,
                                                            ])
                                                                                                                                                                                                                                     }}"
                                                                                                            class="btn btn-sm btn-outline-secondary d-grid">
                                                                                                            <span class="">View Leaderboard <i
                                                                                                                    class="ti ti-database-import"></i></span>
                                                                                                        </a>
                                                                                                    </div>
                                                        @elseif ($acc->code && $acc->code == 'Rejected')
                                                            <div class="d-flex align-items-center">
                                                                <span class="text-danger">Your request is rejected. Create your
                                                                    account again.</span>
                                                            </div>
                                                        @else
                                                            {{-- <div class="d-flex align-items-center">
                                                                <span class="text-warning">Your competition will be active on {{
                                                                    $acc->competition_start_date }}.</span>
                                                            </div> --}}
                                                            <div class="d-flex align-items-center">
                                                                <span class="text-warning">
                                                                    Your competition will be active in
                                                                    <span class="countdown"
                                                                        data-start="{{ \Carbon\Carbon::parse($acc->competition_start_date)->format('Y-m-d H:i:s') }}"
                                                                        id="countdown-{{ $acc->id }}">
                                                                    </span>
                                                                </span>
                                                            </div>

                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route('showCompetitionForm') }}">
                        <div class="card bg-primary available-balance-card">
                            <div class="p-3 card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="mb-0 text-white">Join Competition</h4>
                                    </div>
                                    <div class="avtar">
                                        <i class="ti ti-folder-plus f-20"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                // console.log('abhay');
                const accountIds = [];
                $('.rank-cell').each(function () {
                    accountIds.push($(this).data('id'));
                });
                // console.log('abhay');
                $.ajax({
                    url: '{{ route('get-account-rank') }}',
                    type: 'GET',
                    data: {
                        ids: accountIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        for (const [id, rank] of Object.entries(response)) {
                            $(`.rank-cell[data-id="${id}"]`).text(rank);
                        }
                    },
                    error: function () {
                        $('.rank-cell').text('Error');
                    }
                });
            });
        </script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                function startCountdown(element, startDate) {

                    const target = new Date(startDate + " UTC").getTime();

                    console.log(startDate);
                    console.log(target);
                    function update() {
                        const now = new Date().getTime();
                        const distance = target - now;

                        console.log(now);
                        console.log(distance);

                        if (distance <= 0) {
                            element.innerHTML = "Now!";
                            return;
                        }

                        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        element.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                    }

                    update(); // run immediately
                    setInterval(update, 1000); // update every second
                }

                // Loop through all countdown elements
                document.querySelectorAll(".countdown").forEach(el => {
                    const startDate = el.getAttribute("data-start");
                    startCountdown(el, startDate);
                });
            });
        </script>

@endsection