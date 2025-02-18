@extends('layouts.admin.admin')

@section('content')
<style>
.activity-log {
    display: flex;
    flex-direction: column;
    gap: 20px;
    position: relative;
}

.activity-item {
    display: flex;
    justify-content: left;
    align-items: flex-start;
    position: relative;
}

.log-time {
    display: inline-block;
    width: 10%;
    text-align: center;
    font-size: 14px;
    color: #6c757d;
    /* padding: 8px; */
    border-radius: 5px;
    background-color: #f1f1f1;
    margin-right: 15px;
}
.log-ip {
    display: inline-block;
    width: 18%;
    text-align: center;
    font-size: 14px;
    color: #6c757d;
    /* padding: 17px; */
    border-radius: 5px;
    background-color: #f1f1f1;
    margin-right: 15px;
}

.log-description {
    flex: 1;
}

.log-card {
    background-color: #f8f9fa;
    /* padding: 15px; */
    border-radius: 5px;
    display: flex;
    flex-direction: column;
}

.log-card-header {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 10px;
}

.log-card-body {
    margin-top: 10px;
}

.pagination {
    margin: 0;
}

.page-item {
    display: inline-block;
}

.page-link {
    padding: 0.5rem 0.75rem;
    border: 1px solid #ddd;
    margin-left: -1px;
}

.page-item.active .page-link {
    background-color: #003e40;
    border-color: #003e40;
    color: #fff;
}

