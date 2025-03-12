<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.dashboard.head')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Ensure jQuery and Bootstrap are included -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>

<body id="reportsPage">
    <div id="home">
        <div class="container-fluid"> <!-- Changed to container-fluid for better responsiveness -->
            {{-- Navbar --}}
            @include('partials.dashboard.navs.customer-navbar')

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

    {{-- Global Scripts --}}
    @include('partials.dashboard.scripts')

    {{-- Page-Specific Scripts --}}
    @yield('scripts')
</body>

</html>
