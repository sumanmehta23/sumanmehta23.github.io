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
                            {{-- <form method="GET" action="{{ url('admin/logs') }}" class="mb-4">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search logs..." value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                                    </div>
                                </div>
                            </form> --}}
                            <form method="GET" action="{{ url('admin/logs') }}" class="mb-4">
                                <div class="input-group">
                                    <select name="search_type" class="form-control">
                                        <option value="text" {{ request('search_type') == 'text' ? 'selected' : '' }}>Search By Text</option>
                                        <option value="user" {{ request('search_type') == 'user' ? 'selected' : '' }}>Search By User</option>
                                        <option value="date_range" {{ request('search_type') == 'date_range' ? 'selected' : '' }}>Search By Date</option>
                                        <option value="type" {{ request('search_type') == 'type' ? 'selected' : '' }}>Search By Type</option>
                                    </select>
                                    <input type="text" name="search" class="form-control" placeholder="Search logs..." value="{{ request('search') }}" id="search-text">
                                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" id="start-date" style="display: none;">
                                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" id="end-date" style="display: none;">
                                    <select name="log_type" class="form-control" id="log-type" style="display: none;">
                                        <option value="">Select Type</option>
                                        <option value="Login" {{ request('log_type') == 'Login' ? 'selected' : '' }}>Login</option>
                                        <option value="Logout" {{ request('log_type') == 'Logout' ? 'selected' : '' }}>Logout</option>
                                        <option value="Incorrect login details" {{ request('log_type') == 'Incorrect login details' ? 'selected' : '' }}>Incorrect login details</option>
                                        <option value="Too many requests" {{ request('log_type') == 'Too many requests' ? 'selected' : '' }}>Too many requests</option>
                                        <option value="Invalid email or unverified account" {{ request('log_type') == 'Invalid email or unverified account' ? 'selected' : '' }}>Invalid email or unverified account</option>
                                        <option value="Switch To User" {{ request('log_type') == 'Switch To User' ? 'selected' : '' }}>Switch To User</option>
                                        <option value="Update Client Email" {{ request('log_type') == 'Update Client Email' ? 'selected' : '' }}>Update Client Email</option>
                                        <option value="Create Demo Account" {{ request('log_type') == 'Create Demo Account' ? 'selected' : '' }}>Create Demo Account</option>
                                        <option value="Create Live Account" {{ request('log_type') == 'Create Live Account' ? 'selected' : '' }}>Create Live Account</option>
                                        <option value="Wallet Withdraw" {{ request('log_type') == 'Wallet Withdraw' ? 'selected' : '' }}>Wallet Withdraw</option>
                                        <option value="Reject Wallet Withdraw" {{ request('log_type') == 'Reject Wallet Withdraw' ? 'selected' : '' }}>Reject Wallet Withdraw</option>
                                        <option value="Approve Wallet Withdraw" {{ request('log_type') == 'Approve Wallet Withdraw' ? 'selected' : '' }}>Approve Wallet Withdraw</option>
                                        <option value="Manually Approved Wallet Withdraw" {{ request('log_type') == 'Manually Approved Wallet Withdraw' ? 'selected' : '' }}>Manually Approved Wallet Withdraw</option>
                                        <option value="Wallet Withdraw Cancel By Client" {{ request('log_type') == 'Wallet Withdraw Cancel By Client' ? 'selected' : '' }}>Wallet Withdraw Cancel By Client</option>
                                        <option value="Account Withdraw" {{ request('log_type') == 'Account Withdraw' ? 'selected' : '' }}>Account Withdraw</option>
                                        <option value="Account Deposit" {{ request('log_type') == 'Account Deposit' ? 'selected' : '' }}>Account Deposit</option>
                                        <option value="Internal Transfer" {{ request('log_type') == 'Internal Transfer' ? 'selected' : '' }}>Internal Transfer</option>
                                        <option value="Created New Wallet Address" {{ request('log_type') == 'Created New Wallet Address' ? 'selected' : '' }}>Created New Wallet Address</option>
                                        <option value="Verified New Wallet Address" {{ request('log_type') == 'Verified New Wallet Address' ? 'selected' : '' }}>Verified New Wallet Address</option>
                                        <option value="Edit Wallet Details" {{ request('log_type') == 'Edit Wallet Details' ? 'selected' : '' }}>Edit Wallet Details</option>
                                        <option value="Verify Wallet Deletion" {{ request('log_type') == 'Verify Wallet Deletion' ? 'selected' : '' }}>Verify Wallet Deletion</option>
                                        <option value="Wallet Deleted" {{ request('log_type') == 'Wallet Deleted' ? 'selected' : '' }}>Wallet Deleted</option>
                                        <option value="Update Client Password" {{ request('log_type') == 'Update Client Password' ? 'selected' : '' }}>Update Client Password</option>
                                        <option value="Commission Transfer" {{ request('log_type') == 'Commission Transfer' ? 'selected' : '' }}>Commission Transfer</option>
                                        <option value="Update Referral" {{ request('log_type') == 'Update Referral' ? 'selected' : '' }}>Update Referral</option>
                                        <option value="Update Client Status" {{ request('log_type') == 'Update Client Status' ? 'selected' : '' }}>Update Client Status</option>
                                        <option value="Client Email Confirmation" {{ request('log_type') == 'Client Email Confirmation' ? 'selected' : '' }}>Client Email Confirmation</option>
                                        <option value="Update Client Details" {{ request('log_type') == 'Update Client Details' ? 'selected' : '' }}>Update Client Details</option>
                                        <option value="Delete Account" {{ request('log_type') == 'Delete Account' ? 'selected' : '' }}>Delete Account</option>
                                        <option value="CRM Deposit" {{ request('log_type') == 'CRM Deposit' ? 'selected' : '' }}>CRM Deposit</option>
                                        <option value="CRM Withdraw" {{ request('log_type') == 'CRM Withdraw' ? 'selected' : '' }}>CRM Withdraw</option>
                                        <option value="CRM Credit Bonus" {{ request('log_type') == 'CRM Credit Bonus' ? 'selected' : '' }}>CRM Credit Bonus</option>
                                        <option value="CRM Deposit Bonus" {{ request('log_type') == 'CRM Deposit Bonus' ? 'selected' : '' }}>CRM Deposit Bonus</option>
                                        <option value="CRM Update Investor Password" {{ request('log_type') == 'CRM Update Investor Password' ? 'selected' : '' }}>CRM Update Investor Password</option>
                                        <option value="CRM Update Master Password" {{ request('log_type') == 'CRM Update Master Password' ? 'selected' : '' }}>CRM Update Master Password</option>
                                        <option value="CRM Update Group Leverage" {{ request('log_type') == 'CRM Update Group Leverage' ? 'selected' : '' }}>CRM Update Group Leverage</option>
                                        <option value="IB Plan Create" {{ request('log_type') == 'IB Plan Create' ? 'selected' : '' }}>IB Plan Create</option>
                                        <option value="IB Plan Update" {{ request('log_type') == 'IB Plan Update' ? 'selected' : '' }}>IB Plan Update</option>
                                        <option value="IB Commission Create" {{ request('log_type') == 'IB Commission Create' ? 'selected' : '' }}>IB Commission Create</option>
                                        <option value="IB Commission Update" {{ request('log_type') == 'IB Commission Update' ? 'selected' : '' }}>IB Commission Update</option>
                                        <option value="Create Role" {{ request('log_type') == 'Create Role' ? 'selected' : '' }}>Create Role</option>
                                        <option value="Update Role" {{ request('log_type') == 'Update Role' ? 'selected' : '' }}>Update Role</option>
                                        <option value="Ib Request" {{ request('log_type') == 'Ib Request' ? 'selected' : '' }}>Ib Request</option>

                                        <!-- Add more options as needed -->
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                                    </div>
                                </div>
                            </form>

                            <div class="activity-log justify-content-center">

                                @foreach($logs as $index => $log)
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
                                        <div class="d-flex justify-content-end mt-4">
                                            {{-- {{ $logs->appends(['search' => request('search')])->links('pagination::bootstrap-4') }} --}}
                                            {{ $logs->appends([
                                                'search' => request('search'),
                                                'search_type' => request('search_type'),
                                                'start_date' => request('start_date'),
                                                'end_date' => request('end_date'),
                                                'log_type' => request('log_type'),
                                            ])->links('pagination::bootstrap-4') }}
                                        </div>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchType = document.querySelector('select[name="search_type"]');
        const searchText = document.getElementById('search-text');
        const startDate = document.getElementById('start-date');
        const endDate = document.getElementById('end-date');
        const logType = document.getElementById('log-type');

        function toggleFields() {
            const selectedType = searchType.value;
            // searchText.style.display = selectedType === 'text' ? 'block' : 'none';
            // startDate.style.display = selectedType === 'date_range' ? 'block' : 'none';
            // endDate.style.display = selectedType === 'date_range' ? 'block' : 'none';
            // logType.style.display = selectedType === 'type' ? 'block' : 'none';

            searchText.style.display = (selectedType === 'user' || selectedType === 'text') ? 'block' : 'none';
            logType.style.display = (selectedType === 'type' || selectedType === 'user') ? 'block' : 'none';
            startDate.style.display = selectedType === 'date_range' ? 'block' : 'none';
            endDate.style.display = selectedType === 'date_range' ? 'block' : 'none';
        }

        searchType.addEventListener('change', toggleFields);
        toggleFields(); // Initial call to set the correct fields visible
    });
</script>
