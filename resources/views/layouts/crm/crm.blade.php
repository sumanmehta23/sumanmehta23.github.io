<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Include head content like meta tags, title, etc. -->
    @include('layouts.crm.partials.header') <!-- Include the header partial -->
</head>
<body>
        <!-- Main Content -->
        @yield('content')

    <!-- Include footer partial -->
    @include('layouts.crm.partials.footer')
</body>
</html>
