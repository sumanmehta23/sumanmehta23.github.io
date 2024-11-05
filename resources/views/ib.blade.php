@extends('layouts.crm.crm')
@section('content')
<div class="pc-container">
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
                <p>Join our Introducing Broker (IB) program and unlock the potential to grow your business and increase your revenue. As a valued partner, you’ll gain access to our world-class trading technology, dedicated support, and competitive compensation structures.</p>
                <h6>Benefits of Being an Introducing Broker:</h6>
                <ul>
                  <li><strong>Attractive Rebates:</strong> Earn competitive rebates and commissions on the trading activity of clients you introduce.</li>
                  <li><strong>Marketing Support:</strong> Access a wide range of marketing materials and tools designed to help you attract and retain clients.</li>
                  <li><strong>Dedicated Manager:</strong> Receive personal support from a dedicated account manager who understands your business.</li>
                  <li><strong>Transparent Reporting:</strong> Use our robust reporting tools to track your success and optimize your strategies.</li>
                </ul>
                <h6>How to Become an Introducing Broker?</h6>
                <p>Starting as an Introducing Broker with us is simple. Follow these steps: </p>
                <ol>
                  <li>Place a request</li>
                  <li>Receive confirmation and your unique IB link.</li>
                  <li>Start promoting us and earn as your referrals trade!</li>
                </ol>

                @if (is_null($ib_result))
                  <a href="#" class="d-grid ib_enrol">
                    <button class="btn btn-primary ib-enroll">
                      <span class="text-truncate w-100">Enroll as an Introducing Broker</span>
                    </button>
                  </a>
                @elseif ($ib_result->status == 0)
                  <span class="badge bg-light-warning mt-4 mb-5">Pending Approval</span>
                @endif

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    $(".ib-enroll").click(function() {
      $.ajax({
        url: "{{ route('ib-enroll') }}",
        data: "ib_enroll=true",
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
              title: "You're Successfully enrolled as an IB",
              text: "Share and Earn",
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
