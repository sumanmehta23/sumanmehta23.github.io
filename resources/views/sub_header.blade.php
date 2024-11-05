<?php
$script_name=request()->path();
?>
<ul class="nav nav-tabs checkout-tabs mb-0" id="myTab" role="tablist">
    <li class="nav-item" role="presentation"><a class="nav-link <?= ($script_name == "trade-deposit")?"active":"" ?>" id="ecomtab-tab-1"
            href="/trade-deposit" role="tab" aria-controls="ecomtab-1" aria-selected="true"
            tabindex="-1">
            <div class="media align-items-center">
                <div class="avtar avtar-s"><i class="feather icon-credit-card"></i>
                </div>
                <div class="media-body ms-2">
                    <h6 class="mb-0">DEPOSIT</h6>
                </div>
            </div>
        </a>
    </li>
    <li class="nav-item" role="presentation"><a class="nav-link <?= ($script_name == "trade-withdrawal")?"active":"" ?>" href="/trade-withdrawal"
            aria-controls="ecomtab-2" aria-selected="false" tabindex="-1">
            <div class="media align-items-center">
                <div class="avtar avtar-s"><i class="feather icon-dollar-sign"></i>
                </div>
                <div class="media-body ms-2">
                    <h6 class="mb-0">WITHDRAW</h6>
                </div>
            </div>
        </a>
    </li>
    <li class="nav-item" role="presentation"><a class="nav-link <?= ($script_name == "internal-transfer")?"active":"" ?>" href="/internal-transfer"
            aria-controls="ecomtab-2" aria-selected="false" tabindex="-1">
            <div class="media align-items-center">
                <div class="avtar avtar-s"><i class="feather icon-repeat"></i>
                </div>
                <div class="media-body ms-2">
                    <h6 class="mb-0">INTERNAL TRANSFER</h6>
                </div>
            </div>
        </a>
    </li>
</ul>
