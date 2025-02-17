@extends('layouts.admin.admin')

@section('content')
<style>
.activity-log {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.activity-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    animation: fadeIn 1s ease-in-out;
}

.activity-item.from-left {
    animation: fadeInLeft 1s ease-in-out;
}

.activity-item.from-right {
    animation: fadeInRight 1s ease-in-out;
}

.user-info {
    flex: 1;
    text-align: left;
}

.log-time {
    position: relative;
    text-align: center;
}

.log-circle-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.log-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background-color: #0c5f62;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 14px;
}

.log-circle-time {
    margin-top: 5px;
    font-size: 12px;
    color: #777;
}

.log-description {
    flex: 3;
    padding-left: 20px;
}

.log-card {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.user-email {
    font-weight: bold;
    color: #007bff;
}

.log_success {
    color: green;
}

.log_warning {
    color: orange;
}

.log_danger {
    color: red;
}

@keyframes fadeInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .activity-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .log-time {
        margin: 10px 0;
    }

    .log-description {
        padding-left: 0;
        padding-top: 10px;
    }

    .log-card {
        width: 100%;
    }
}
.pagination .page-item .page-link {
    color: #007bff;
    border: 1px solid #ddd;
    padding: 8px 12px;
    margin: 2px;
    transition: 0.3s;
}
.pagination .page-item .page-link:hover {
    background-color: #007bff;
    color: white;
}
.pagination .page-item.active .page-link {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

</style>
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header">
            <h1 class="page-title">Activity Logs</h1>
        </div>
            <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                <div class="card-body">
                    <div class="activity-log justify-content-center">
                        @foreach($logs as $index => $log)
                            @php
                                // Determine user email link based on user type (Employee or Client)
                                $user_id = $log->causer_id;
                                $user = $log->causer_type == 'App\Models\EmployeeList'
                                    ? \App\Models\EmployeeList::where('id', $user_id)->first()
                                    : \App\Models\User::where('id', $user_id)->first();
                                $userLink = $user ? "<a href='/admin/client_details/{$user->id}' style='color: #007bff;'>{$user->email}</a>" : "Unknown";

                                if (isset($log->properties['code']) && $log->properties['code'] != 'Pending' ) {
                                    $client_account = \App\Models\Account::where('code', $log->properties['code'])->first();
                                    // dd($client_account);
                                    $account = "<a href='/admin/view_account_details/{$client_account->id}' style='color: #007bff;'>{$log->properties['code']}</a>";
                                }

                                if (isset($log->properties['to']) && isset($log->properties['from'])) {
                                    $to_account = \App\Models\Account::where('code', $log->properties['to'])->first();
                                    $from_account = \App\Models\Account::where('code', $log->properties['from'])->first();
                                    // dd($client_account);
                                    $toAccount_url = "<a href='/admin/view_account_details/{$to_account->id}' style='color: #007bff;'>{$log->properties['to']}</a>";
                                    $fromAccount_url = "<a href='/admin/view_account_details/{$from_account->id}' style='color: #007bff;'>{$log->properties['from']}</a>";
                                }

                                // Extract the date and time
                                $date = $log->created_at->format('Y-m-d');
                                $time = $log->created_at->format('H:i:s');

                                // Handle special cases for specific activity types
                                $logDescription = '';
                                switch ($log->properties['remark'] ?? '') {
                                    case 'Login':
                                        $logDescription = "<div class='log_success'>
                                                            <span>User {$userLink} Logged in</span>
                                                        </div>";
                                        break;

                                    case 'Logout':
                                        $logDescription = "<div class='log_danger'>
                                                            <span>User {$userLink} Logged out</span>
                                                        </div>";
                                        break;

                                    case 'Incorrect login details':
                                        $logDescription = "<div class='log_warning'>
                                                            <span>User {$userLink} entered wrong login details</span>
                                                        </div>";
                                        break;
                                    case 'Invalid email or unverified account':
                                        $logDescription = "<div class='log_warning'>
                                                            <span style='color: #dc3545;'>User {$userLink} entered wrong email details</span>
                                                        </div>";
                                        break;
                                    case 'Switch To User':
                                        $logDescription = "<div class='log_warning'>
                                                            <span style='color: #28a745;'>User {$userLink} switched to <a href='/admin/client_details/{$log->properties['client_user_id']}' style='color: #007bff;'>{$log->properties['client_email']}</a></span>
                                                        </div>";
                                        break;
                                    case 'Update Client Email':
                                        $logDescription = "<div class='log_warning'>
                                                            <span style='color: #28a745;'>User {$userLink} entered wrong email details</span>
                                                        </div>";
                                        break;

                                    case 'Create Demo Account':
                                        $accountLink = "<a href='/admin/view_account_details/{$log->properties['code']}' style='color: #28a745;'>{$log->properties['code']}</a>";
                                        $logDescription = "<div class='log_success'>
                                                            <span>User {$userLink} created Demo account {$accountLink} with amount {$log->properties['amount']} and leverage {$log->properties['leverage']}</span>
                                                        </div>";
                                        break;

                                    case 'Create Live Account':
                                        $accountLink = "<a href='/admin/view_account_details/{$log->properties['code']}' style='color: #28a745;'>{$log->properties['code']}</a>";
                                        if ($log->properties['code'] == 'Pending') {
                                            $logDescription = "<div class='log_warning'>
                                                                <span>User {$userLink} sent request for live account with leverage: {$log->properties['leverage']}</span>
                                                            </div>";
                                        } else {
                                            $logDescription = "<div class='log_warning'>
                                                                <span>Live account {$accountLink} issued by {$user->email} with leverage {$log->properties['leverage']}</span>
                                                            </div>";
                                        }
                                        break;

                                    case 'Wallet Withdraw':
                                        $withdrawal_amount = $log->properties['withdraw_amount'] + $log->properties['withdraw_transaction_fee'];
                                        $transaction_id_link = "<a href='/admin/wallet_withdrawal_details/?id={$log->properties['wallet_withdraw_id']}&email={$log->properties['email']}&deposit={$log->properties['withdraw_amount']}' style='color: #dc3545;'>{$log->properties['wallet_withdraw_id']}</a>";
                                        $logDescription = "<div class='log_warning'>
                                                            <span>User {$userLink} send request of \${$withdrawal_amount} using {$log->properties['remark']} with transaction ID {$transaction_id_link}</span>
                                                        </div>";
                                        break;
                                    case 'Reject Wallet Withdraw':
                                        $withdrawal_amount = $log->properties['approved_amount'];
                                        $transaction_id_link = "<a href='/admin/wallet_withdrawal_details/?id={$log->properties['transaction_id']}&email={$log->properties['client_email']}&deposit={$log->properties['approved_amount']}'  style='color: #007bff;'>{$log->properties['transaction_id']}</a>";
                                        $logDescription = "<div class='log_warning'>
                                                            <span style='color: #dc3545;'>User {$userLink} {$log->properties['remark']} of \${$withdrawal_amount}, having transaction ID {$transaction_id_link}</span>
                                                        </div>";
                                        break;
                                    case 'Approve Wallet Withdraw':
                                        $withdrawal_amount = $log->properties['approved_amount'];
                                        $transaction_id_link = "<a href='/admin/wallet_withdrawal_details/?id={$log->properties['transaction_id']}&email={$log->properties['client_email']}&deposit={$log->properties['approved_amount']}' style='color: #007bff;'>{$log->properties['transaction_id']}</a>";
                                        $logDescription = "<div class='log_warning'>
                                                            <span style='color: #28a745;'> User {$userLink} {$log->properties['remark']} of \${$withdrawal_amount}, having transaction ID {$transaction_id_link}</span>
                                                        </div>";
                                        break;
                                    case 'Manually Approved Wallet Withdraw':
                                        $withdrawal_amount = $log->properties['approved_amount'];
                                        $transaction_id_link = "<a href='/admin/wallet_withdrawal_details/?id={$log->properties['transaction_id']}&email={$log->properties['client_email']}&deposit={$log->properties['approved_amount']}' style='color: #007bff;'>{$log->properties['transaction_id']}</a>";
                                        $logDescription = "<div class='log_warning'>
                                                            <span style='color: #28a745;'> User {$userLink} {$log->properties['remark']} of \${$withdrawal_amount}, having transaction ID {$transaction_id_link}</span>
                                                        </div>";
                                        break;
                                    case 'Wallet Withdraw Cancel By Client':
                                        $withdrawal_amount = $log->properties['amount'];
                                        $logDescription = "<div class='log_warning'>
                                                            <span style='color: #dc3545;'>{$log->properties['remark']} {$userLink} having amount  \${$withdrawal_amount}.</span>
                                                        </div>";
                                        break;
                                    case 'Account Withdraw':
                                        $withdrawal_amount = $log->properties['withdraw_amount'];
                                        $logDescription = "<div class='log_warning'>
                                                            <span style='color: #28a745;'>User {$userLink} withdraw  \${$withdrawal_amount} from account {$account}.</span>
                                                        </div>";
                                        break;
                                    case 'Account Deposit':
                                        $withdrawal_amount = $log->properties['deposit_amount'];
                                        $logDescription = "<div class='log_warning'>
                                                            <span style='color: #28a745;'>User {$userLink} deposit  \${$withdrawal_amount} to account {$account}.</span>
                                                        </div>";
                                        break;
                                    case 'Internal Transfer':
                                        $transfer_amount = $log->properties['transfer_amount'];
                                        $logDescription = "<div class='log_warning'>
                                                            <span style='color: #28a745;'>User {$userLink} internal transfer  \${$transfer_amount} from account {$fromAccount_url} to {$toAccount_url}.</span>
                                                        </div>";
                                        break;

                                    default:
                                        $logDescription = "[{$date}] Activity recorded for {$log->description}.";
                                        break;
                                }
                            @endphp

                            <div class="activity-item d-flex align-items-center justify-content-between @if($index % 2 == 0) from-left @else from-right @endif">
                                <div class="user-info" style="text-align: right; padding-right:20px ">
                                    <span class="user-email ">{!! $userLink !!}</span>
                                </div>
                                <div class="log-time">
                                    <div class="log-circle-wrapper log-circle">
                                        <div style="font-size: 12px">{{ $date }}</div>
                                        <div style="font-size: 12px">{{ $time }}</div>
                                    </div>
                                </div>
                                <div class="log-description">
                                    <div class="log-card">
                                        {!! $logDescription !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        <nav>
                            <ul class="pagination">
                                {{-- Previous Page Link --}}
                                @if ($logs->onFirstPage())
                                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $logs->previousPageUrl() }}" aria-label="Previous">
                                            <span aria-hidden="true">&laquo; Previous</span>
                                        </a>
                                    </li>
                                @endif

                                {{-- Page Numbers --}}
                                @foreach ($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                                    <li class="page-item {{ $page == $logs->currentPage() ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($logs->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $logs->nextPageUrl() }}" aria-label="Next">
                                            <span aria-hidden="true">Next &raquo;</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                                @endif
                            </ul>
                        </nav>
                    </div>


                </div>
                </div>
            </div>
            </div>
    </div>
</div>
@endsection
