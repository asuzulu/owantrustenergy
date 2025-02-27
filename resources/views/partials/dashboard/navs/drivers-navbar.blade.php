<nav class="navbar navbar-expand-xl navbar-light bg-light">
    <a class="navbar-brand" href="{{ route('drivers.cylinders') }}">
        <img src="{{ asset('images/fav-icon.png') }}" alt="Dashboard Icon" class="tm-site-icon"
            style="width: 48px; height: 48px;">
        <h1 class="tm-site-title mb-0">{{ config('app.name') }}</h1>
    </a>
    <button class="navbar-toggler ml-auto mr-0" type="button" data-toggle="collapse"
        data-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mx-auto">
            <!-- Cylinders Link -->
            <li class="nav-item {{ request()->routeIs('drivers.cylinders') ? 'active' : '' }}">
                <a href="{{ route('drivers.cylinders') }}" class="nav-link">Cylinders</a>
            </li>

            <!-- Profile Link (Updated to use the drivers profile page with matching styling) -->
            <li class="nav-item {{ request()->routeIs('drivers.profile') ? 'active' : '' }}">
                <a href="{{ route('drivers.profile', ['id' => $user->id]) }}" class="nav-link">
                    <i class="fas fa-user-circle mr-2"></i>Profile
                </a>
            </li>

            <!-- Settings Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" data-toggle="dropdown">Settings</a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="#">Settings</a>
                    <a class="dropdown-item" href="#">Billing</a>
                    <a class="dropdown-item" href="#">Customize</a>
                </div>
            </li>
        </ul>

        <!-- Logout -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <a class="nav-link d-flex" href="#"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="far fa-user mr-2 tm-logout-icon"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</nav>
