<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.dashboard.head')
</head>
<body id="reportsPage">
    <div id="home" class="">
        <div class="container">
            {{-- Navbar --}}
            @include('partials.dashboard.navs.agent-navbar')

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
