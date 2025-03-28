<nav class="navbar navbar-expand-xl navbar-light bg-light">
    <a class="navbar-brand" href="/">
        <img src="{{ asset('images/fav-icon.png') }}" alt="Dashboard Icon" class="tm-site-icon" style="width: 48px; height: 48px;">
        <h1 class="tm-site-title mb-0">{{ config('app.name') }}</h1>
    </a>
    <button class="navbar-toggler ml-auto mr-0" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mx-auto">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard.profile') ? 'active' : '' }}" href="{{ route('dashboard.profile') }}">
                    <i class="fas fa-user-circle mr-2"></i>Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard.cylinder') ? 'active' : '' }}" href="{{ route('dashboard.cylinder', ['userId' => Auth::id()]) }}">
                    Cylinders
                </a>
            </li>
            <!-- New Order Cylinder Nav Link -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard.ordercylinder') ? 'active' : '' }}" href="{{ route('dashboard.ordercylinder') }}">
                    <i class="fas fa-shopping-cart mr-2"></i>Order Cylinders
                </a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {{ request()->is('settings/*') ? 'active' : '' }}" href="#" id="navbarDropdown" data-toggle="dropdown">
                    Settings
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item {{ request()->is('settings/profile') ? 'active' : '' }}" href="#">Profile</a>
                    <a class="dropdown-item {{ request()->is('settings/billing') ? 'active' : '' }}" href="#">Billing</a>
                </div>
            </li>
        </ul>
        <ul class="navbar-nav">
            <li class="nav-item">
                @if(Auth::check())
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a class="nav-link d-flex" href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="far fa-user mr-2 tm-logout-icon"></i>Logout
                    </a>
                @else
                    <a class="nav-link" href="/">Home</a>
                @endif
            </li>
        </ul>
    </div>
</nav>
