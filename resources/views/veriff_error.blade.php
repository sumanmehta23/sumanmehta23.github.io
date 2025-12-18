<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $errorTitle ?? 'Error' }}</title>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
            integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/libs/sweetalert2/sweetalert2.min.css') }}">
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }
    </style>
</head>

<body>
<script>
    Swal.fire({
        icon: 'warning',
        title: '{{ $errorTitle ?? 'Something went wrong' }}',
        html: '{{ $errorMessage }}',
        showConfirmButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        backdrop: true,
        didOpen: () => {
            document.body.style.overflow = 'hidden';
        },
        willClose: () => {
            document.body.style.overflow = 'auto';
        }
    }).then(() => {
        // Reload the window
        if (window.parent && window.parent !== window) {
            // If we're in an iframe, reload the parent window
            window.parent.location.reload();
        } else {
            // If not in iframe, reload current window
            window.location.reload();
        }
    });
</script>
</body>

</html>

