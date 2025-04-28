<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="logo">
        <a href="{{ route('home') }}"><img src="{{ asset('images/logo.png') }}"></a>
    </div>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
        aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav">
            <a class="nav-item nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                href="{{ route('home') }}">Home</a>
            <a class="nav-item nav-link {{ request()->routeIs('about') ? 'active' : '' }}"
                href="{{ route('about') }}">About</a>
            <a class="nav-item nav-link {{ request()->routeIs('products') ? 'active' : '' }}"
                href="{{ route('products') }}">Products</a>
            <a class="nav-item nav-link {{ request()->routeIs('contact') ? 'active' : '' }}"
                href="{{ route('contact') }}">Contact</a>

            @auth
                @if (Auth::user()->position === 'Customer')
                    <a class="nav-item nav-link {{ request()->routeIs('dashboard.profile') ? 'active' : '' }}"
                        href="{{ route('dashboard.profile') }}">MyDashboard</a>
                @elseif(Auth::user()->position === 'Manager')
                    <a class="nav-item nav-link {{ request()->routeIs('management.home') ? 'active' : '' }}"
                        href="{{ route('management.home') }}">MyDashboard</a>
                @elseif(Auth::user()->position === 'Employee')
                    <a class="nav-item nav-link {{ request()->routeIs('employee.home') ? 'active' : '' }}"
                        href="{{ route('employee.home') }}">MyDashboard</a>
                @elseif(Auth::user()->position === 'Agent')
                    <a class="nav-item nav-link {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}"
                        href="{{ route('agent.dashboard') }}">MyDashboard</a>
                @elseif(Auth::user()->position === 'Driver')
                    <a class="nav-item nav-link {{ request()->routeIs('drivers.cylinders') ? 'active' : '' }}"
                        href="{{ route('drivers.cylinders') }}">MyDashboard</a>
                @endif

                <a class="nav-item nav-link" href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Sign Out
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @endauth

            @guest
                <a class="nav-item nav-link {{ request()->routeIs('register') ? 'active' : '' }}"
                    href="{{ route('register') }}">Sign Up</a>
                <a class="nav-item nav-link {{ request()->routeIs('signin.form') ? 'active' : '' }}"
                    href="{{ route('signin.form') }}">Sign In</a>
            @endguest
        </div>
    </div>
</nav>
