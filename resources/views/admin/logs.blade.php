@extends('layouts.admin.admin')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header">
            <h1 class="page-title">Activity Logs</h1>
        </div>
            <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                <div class="card-body">
                    {{-- <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Activity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>{!! $log->formatted_log !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    </div> --}}

                    <div class="activity-log">
                        @foreach($logs as $index => $log)
                            @php
                                // Determine user email link based on user type (Employee or Client)
                                $user_id = $log->causer_id;
                                $user = $log->causer_type == 'App\Models\EmployeeList'
                                    ? \App\Models\EmployeeList::where('id', $user_id)->first()
                                    : \App\Models\User::where('id', $user_id)->first();
                                $userLink = $user ? "<a href='/admin/client_details/{$user->id}' style='color: #007bff;'>{$user->email}</a>" : "Unknown";

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
                                                            <span>User {$userLink} withdrew \${$withdrawal_amount} using {$log->properties['remark']} with transaction ID {$transaction_id_link}</span>
                                                        </div>";
                                        break;

                                    default:
                                        $logDescription = "[{$date}] Activity recorded for {$log->description}.";
                                        break;
                                }
                            @endphp

                            <div class="activity-item d-flex align-items-center justify-content-between @if($index % 2 == 0) from-left @else from-right @endif">
                                <div class="user-info">
                                    <span class="user-email">{!! $userLink !!}</span>
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
                </div>
                </div>
            </div>
            </div>
    </div>
</div>
@endsection
