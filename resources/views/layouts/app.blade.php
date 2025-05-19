<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Owan Trust Energy')</title>
    <meta name="description" content="@yield('description', 'Owan Trust Energy offers gas cylinders, stoves, burners, and kitchen accessories.')">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/fav-icon.png') }}" type="image/x-icon">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/meanmenu.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery.mCustomScrollbar.concat.min.css') }}">

    <!-- jQuery (only once) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Plugins -->
    <script src="{{ asset('js/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('js/jquery.meanmenu.js') }}"></script>
    <script src="{{ asset('js/jquery.sticky.js') }}"></script>
    <script src="{{ asset('js/jquery.mCustomScrollbar.concat.min.js') }}"></script>
    <script src="{{ asset('js/plugin.js') }}"></script>

    <!-- Fancybox v3 CSS and JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>

    <!-- Bootstrap Icons (free) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

    <!-- Bootstrap: Use Bootstrap 5 from CDN if useBootstrap5 is set, else fallback to local Bootstrap 4 -->
    @if (isset($useBootstrap5) && $useBootstrap5)
        <!-- Bootstrap 5 Bundle (includes Popper) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @else
        <!-- Bootstrap 4 (Popper + Bootstrap 4 separate) -->
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <!-- Local fallback Bootstrap 4 Bundle -->
        <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    @endif
</head>

<body>
    @include('partials.navbar')

    <div class="content">
        @yield('content')
    </div>

    @include('partials.footer')

    <!-- Custom Scripts -->
    <script src="{{ asset('js/custom.js') }}"></script>

    @yield('scripts')
    @stack('scripts')

    <!-- Vite app.js must come last -->
    @vite('resources/js/app.js')

</body>

</html>