.page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
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
                        {{-- {{ dd($log) }} --}}
                            @php
                                $user_id = $log->causer_id;

                                $user = $log->causer_type == 'App\Models\EmployeeList'
                                            ? \App\Models\EmployeeList::where('id', $user_id)->first()
                                            : \App\Models\User::where('id', $user_id)->first();

                                        if ($log->causer_type == 'App\Models\EmployeeList') {
                                            $userLink = $user ? $user->email : "Unknown";
                                        } else {
                                            $userLink = $user ? "<a href='/admin/client_details/{$user->id}' style='color: #007bff;'>{$user->email}</a>" : "Unknown";
                                        }

                                if (isset($log->properties['code']) && $log->properties['code'] != 'Pending' ) {
                                    $client_account = \App\Models\Account::withTrashed()->where('code', $log->properties['code'])->first();
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
                                $ip =   $log->properties['ip'];

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
                                        $logDescription = "<div class=''>
                                                            <span>User {$userLink} entered wrong login details</span>
                                                        </div>";
                                        break;
                                    case 'Too many requests':
                                        $logDescription = "<div class=''>
                                                            <span>Too many login requests for User {$userLink}.</span>
                                                        </div>";
                                        break;
                                    case 'Invalid email or unverified account':
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} entered wrong email details</span>
                                                        </div>";
                                        break;
                                    case 'Switch To User':
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} switched to <a href='/admin/client_details/{$log->properties['client_user_id']}' style='color: #007bff;'>{$log->properties['client_email']}</a></span>
                                                        </div>";
                                        break;
                                    case 'Update Client Email':
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} entered wrong email details</span>
                                                        </div>";
                                        break;

                                    case 'Create Demo Account':
                                        $logDescription = "<div class='log_success'>
                                                            <span>User {$userLink} created Demo account {$account} with amount {$log->properties['amount']} and leverage {$log->properties['leverage']}</span>
                                                        </div>";
                                        break;

                                    case 'Create Live Account':

                                        if ($log->properties['code'] == 'Pending') {
                                            $logDescription = "<div class=''>
                                                                <span>User {$userLink} sent request for live account with leverage: {$log->properties['leverage']}</span>
                                                            </div>";
                                        } else {
                                            $code = $log->properties['code'];
                                            $account_data = \App\Models\Account::withTrashed()->where('code', $code)->first();
                                            $client = \App\Models\User::where('id', $account_data->user_id)->first();
                                            $client_url = "<a href='/admin/client_details/{$client->id}' style='color: #007bff;'>{$client->email}</a>";
                                            $logDescription = "<div class=''>
                                                                <span>Live account {$account} issued to user {$client_url} by {$user->email} with leverage {$log->properties['leverage']}</span>
                                                            </div>";
                                        }
                                        break;

                                    case 'Wallet Withdraw':
                                        $withdrawal_amount = $log->properties['withdraw_amount'] + $log->properties['withdraw_transaction_fee'];
                                        $transaction_id_link = "<a href='/admin/wallet_withdrawal_details/?id={$log->properties['wallet_withdraw_id']}&email={$log->properties['email']}&deposit={$log->properties['withdraw_amount']}' style='color: #007bff;'>{$log->properties['wallet_withdraw_id']}</a>";
                                        $logDescription = "<div class=''>
                                                            <span>User {$userLink} send request of \${$withdrawal_amount} using {$log->properties['remark']} with transaction ID {$transaction_id_link}</span>
                                                        </div>";
                                        break;
                                    case 'Reject Wallet Withdraw':
                                        $withdrawal_amount = $log->properties['approved_amount'];
                                        $transaction_id_link = "<a href='/admin/wallet_withdrawal_details/?id={$log->properties['transaction_id']}&email={$log->properties['client_email']}&deposit={$log->properties['approved_amount']}'  style='color: #007bff;'>{$log->properties['transaction_id']}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} {$log->properties['remark']} of \${$withdrawal_amount}, having transaction ID {$transaction_id_link}</span>
                                                        </div>";
                                        break;
                                    case 'Approve Wallet Withdraw':
                                        $withdrawal_amount = $log->properties['approved_amount'];
                                        $transaction_id_link = "<a href='/admin/wallet_withdrawal_details/?id={$log->properties['transaction_id']}&email={$log->properties['client_email']}&deposit={$log->properties['approved_amount']}' style='color: #007bff;'>{$log->properties['transaction_id']}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''> User {$userLink} {$log->properties['remark']} of \${$withdrawal_amount}, having transaction ID {$transaction_id_link}</span>
                                                        </div>";
                                        break;
                                    case 'Manually Approved Wallet Withdraw':
                                        $withdrawal_amount = $log->properties['approved_amount'];
                                        $transaction_id_link = "<a href='/admin/wallet_withdrawal_details/?id={$log->properties['transaction_id']}&email={$log->properties['client_email']}&deposit={$log->properties['approved_amount']}' style='color: #007bff;'>{$log->properties['transaction_id']}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''> User {$userLink} {$log->properties['remark']} of \${$withdrawal_amount}, having transaction ID {$transaction_id_link}</span>
                                                        </div>";
                                        break;
                                    case 'Wallet Withdraw Cancel By Client':
                                        $withdrawal_amount = $log->properties['amount'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>{$log->properties['remark']} {$userLink} having amount  \${$withdrawal_amount}.</span>
                                                        </div>";
                                        break;
                                    case 'Account Withdraw':
                                        $withdrawal_amount = $log->properties['withdraw_amount'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} withdraw  \${$withdrawal_amount} from account {$account}.</span>
                                                        </div>";
                                        break;
                                    case 'Account Deposit':
                                        $withdrawal_amount = $log->properties['deposit_amount'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} deposit  \${$withdrawal_amount} to account {$account}.</span>
                                                        </div>";
                                        break;
                                    case 'Internal Transfer':
                                        $transfer_amount = $log->properties['transfer_amount'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} internal transfer  \${$transfer_amount} from account {$fromAccount_url} to {$toAccount_url}.</span>
                                                        </div>";
                                        break;
                                    case 'Created New Wallet Address':
                                        $wallet_name = $log->properties['wallet_name'];
                                        $wallet_address = $log->properties['wallet_address'];
                                        $wallet_network = $log->properties['wallet_network'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User ({$userLink}) created new wallet ( {$wallet_name} ) having address ({$wallet_address}) on network ({$wallet_network}). Address is not verified yet.</span>
                                                        </div>";
                                        break;
                                    case 'Verified New Wallet Address':
                                        $wallet_name = $log->properties['wallet_name'];
                                        $wallet_address = $log->properties['wallet_address'];
                                        $wallet_network = $log->properties['wallet_network'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User ({$userLink}) verified new wallet ( {$wallet_name} ) having address ({$wallet_address}) on network ({$wallet_network}).</span>
                                                        </div>";
                                        break;
                                    case 'Edit Wallet Details':
                                        $wallet_name = $log->properties['wallet_name'];
                                        $wallet_address = $log->properties['wallet_address'];
                                        $wallet_network = $log->properties['wallet_network'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User ({$userLink}) updated to wallet ( {$wallet_name} ) having address ({$wallet_address}) on network ({$wallet_network}).</span>
                                                        </div>";
                                        break;
                                    case 'Verify Wallet Deletion':
                                        $wallet_name = $log->properties['wallet_name'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User ({$userLink}) send verification email to delete wallet ( {$wallet_name} ).</span>
                                                        </div>";
                                        break;
                                    case 'Wallet Deleted':
                                        $wallet_name = $log->properties['wallet_name'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User ({$userLink}) deleted wallet ( {$wallet_name} ).</span>
                                                        </div>";
                                        break;
                                    case 'Update Client Password':
                                        $new_passowrd = $log->properties['new_passowrd'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User ({$userLink}) updated password to ( {$new_passowrd} ).</span>
                                                        </div>";
                                        break;
                                    case 'Commission Transfer':
                                        $deposit_amount = $log->properties['deposit_amount'];
                                        $code = $log->properties['code'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User ({$userLink}) transfer comission of \${$deposit_amount} to {$account}.</span>
                                                        </div>";
                                        break;
                                    case 'Update Referral':
                                        $new = $log->properties['new'];
                                        $old = $log->properties['old'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User ({$userLink}) updated referral code from {$old} to {$new}.</span>
                                                        </div>";
                                        break;
                                    case 'Update Client Status':
                                        $client_id = $log->properties['client_id'];
                                        $client = \App\Models\User::where('id', $client_id)->first();
                                        $client_url = "<a href='/admin/client_details/{$client->id}' style='color: #007bff;'>{$client->email}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} updated client {$client_url} status.</span>
                                                        </div>";
                                        break;
                                    case 'Client Email Confirmation':
                                        $client_id = $log->properties['send_to'];
                                        $client = \App\Models\User::where('id', $client_id)->first();
                                        $client_url = "<a href='/admin/client_details/{$client->id}' style='color: #007bff;'>{$client->email}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} send Email Confirmation mail to {$client_url}.</span>
                                                        </div>";
                                        break;
                                    case 'Update Client Details':
                                        $client_id = $log->properties['send_to'];
                                        $client = \App\Models\User::where('id', $client_id)->first();
                                        $client_url = "<a href='/admin/client_details/{$client->id}' style='color: #007bff;'>{$client->email}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} updated {$client_url} details.</span>
                                                        </div>";
                                        break;
                                    case 'Delete Account':
                                        $client_id = $log->properties['client_id'];
                                        $code = $log->properties['code'];
                                        $client = \App\Models\User::where('id', $client_id)->first();
                                        $client_url = "<a href='/admin/client_details/{$client->id}' style='color: #007bff;'>{$client->email}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} deleted client {$client_url} account {$code} .</span>
                                                        </div>";
                                        break;
                                    case 'CRM Deposit':
                                        $client_id = $log->properties['client_id'];
                                        $deposit_amount = $log->properties['deposit_amount'];
                                        $client = \App\Models\User::where('id', $client_id)->first();
                                        $client_url = "<a href='/admin/client_details/{$client->id}' style='color: #007bff;'>{$client->email}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} deposited \${$deposit_amount} to account {$account} of user {$client_url}.</span>
                                                        </div>";
                                        break;
                                    case 'CRM Withdraw':
                                        $client_id = $log->properties['client_id'];
                                        $withdrawal_amount = $log->properties['withdrawal_amount'];
                                        $client = \App\Models\User::where('id', $client_id)->first();
                                        $client_url = "<a href='/admin/client_details/{$client->id}' style='color: #007bff;'>{$client->email}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} withdraw \${$withdrawal_amount} from account {$account} of user {$client_url}.</span>
                                                        </div>";
                                        break;
                                    case 'CRM Credit Bonus':
                                        $client_id = $log->properties['client_id'];
                                        $bonus_amount = $log->properties['bonus_amount'];
                                        $bonus_type = $log->properties['bonus_type'];
                                        $client = \App\Models\User::where('id', $client_id)->first();
                                        $client_url = "<a href='/admin/client_details/{$client->id}' style='color: #007bff;'>{$client->email}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} {$bonus_type} \${$bonus_amount} to account {$account} of user {$client_url}.</span>
                                                        </div>";
                                        break;
                                    case 'CRM Deposit Bonus':
                                        $client_id = $log->properties['client_id'];
                                        $bonus_amount = $log->properties['bonus_amount'];
                                        $bonus_type = $log->properties['bonus_type'];
                                        $client = \App\Models\User::where('id', $client_id)->first();
                                        $client_url = "<a href='/admin/client_details/{$client->id}' style='color: #007bff;'>{$client->email}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''>User  {$userLink} {$bonus_type} \${$bonus_amount} from account {$account} of user {$client_url}.</span>
                                                        </div>";
                                        break;
                                    case 'CRM Update Investor Password':
                                        $code = $log->properties['code'];
                                        $account_data = \App\Models\Account::where('code', $code)->first();
                                        $client = \App\Models\User::where('id', $account_data->user_id)->first();
                                        $client_url = "<a href='/admin/client_details/{$client->id}' style='color: #007bff;'>{$client->email}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''>User  {$userLink} updated account investor password of user {$client_url} having account {$account}.</span>
                                                        </div>";
                                        break;
                                    case 'CRM Update Master Password':
                                        $code = $log->properties['code'];
                                        $account_data = \App\Models\Account::where('code', $code)->first();
                                        $client = \App\Models\User::where('id', $account_data->user_id)->first();
                                        $client_url = "<a href='/admin/client_details/{$client->id}' style='color: #007bff;'>{$client->email}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''>User  {$userLink} updated account master password of user {$client_url} having account {$account}.</span>
                                                        </div>";
                                        break;
                                    case 'CRM Update Group Leverage':
                                        $code = $log->properties['code'];
                                        $account_data = \App\Models\Account::where('code', $code)->first();
                                        $client = \App\Models\User::where('id', $account_data->user_id)->first();
                                        $client_url = "<a href='/admin/client_details/{$client->id}' style='color: #007bff;'>{$client->email}</a>";
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} updated Group/Leverage of user {$client_url} having account {$account}.</span>
                                                        </div>";
                                        break;
                                    case 'IB Plan Create':
                                        $ib_cat_name = $log->properties['ib_cat_name'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} created IB Plan {$ib_cat_name}.</span>
                                                        </div>";
                                        break;
                                    case 'IB Plan Update':
                                        $ib_cat_name = $log->properties['ib_cat_name'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} updated IB Plan {$ib_cat_name}.</span>
                                                        </div>";
                                        break;
                                    case 'IB Commission Create':
                                        $ib_category_id = $log->properties['ib_category_id'];
                                        $ib_plan =  \App\Models\IbCategory::where('id', $ib_category_id)->first();
                                        $acc_type = $log->properties['acc_type'];
                                        $ib_group =  \App\Models\AccountType::where('id', $acc_type)->first();
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} created IB commission with Group {$ib_group->ac_group} and Plan {$ib_plan->ib_cat_name}.</span>
                                                        </div>";
                                        break;
                                    case 'IB Commission Update':
                                        $ib_category_id = $log->properties['ib_category_id'];
                                        $ib_plan =  \App\Models\IbCategory::where('id', $ib_category_id)->first();
                                        $acc_type = $log->properties['acc_type'];
                                        $ib_group =  \App\Models\AccountType::where('id', $acc_type)->first();
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} update IB commission with Group {$ib_group->ac_group} and Plan {$ib_plan->ib_cat_name}.</span>
                                                        </div>";
                                        break;
                                    case 'Create Role':
                                        $role_name = $log->properties['role_name'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} created new role {$role_name}.</span>
                                                        </div>";
                                        break;
                                    case 'Update Role':
                                        $role_name = $log->properties['role_name'];
                                        $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} updated role {$role_name}.</span>
                                                        </div>";
                                        break;
                                    case 'Ib Request':
                                        $ib_group = $log->properties['ib_group'];
                                        $ib_plan =  \App\Models\IbPlanDetails::with('plan')->where('id', $ib_group)->first();
                                        $ib_status = $log->properties['ib_status'];
                                        $client_id = $log->properties['client_id'];
                                        $client = \App\Models\User::where('id', $client_id)->first();
                                        $client_url = "<a href='/admin/client_details/{$client->id}' style='color: #007bff;'>{$client->email}</a>";
                                        if ($ib_status==1) {
                                            $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} approve ib request of client {$client_url} having plan {$ib_plan->plan->ib_cat_name}.</span>
                                                        </div>";
                                        }elseif($ib_status==0){
                                            $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} change ib request of client {$client_url} having plan {$ib_plan->plan->ib_cat_name} to pending.</span>
                                                        </div>";
                                        }elseif($ib_status==2){
                                            $logDescription = "<div class=''>
                                                            <span style=''>User {$userLink} change ib request of client {$client_url} having plan {$ib_plan->plan->ib_cat_name} to rejected.</span>
                                                        </div>";
                                        }

                                        break;



                                    default:
                                        $logDescription = "<div class=''>
                                                            <span style=''>Activity recorded for {$userLink}.</span>
                                                            </div>";
                                        break;
                                }
                            @endphp

                            <div class="activity-item">
                                <div class="log-time px-1 py-2">
                                    <div class="log-circle-wrapper">
                                        <div style="font-size: 12px">{{ $date }}{{ $time }}</div>
                                        {{-- <div style="font-size: 12px">{{ $time }}</div> --}}
                                    </div>
                                </div>
                                <div class="log-ip px-1 py-2">
                                    <div class="log-circle-wrapper">
                                        <div style="font-size: 12px">{{ $ip }}</div>
                                    </div>
                                </div>
                                <div class="log-description @if($index % 2 == 0) from-left @else from-right @endif">
                                    <div class="log-card px-1 py-2">
                                        {!! $logDescription !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-end mt-4"> <!-- Changed to justify-content-end -->
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
