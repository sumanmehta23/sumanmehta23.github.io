@extends('layouts.crm.crm')
@section('content')
<div class="pc-container">
    @if (auth()->user()->kyc_verify == 1)
        <div class="pc-content">
            <div class="row">
                <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Introducing Broker Program</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                    <div class="col-md-6 col-lg-6">
                        <div class="text-center mb-5">
                        <img src="{{ asset('assets/ib_banner.png') }}" alt="Introducing Broker" class="pt-4 mt-3" style="width: 80%;">
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <h5 class="mb-4">Become an Introducing Broker with us</h5>
                        <p>Join our IB program and start earning by connecting traders to our platform. Get access to advanced trading tools, dedicated support, and a clear commission structure built to grow with you.</p>
                        <h6>Why Partner with Us as an IB:</h6>
                        <ul>
                        <li><strong>$5/lot Commissions:</strong> Start with our default plan — earn $5 per traded lot on all referred client activity.</li>
                        <li><strong>VIP Tiers:</strong> Unlock exclusive commission upgrades as your network grows.</li>
                        <li><strong>Marketing Tools:</strong> Get banners, content, and ready-made materials to help you attract and retain clients.</li>
                        <li><strong>1-on-1 Support: </strong> Work closely with a dedicated account manager who’s here to support your growth.</li>
                        <li><strong>Real-Time Tracking: </strong>Monitor performance and payouts through our easy-to-use reporting system.</li>
                        </ul>
                        <h6>How to Get Started:</h6>
                        <p>Starting as an Introducing Broker with us is simple. Follow these steps: </p>
                        <ol>
                        <li>Submit a quick request to join.</li>
                        <li>Receive confirmation and your unique IB link.</li>
                        <li>Promote and earn as your clients trade — no deposit required to start.</li>
                        </ol>

                        @if (is_null($ib_result))
                        <a href="#" class="d-grid ib_enrol">
                            <button class="btn btn-primary ib-enroll">
                            <span class="text-truncate w-100">Enroll as an Introducing Broker</span>
                            </button>
                        </a>
                        @elseif ($ib_result->status == 0)
                        <span class="badge bg-success text-white mt-4 mb-5 fs-6">Pending Approval</span>
                        @elseif ($ib_result->status == 2)
                        <span class="badge bg-light-warning mt-4 mb-5 fs-6">Approval Rejected</span>
                        <a href="#" class="d-grid ib_enrol">
                            <button class="btn btn-primary ib-resend">
                            <span class="text-truncate w-100">Resend Approval</span>
                            </button>
                        </a>
                        @endif

                    </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    @else
        <div class="pc-content">
            <div class="card support-tickets ribbon-box border ribbon-fill shadow-none pb-1">
                <div class="row p-3">
                    <div class="card-body text-center">
                        <div class="text-center me-4"><a href="/transactions/deposit#"><img src="/assets/images/doc_upload.png" class="w-25" alt="img"></a></div>
                        <h6 class="text-center text-secondary mb-3 mt-2 f-w-400 mb-0 f-16">KYC Not Yet Verified !</h6>
                        <a id="verify-user-kyc" class="mt-3"><button class="btn btn-outline-primary"><span class="text-truncate">Verify Now To Proceed</span></button></a>
                    </div>
                </div>
            </div>
        </div>
    @endif
  <script>
    $(".ib-enroll").click(function() {
        $.ajax({
            url: "{{ route('ib-enroll') }}",
            data: { ib_enroll: true }, // ✅ better way to pass data
            type: "POST",
            beforeSend: function() {
            Swal.fire({
                showConfirmButton: false,
                showCancelButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function() {
                Swal.showLoading(); // ✅ correct function to show loading
                }
            });
            },
            success: function(data) {
            Swal.close();

            if (data.status === 'true' && data.activationType === 'automatic') {
                Swal.fire({
                title: "You're officially enrolled as an Introducing Broker",
                text: "",
                icon: "success"
                }).then(() => {
                location.reload();
                });

            } else if (data.status === 'true' && data.activationType === 'manually') {
                Swal.fire({
                title: "Your IB request has been sent for approval. You will be notified once it’s approved.",
                text: "",
                icon: "success"
                }).then(() => {
                location.reload();
                });
            }
            }
        });
    });

    $(".ib-resend").click(function() {
      $.ajax({
        url: "{{ route('ib-resend') }}",
        data: "ib_resend=true",
        type: "POST",
        beforeSend: function() {
          Swal.fire({
            showConfirmButton: false,
            showCancelButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: function() {
              Swal.enableLoading();
            }
          });
        },
        success: function(data) {
          Swal.close();
          if (data.status == 'true') {
            Swal.fire({
              title: "You'r Ib request is resent",
              text: "Welcome to the team!",
              icon: "success"
            }).then((val) => {
              location.reload();
            });
          }
        }
      });
    });
  </script>

@endsection
