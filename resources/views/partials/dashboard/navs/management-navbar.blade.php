<style>
    /* Scale the image down to 2/3 of its current size */
    .tm-site-icon {
        width: 32px;  /* 48px * 2/3 ≈ 32px */
        height: 32px;
    }
    /* Reduce the font size of the title and nav links */
    .tm-site-title,
    .nav-link {
        font-size: 0.67em; /* 2/3 of the inherited font-size */
    }
  </style>

  <nav class="navbar navbar-expand-xl navbar-light bg-light">
      <a class="navbar-brand" href="/">
          <img src="{{ asset('images/fav-icon.png') }}" alt="Dashboard Icon" class="tm-site-icon">
          <h1 class="tm-site-title mb-0">{{ config('app.name') }}</h1>
      </a>
      <button class="navbar-toggler ml-auto mr-0" type="button" data-toggle="collapse"
          data-target="#navbarSupportedContent">
          <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav mx-auto">
              <li class="nav-item {{ request()->routeIs('management.cylinders') ? 'active' : '' }}">
                  <a href="{{ route('management.cylinders') }}" class="nav-link">Cylinders</a>
              </li>
              <li class="nav-item {{ request()->routeIs('management.accounts') ? 'active' : '' }}">
                  <a class="nav-link" href="{{ route('management.accounts') }}">Customers</a>
              </li>
              <li class="nav-item {{ request()->routeIs('management.employees') ? 'active' : '' }}">
                  <a class="nav-link" href="{{ route('management.employees') }}">Employees</a>
              </li>
              <li class="nav-item {{ request()->routeIs('management.agents') ? 'active' : '' }}">
                  <a class="nav-link" href="{{ route('management.agents') }}">Agents</a>
              </li>
              <li class="nav-item {{ request()->routeIs('management.drivers') ? 'active' : '' }}">
                  <a class="nav-link" href="{{ route('drivers.index') }}">Drivers</a>
              </li>
              <li class="nav-item {{ request()->routeIs('warehouses.index') ? 'active' : '' }}">
                  <a href="{{ route('warehouses.index') }}" class="nav-link">Warehouses</a>
              </li>
              <li class="nav-item {{ request()->routeIs('management.statistics') ? 'active' : '' }}">
                  <a class="nav-link" href="{{ route('management.statistics') }}">Statistics</a>
              </li>
              <li class="nav-item dropdown {{ request()->is('settings*') ? 'active' : '' }}">
                  <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown"
                      data-toggle="dropdown">Settings</a>
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
