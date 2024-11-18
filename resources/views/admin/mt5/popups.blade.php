<div class="modal fade" id="depositModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="depositModalLabel" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <form action="{{route('admin.depositToAccount')}}" id="depositForm" method="post">
                @csrf
                <input type="hidden" name="client_id" id="client_id" value="<?= md5($id) ?>">
                <input type="hidden" name="trade_id" id="trade_id" value="<?= $getUser->trade_id??'' ?>">
                <input type="hidden" name="email" id="email" value="<?= $getUser->email??'' ?>">

                <div class="modal-header">
                    <h5 class="modal-title" id="depositModalLabel">Deposit To Trade Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body custom-card card mb-0" style="max-height: 400px;overflow-y: auto;">
                    <div class="card-body">
                        <div class="trade-deposit-details">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-12 mt-2">
                                        <div class="form-group row"><label class="col-lg-4 col-form-label">AMOUNT IN USD
                                                :<small class="text-muted d-block"> Deposit
                                                    amount in USD </small></label>
                                            <div class="col-lg-8">
                                                <div class="input-group mb-3"><span
                                                        class="input-group-text">USD</span><input name="amount"
                                                        id="amount_deposit" type="text"
                                                        class="form-control fill tradedeposit_amount" required><!---->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3"><label class="col-lg-4 col-form-label">ADMIN
                                                REMARK:</label>
                                            <div class="col-lg-8">
                                                <textarea id="description" name="description" rows="3" class="mt-2 form-control" placeholder="Add a remark"></textarea>
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
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <form action="{{route('admin.withdrawFromAccount')}}" id="withdrawalForm" method="post">
                @csrf
                <input type="hidden" name="client_id" id="client_id" value="<?= md5($id) ?>">
                <input type="hidden" name="trade_id" id="trade_id" value="<?= $getUser->trade_id??'' ?>">
                <input type="hidden" name="email" id="email" value="<?= $getUser->email??'' ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="withdrawalModalLabel">Withdraw From Trade Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body custom-card card mb-0" style="max-height: 400px;overflow-y: auto;">
                    <div class="card-body">
                        <div class="trade-deposit-details">
                            <form method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-12 mt-2">
                                        <div class="form-group row"><label class="col-lg-4 col-form-label">AMOUNT IN USD
                                                :<small class="text-muted d-block"> Withdrawal
                                                    amount in USD </small></label>
                                            <div class="col-lg-8">
                                                <div class="input-group mb-3"><span
                                                        class="input-group-text">USD</span><input name="amount"
                                                        id="amount_deposit" type="text"
                                                        class="form-control fill tradedeposit_amount" required><!---->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-3"><label class="col-lg-4 col-form-label">ADMIN
                                                REMARK:</label>
                                            <div class="col-lg-8">
                                                <textarea id="description" name="description" rows="3" class="mt-2 form-control" placeholder="Add a remark"></textarea>
                                            </div>
                                        </div>
                                        <div class="">
                                            <div class="row">
                                                <div class="col-lg-4"></div>
                                                <div class="col-lg-8">
                                                    <div class="row g-1"><input type="submit"
                                                            name="withdraw_from_account"
                                                            class="btn btn-primary col-12"
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
<div class="modal fade" id="bonusModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="bonusModalLabel" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <form action="{{route('admin.bonusToAccount')}}" id="bonusForm" method="post">
                @csrf
                <input type="hidden" name="client_id" id="client_id" value="<?= md5($id) ?>">
                <input type="hidden" name="trade_id" id="trade_id" value="<?= $getUser->trade_id??'' ?>">
                <input type="hidden" name="email" id="email" value="<?= $getUser->email??'' ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="bonusModalLabel">Bonus To Trade Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body custom-card card mb-0" style="max-height: 400px;overflow-y: auto;">
                    <div class="card-body">
                        <div class="trade-deposit-details">
                            <form method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-12 mt-2">
                                        <div class="form-group row mb-3">
                                            <label class="col-lg-4 col-form-label">BONUS TYPE:</label>
                                            <div class="col-lg-8">
                                                <select name="type" id="input" class="form-control"
                                                    required="required">
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
                                                <div class="input-group mb-3"><span
                                                        class="input-group-text">USD</span><input name="amount"
                                                        id="amount_deposit" type="text"
                                                        class="form-control fill tradedeposit_amount" required><!---->
                                                </div>
                                            </div>
                                        </div>
                                        {{-- <div class="form-group row mb-3"><label class="col-lg-4 col-form-label">ADMIN
                                                REMARK:</label>
                                            <div class="col-lg-8"><input id="description" name="description"
                                                    rows="3" class="mt-2 form-control"
                                                    placeholder="Add a remark" required>
                                            </div>
                                        </div> --}}
                                        <div class="">
                                            <div class="row">
                                                <div class="col-lg-4"></div>
                                                <div class="col-lg-8">
                                                    <div class="row g-1"><input type="submit"
                                                            name="bonus_to_account" class="btn btn-primary col-12"
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
