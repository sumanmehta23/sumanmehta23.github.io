<div class="modal fade" id="accountSoftDeleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="accountSoftDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.softDeleteAccount') }}" id="softDeleteAccountForm" method="POST">
                @csrf
                <input type="hidden" name="client_id" id="client_id" value="{{ $getUser->user_id }}">
                <input type="hidden" name="account_id" id="account_id" value="{{ $getUser->id ?? '' }}">
                <input type="hidden" name="email" id="email" value="{{ $getUser->email ?? '' }}">
                <input type="hidden" name="platform" id="platform" value="{{ $getUser->platform ?? '' }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountSoftDeleteModalLabel">Soft Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-3">
                        This will soft delete the account — client info will be hidden but kept for records.
                        Accounts with trading history will be disabled, not deleted.
                        Deposits and withdrawals remain for reconciliation, and emails to the client will stop.
                    </p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="accountDeleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="accountDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.deleteAccount') }}" id="deleteAccountForm" method="POST">
                @csrf
                <input type="hidden" name="client_id" id="client_id" value="{{ $getUser->user_id }}">
                <input type="hidden" name="account_id" id="account_id" value="{{ $getUser->id ?? '' }}">
                <input type="hidden" name="email" id="email" value="{{ $getUser->email ?? '' }}">
                <input type="hidden" name="platform" id="platform" value="{{ $getUser->platform ?? '' }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountDeleteModalLabel">Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-3">
                        This will delete the account from MT5.
                    </p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="accountRestoreModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="accountRestoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            {{-- {{ dd($getUser) }} --}}
            <form action="{{ route('admin.restoreAccount') }}" id="restoreAccountForm" method="POST">
                @csrf
                <input type="hidden" name="client_id" id="client_id" value="{{ $getUser->user_id }}">
                <input type="hidden" name="account_id" id="account_id" value="{{ $getUser->id ?? '' }}">
                <input type="hidden" name="email" id="email" value="{{ $getUser->email ?? '' }}">
                <input type="hidden" name="platform" id="platform" value="{{ $getUser->platform ?? '' }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountRestoreModalLabel">Restore Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-3">
                        This will restore the account. All client data, trading access, deposits, withdrawals, and email communication will be fully restored.
                    </p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Restore Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="accountArchiveModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="accountArchiveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.archiveAccount') }}" id="archiveAccountForm" method="POST">
                @csrf
                <input type="hidden" name="client_id" id="client_id" value="{{ $getUser->user_id }}">
                <input type="hidden" name="account_id" id="account_id" value="{{ $getUser->id ?? '' }}">
                <input type="hidden" name="email" id="email" value="{{ $getUser->email ?? '' }}">
                <input type="hidden" name="login" id="login" value="{{ $getUser->code ?? '' }}">
                <input type="hidden" name="platform" id="platform" value="{{ $getUser->platform ?? '' }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountArchiveModalLabel">Archive Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-3">
                        This will move the account to the archive database on MT5.
                        The account will no longer be active for trading but will be preserved in the archive.
                    </p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Archive Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="depositModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="depositModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{route('admin.depositToAccount')}}" id="depositForm" method="post">
                @csrf
                <input type="hidden" name="client_id" id="client_id" value="<?= ($getUser->user_id) ?>">
                <input type="hidden" name="code" id="code" value="<?= $getUser->code ?? '' ?>">
                <input type="hidden" name="email" id="email" value="<?= $getUser->email ?? '' ?>">

                <div class="modal-header">
                    <h5 class="modal-title" id="depositModalLabel">Deposit To Trade Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="mb-0 modal-body custom-card card" style="max-height: 400px;overflow-y: auto;">
                    <div class="card-body">
                        <div class="trade-deposit-details">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="mt-2 col-12">
                                        <div class="form-group row"><label class="col-lg-4 col-form-label">AMOUNT IN USD
                                                :<small class="text-muted d-block"> Deposit
                                                    amount in USD </small></label>
                                            <div class="col-lg-8">
                                                <div class="mb-3 input-group"><span
                                                        class="input-group-text">USD</span><input name="amount"
                                                        id="amount_deposit" type="text"
                                                        class="form-control fill tradedeposit_amount" required><!---->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 form-group row"><label class="col-lg-4 col-form-label">ADMIN
                                                REMARK:</label>
                                            <div class="col-lg-8">
                                                <textarea id="description" name="description" rows="3"
                                                    class="mt-2 form-control" placeholder="Add a remark"></textarea>
                                            </div>
                                        </div>
                                        <div class="">
                                            <div class="row">
                                                <div class="col-lg-4"></div>
                                                <div class="col-lg-8">
                                                    <div class="row g-1"><input type="submit" name="deposit_to_account"
                                                            class="btn btn-primary col-12"
                                                            value="Deposit To Trade Account"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="withdrawalModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="withdrawalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{route('admin.withdrawFromAccount')}}" id="withdrawalForm" method="post">
                @csrf
                <input type="hidden" name="client_id" id="client_id" value="<?= ($getUser->user_id) ?>">
                <input type="hidden" name="code" id="code" value="<?= $getUser->code ?? '' ?>">
                <input type="hidden" name="email" id="email" value="<?= $getUser->email ?? '' ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="withdrawalModalLabel">Withdraw From Trade Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="mb-0 modal-body custom-card card" style="max-height: 400px;overflow-y: auto;">
                    <div class="card-body">
                        <div class="trade-deposit-details">
                            <form method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="mt-2 col-12">
                                        <div class="form-group row"><label class="col-lg-4 col-form-label">AMOUNT IN USD
                                                :<small class="text-muted d-block"> Withdrawal
                                                    amount in USD </small></label>
                                            <div class="col-lg-8">
                                                <div class="mb-3 input-group"><span
                                                        class="input-group-text">USD</span><input name="amount"
                                                        id="amount_deposit" type="text"
                                                        class="form-control fill tradedeposit_amount" required><!---->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 form-group row"><label class="col-lg-4 col-form-label">ADMIN
                                                REMARK:</label>
                                            <div class="col-lg-8">
                                                <textarea id="description" name="description" rows="3"
                                                    class="mt-2 form-control" placeholder="Add a remark"></textarea>
                                            </div>
                                        </div>
                                        <div class="">
                                            <div class="row">
                                                <div class="col-lg-4"></div>
                                                <div class="col-lg-8">
                                                    <div class="row g-1"><input type="submit"
                                                            name="withdraw_from_account" class="btn btn-primary col-12"
                                                            value="Withdraw From Trade Account"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="depositModalCellExp" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="depositModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{route('admin.depositToCellexpertAccount')}}" id="depositForm" method="post">
                @csrf
                <input type="hidden" name="client_id" id="client_id" value="<?= ($getUser->user_id) ?>">
                <input type="hidden" name="code" id="code" value="<?= $getUser->code ?? '' ?>">
                <input type="hidden" name="email" id="email" value="<?= $getUser->email ?? '' ?>">

                <div class="modal-header">
                    <h5 class="modal-title" id="depositModalLabel">Deposit To Cell Expert Trade Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="mb-0 modal-body custom-card card" style="max-height: 400px;overflow-y: auto;">
                    <div class="card-body">
                        <div class="trade-deposit-details">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="mt-2 col-12">
                                        <div class="form-group row"><label class="col-lg-4 col-form-label">AMOUNT IN USD
                                                :<small class="text-muted d-block"> Deposit
                                                    amount in USD </small></label>
                                            <div class="col-lg-8">
                                                <div class="mb-3 input-group"><span
                                                        class="input-group-text">USD</span><input name="amount"
                                                        id="amount_deposit" type="text"
                                                        class="form-control fill tradedeposit_amount" required><!---->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 form-group row"><label class="col-lg-4 col-form-label">ADMIN
                                                REMARK:</label>
                                            <div class="col-lg-8">
                                                <textarea id="description" name="description" rows="3"
                                                    class="mt-2 form-control" placeholder="Add a remark"></textarea>
                                            </div>
                                        </div>
                                        <div class="">
                                            <div class="row">
                                                <div class="col-lg-4"></div>
                                                <div class="col-lg-8">
                                                    <div class="row g-1"><input type="submit" name="deposit_to_account"
                                                            class="btn btn-primary col-12"
                                                            value="Deposit To Cell Expert Trade Account"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="withdrawalModalCellExp" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="withdrawalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{route('admin.withdrawFromCellexpertAccount')}}" id="withdrawalForm" method="post">
                @csrf
                <input type="hidden" name="client_id" id="client_id" value="<?= ($getUser->user_id) ?>">
                <input type="hidden" name="code" id="code" value="<?= $getUser->code ?? '' ?>">
                <input type="hidden" name="email" id="email" value="<?= $getUser->email ?? '' ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="withdrawalModalLabel">Withdraw From Cell Expert Trade Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="mb-0 modal-body custom-card card" style="max-height: 400px;overflow-y: auto;">
                    <div class="card-body">
                        <div class="trade-deposit-details">
                            <form method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="mt-2 col-12">
                                        <div class="form-group row"><label class="col-lg-4 col-form-label">AMOUNT IN USD
                                                :<small class="text-muted d-block"> Withdrawal
                                                    amount in USD </small></label>
                                            <div class="col-lg-8">
                                                <div class="mb-3 input-group"><span
                                                        class="input-group-text">USD</span><input name="amount"
                                                        id="amount_deposit" type="text"
                                                        class="form-control fill tradedeposit_amount" required><!---->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 form-group row"><label class="col-lg-4 col-form-label">ADMIN
                                                REMARK:</label>
                                            <div class="col-lg-8">
                                                <textarea id="description" name="description" rows="3"
                                                    class="mt-2 form-control" placeholder="Add a remark"></textarea>
                                            </div>
                                        </div>
                                        <div class="">
                                            <div class="row">
                                                <div class="col-lg-4"></div>
                                                <div class="col-lg-8">
                                                    <div class="row g-1"><input type="submit"
                                                            name="withdraw_from_account" class="btn btn-primary col-12"
                                                            value="Withdraw From Trade Account"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="bonusModalCredit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="bonusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{route('admin.creditBonusToAccount')}}" id="bonusForm" method="post">
                @csrf
                <input type="hidden" name="client_id" id="client_id" value="<?= ($getUser->user_id) ?>">
                <input type="hidden" name="code" id="code" value="<?= $getUser->code ?? '' ?>">
                <input type="hidden" name="email" id="email" value="<?= $getUser->email ?? '' ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="bonusModalLabel">Bonus To Trade Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="mb-0 modal-body custom-card card" style="max-height: 400px;overflow-y: auto;">
                    <div class="card-body">
                        <div class="trade-deposit-details">
                            <form method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="mt-2 col-12">
                                        <div class="mb-3 form-group row">
                                            <label class="col-lg-4 col-form-label">BONUS TYPE:</label>
                                            <div class="col-lg-8">
                                                <select name="type" id="input" class="form-control" required="required">
                                                    <option value="in">Bonus In</option>
                                                    <option value="out">Bonus Out</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-lg-4 col-form-label">AMOUNT IN USD
                                                :<small class="text-muted d-block"> Bonus
                                                    amount in USD </small></label>
                                            <div class="col-lg-8">
                                                <div class="mb-3 input-group"><span
                                                        class="input-group-text">USD</span><input name="amount"
                                                        id="amount_deposit" type="text"
                                                        class="form-control fill tradedeposit_amount" required><!---->
                                                </div>
                                            </div>
                                        </div>
                                        {{-- <div class="mb-3 form-group row"><label
                                                class="col-lg-4 col-form-label">ADMIN
                                                REMARK:</label>
                                            <div class="col-lg-8"><input id="description" name="description" rows="3"
                                                    class="mt-2 form-control" placeholder="Add a remark" required>
                                            </div>
                                        </div> --}}
                                        <div class="">
                                            <div class="row">
                                                <div class="col-lg-4"></div>
                                                <div class="col-lg-8">
                                                    <div class="row g-1"><input type="submit"
                                                            name="bonus_to_account_credit"
                                                            class="btn btn-primary col-12"
                                                            value="Bonus To Trade Account"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="bonusModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="bonusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{route('admin.bonusToAccount')}}" id="bonusForm" method="post">
                @csrf
                <input type="hidden" name="client_id" id="client_id" value="<?= ($getUser->user_id) ?>">
                <input type="hidden" name="code" id="code" value="<?= $getUser->code ?? '' ?>">
                <input type="hidden" name="email" id="email" value="<?= $getUser->email ?? '' ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="bonusModalLabel">Bonus To Trade Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="mb-0 modal-body custom-card card" style="max-height: 400px;overflow-y: auto;">
                    <div class="card-body">
                        <div class="trade-deposit-details">
                            <form method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="mt-2 col-12">
                                        <div class="mb-3 form-group row">
                                            <label class="col-lg-4 col-form-label">BONUS TYPE:</label>
                                            <div class="col-lg-8">
                                                <select name="type" id="input" class="form-control" required="required">
                                                    <option value="in">Bonus In</option>
                                                    <option value="out">Bonus Out</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-lg-4 col-form-label">AMOUNT IN USD
                                                :<small class="text-muted d-block"> Bonus
                                                    amount in USD </small></label>
                                            <div class="col-lg-8">
                                                <div class="mb-3 input-group"><span
                                                        class="input-group-text">USD</span><input name="amount"
                                                        id="amount_deposit" type="text"
                                                        class="form-control fill tradedeposit_amount" required><!---->
                                                </div>
                                            </div>
                                        </div>
                                        {{-- <div class="mb-3 form-group row"><label
                                                class="col-lg-4 col-form-label">ADMIN
                                                REMARK:</label>
                                            <div class="col-lg-8"><input id="description" name="description" rows="3"
                                                    class="mt-2 form-control" placeholder="Add a remark" required>
                                            </div>
                                        </div> --}}
                                        <div class="">
                                            <div class="row">
                                                <div class="col-lg-4"></div>
                                                <div class="col-lg-8">
                                                    <div class="row g-1"><input type="submit" name="bonus_to_account"
                                                            class="btn btn-primary col-12"
                                                            value="Bonus To Trade Account"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
