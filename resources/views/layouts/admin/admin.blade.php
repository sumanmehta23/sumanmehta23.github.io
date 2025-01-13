
    @include('layouts.admin.partials.header')

    @yield('content')
    @include('layouts.admin.partials.footer')
    @stack('scripts')
</body>

</html>
