<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.dashboard.head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body id="reportsPage">
    <div id="home" class="">
        <div class="container">
            {{-- Navbar --}}
            @include('partials.dashboard.navs.management-navbar')

            {{-- Page Content --}}
            <div class="row tm-content-row tm-mt-big">
                @yield('content')
            </div>

            @php
                $allowedPositions = ['Customer', 'Employee', 'Manager', 'Agent', 'Driver'];
            @endphp

            @if (Auth::check() && in_array(Auth::user()->position, $allowedPositions))
                {{-- Normal content rendering --}}
            @else
                <script>
                    window.location.href = "{{ url('/') }}";
                </script>
            @endif

            {{-- Footer --}}
            @include('partials.dashboard.footer')
        </div>
    </div>

    @stack('scripts')
    @include('partials.dashboard.scripts')
</body>

</html>
