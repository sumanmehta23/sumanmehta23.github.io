<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KYC Verification - Veriff</title>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
            integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/libs/sweetalert2/sweetalert2.min.css') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 100%;
            height: 100%;
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .loading-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #00b2a9;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loading-text {
            color: #333;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .sub-text {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>

<body>
<div class="loading-container" id="loader">
    <div class="spinner"></div>
    <p class="loading-text">Redirecting to verification...</p>
    <p class="sub-text">Please wait while we connect you to Veriff</p>
</div>

<script>
    const sessionUrl = @json($sessionUrl);
    
    document.addEventListener('DOMContentLoaded', function() {
        if (!sessionUrl) {
            console.error('Veriff session URL missing.');
            Swal.fire({
                icon: "error",
                title: "Verification unavailable",
                text: "Unable to start verification. Please contact support.",
                allowOutsideClick: false
            }).then(() => {
                window.close();
            });
            return;
        }

        console.log('Redirecting to Veriff...');
        
        // Small delay to show loading state, then redirect
        setTimeout(function() {
            window.location.href = sessionUrl;
        }, 500);
    });
</script>
</body>

</html>
