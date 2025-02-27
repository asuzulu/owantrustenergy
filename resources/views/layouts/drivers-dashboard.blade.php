<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.dashboard.head')
</head>

<body id="reportsPage">
    <div id="home" class="">
        <div class="container">
            {{-- Navbar --}}
            @if(Auth::check() && Auth::user()->position === 'Driver')
                @include('partials.dashboard.navs.drivers-navbar', ['user' => $user ?? Auth::user()])
            @endif

            {{-- Page Content --}}
            <div class="row tm-content-row tm-mt-big">
                @yield('content')
            </div>

            {{-- Footer --}}
            @include('partials.dashboard.footer')
        </div>
    </div>

    @include('partials.dashboard.scripts')
</body>

</html>
