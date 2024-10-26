{{-- resources/views/partials/dashboard/navbar.blade.php --}}
<nav class="navbar navbar-expand-xl navbar-light bg-light">
    <a class="navbar-brand" href="#">
        <i class="fas fa-3x fa-tachometer-alt tm-site-icon"></i>
        <h1 class="tm-site-title mb-0">Dashboard</h1>
    </a>
    <button class="navbar-toggler ml-auto mr-0" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mx-auto">
            <li class="nav-item"><a class="nav-link active" href="#">Dashboard</a></li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" data-toggle="dropdown">Reports</a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="#">Daily Report</a>
                    <a class="dropdown-item" href="#">Weekly Report</a>
                    <a class="dropdown-item" href="#">Yearly Report</a>
                </div>
            </li>
            <li class="nav-item"><a class="nav-link" href="products.html">Products</a></li>
            <li class="nav-item"><a class="nav-link" href="accounts.html">Accounts</a></li>
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
                <a class="nav-link d-flex" href="login.html"><i class="far fa-user mr-2 tm-logout-icon"></i>Logout</a>
            </li>
        </ul>
    </div>
</nav>
