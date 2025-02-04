<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KYC Verification</title>
    <script src="https://static.sumsub.com/idensic/static/sns-websdk-builder.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/libs/sweetalert2/sweetalert2.min.css') }}">
</head>

<body>
    <div id="sumsub-websdk-container"></div>
    <script>
        localStorage.setItem("isVerified", "false");
let applicantId='';
        function launchWebSdk(accessToken, applicantEmail, applicantPhone) {

            let snsWebSdkInstance = snsWebSdk
                .init(accessToken, () => getNewAccessToken())
                .withConf({
                    lang: "en",
                    email: applicantEmail,
                    phone: applicantPhone,
                })
                .withOptions({
                    addViewportTag: false,
                    adaptIframeHeight: true
                })
                .on("idCheck.onStepCompleted", (payload) => {
                    console.log("Step completed: ", payload);
                    // Handle the step completion event
                })
                .on("idCheck.onApplicantLoaded", (payload) => {
                    // console.log("onApplicantLoaded: ", payload);
                    applicantId=payload.applicantId;
                    // Handle the step completion event
                })
                .on("idCheck.onError", (error) => {
                    // Handle the error event
                })
                .onMessage((type, payload) => {
                    // console.log("Received message:", type, payload,applicantId);
                    payload.applicantId=applicantId;
                    $.ajax({
                        url: "{{ url('/sumsub_verify') }}",
                        type: "POST",
                        data: {
                            type: type,
                            payload: payload,

                            sumsub: "action"
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Set the CSRF token
                        },
                        success: function(data) {
                            if (localStorage.getItem("isVerified") == "false") {
                                try {
                                    var json_data = JSON.parse(data);
                                } catch (err) {
                                    var json_data = data;
                                }
                                if (json_data.status == "true") {
                                    var applicantId = json_data.applicantId;
                                    $.ajax({
                                            url: "{{ url('/log_kyc_verification') }}", // Endpoint for logging KYC verification
                                            type: "POST",
                                            data: {
                                                applicantId: applicantId,
                                                applicantEmail: applicantEmail,  // Ensure this variable is set
                                                userId: "{{ auth()->user()->id }}", // Pass the authenticated user's ID
                                                _token: $('meta[name="csrf-token"]').attr('content') // CSRF token for security
                                            },
                                            success: function(response) {
                                                // Handle the success of logging if needed
                                                console.log('KYC log saved:', response);
                                            }
                                        });
                                    swal.fire({
                                        icon: "success",
                                        title: "Your KYC has been successfully verified",
                                        text: "You're now cleared for complete access to Liquidity House",
                                        allowEscapeKey: false,
                                        allowOutsideClick: false
                                    }).then(() => {
                                        parent.location.reload();
                                        location.href = "{{ url('/dashboard') }}";
                                    });
                                }
                            } else {
                                localStorage.setItem("isVerified", "true");
                            }
                        }
                    });
                })
                .build();
            snsWebSdkInstance.launch("#sumsub-websdk-container");
        }


        // Requests a new access token from the backend side.
        function getNewAccessToken() {
            return Promise.resolve("{{ $token }}");
        }
        launchWebSdk("{{ $token }}");
    </script>
</body>

</html>
