@extends('layouts.crm.crm')
@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="row">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Manager</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- <div class="col-md-6 col-lg-6">
                                <div>
                                    <iframe id="pamm-widget-iframe-register" src="{{ config('services.pamm.url') }}/app/auth/register/manager" style="width: 1px; min-width: 100%; border: medium; overflow: hidden;" scrolling="no">
                                    </iframe>
                                </div>
                            </div> --}}
                            <div class="col-md-12 col-lg-12">
                                <div>
                                    <iframe id="pamm-widget-iframe-login" src="{{ config('services.pamm.url') }}/app/auth/manager" style="width: 1px; min-width: 100%; border: medium; overflow: hidden;" scrolling="no">
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iframe-resizer/4.2.10/iframeResizer.js"></script>
    <script>
        iFrameResize({
            checkOrigin: false,
            heightCalculationMethod: 'taggedElement'
        }, '#pamm-widget-iframe-register,#pamm-widget-iframe-login');
    </script>
@endsection
