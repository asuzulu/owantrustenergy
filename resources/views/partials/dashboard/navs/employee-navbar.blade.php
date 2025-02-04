<nav class="navbar navbar-expand-xl navbar-light bg-light">
    <a class="navbar-brand" href="/">
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
            <li class="nav-item {{ request()->routeIs('employee.cylinders') ? 'active' : '' }}">
                <a href="{{ route('employee.cylinders') }}" class="nav-link">Cylinders</a>
            </li>
            <li class="nav-item {{ request()->routeIs('employee.accounts') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('employee.accounts') }}">Customers</a>
            </li>
            <li class="nav-item {{ request()->routeIs('employee.agents') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('employee.agents') }}">Agents</a>
            </li>
            <li class="nav-item {{ request()->routeIs('employee.statistics') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('employee.statistics') }}">Statistics</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" data-toggle="dropdown">Settings</a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="#">Profile</a>
                    <a class="dropdown-item" href="#">Billing</a>
                    <a class="dropdown-item" href="#">Customize</a>
                </div>
            </li>
        </ul>
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
