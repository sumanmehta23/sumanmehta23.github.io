{{-- resources/views/errors/500.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Under Maintenance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            color: #000000;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            padding: 20px;
        }
        .top-image {
            max-width: 300px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 36px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #000000;
        }
        p {
            font-size: 16px;
            color: #000000;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .support-btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #00b98e;
            color: #ffffff;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }
        .support-btn:hover {
            background-color: #0b8367;
        }

        /* Mobile Responsive Adjustments */
        @media (max-width: 720px) {
            .top-image {
                max-width: 200px;
            }
            h1 {
                font-size: 30px;
            }
            p {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container">

        <!-- Top Image (Logo or Graphic) -->
        <img src="{{ $settings['logo_url'] }}" alt="Site Logo" class="top-image">

        <h1>Offline for Maintenance</h1>
        <p>
            Our site is currently undergoing maintenance to serve you better.<br>
            We’ll be back online shortly, thank you for your patience.
        </p>

        <!-- Contact Support Button -->
        <a href="https://www.lqhmarkets.com/contact-us" class="support-btn">Contact Support</a>

    </div>
</body>
</html>
