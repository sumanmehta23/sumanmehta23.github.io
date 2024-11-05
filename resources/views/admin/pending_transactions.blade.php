@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Pending Transaction List</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Transaction List</li>
                </ol>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <ul class="nav nav-tabs mb-3 border-0" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'wallet_deposit'? 'active':''}}" data-type="wallet_deposit" data-bs-toggle="tab" role="tab"
                                        href="#walletdeposit" aria-selected="true">Wallet Deposit</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'wallet_withdrawal'? 'active':''}}" data-bs-toggle="tab" data-type="wallet_withdrawal" role="tab"
                                        href="#walletwithdrawal" aria-selected="false">Wallet
                                        Withdrawal</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'trading_deposit'? 'active':''}}" data-bs-toggle="tab" data-type="trading_deposit" role="tab"
                                        href="#tradingdeposit" aria-selected="false">Trading
                                        Deposit</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'trading_withdrawal'? 'active':''}}" data-bs-toggle="tab" data-type="trading_withdrawal" role="tab"
                                        href="#tradingwithdrawal" aria-selected="false">Trading
                                        Withdrawal</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane text-muted active show" id="walletdeposit" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tableWalletDeposit"
                                            class="ajaxDataTable table table-bordered text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Email</th>
                                                    <th>Amount</th>
                                                    <th>Payment Mode</th>
                                                    <th>Deposit Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane text-muted" id="walletwithdrawal" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tableWalletWithdrawal"
                                            class="ajaxDataTable table table-bordered text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Account No</th>
                                                    <th>Withdrawal Amount</th>
                                                    <th>Withdraw To</th>
                                                    <th>Withdraw Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane text-muted" id="tradingdeposit" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tableTradingDeposit"
                                            class="ajaxDataTable table table-bordered text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>#</th>

                                                    <th>Account No</th>
                                                    <th>Deposit Amount</th>
                                                    <th>Deposit Type</th>
                                                    <th>Deposit From</th>
                                                    <th>Deposited Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane text-muted" id="tradingwithdrawal" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tableTradingWithdrawal"
                                            class="ajaxDataTable table table-bordered text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Account No</th>
                                                    <th>Withdrawal Amount</th>
                                                    <th>Withdraw Type</th>
                                                    <th>Withdraw To</th>
                                                    <th>Withdraw Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane text-muted" id="transaction5" role="tabpanel">
                                    <table id="tableInternalTransfer"
                                        class="ajaxDataTable table table-bordered text-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Amount</th>
                                                <th>Transfer From</th>
                                                <th>Transfer To</th>
                                                <th>Status</th>
                                                <!-- <th>Actions</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.shared.script_pending');
@endsection
