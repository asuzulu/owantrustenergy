<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="logo"><a href="{{ route('home') }}"><img src="{{ asset('images/logo.png') }}"></a></div>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup"
        aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav">
            <a class="nav-item nav-link" href="{{ route('home') }}">Home</a>
            <a class="nav-item nav-link" href="{{ route('about') }}">About</a>
            <a class="nav-item nav-link" href="{{ route('products') }}">Products</a>
            <a class="nav-item nav-link" href="{{ route('contact') }}">Contact Us</a>

            @if(Auth::check())
                <a class="nav-item nav-link" href="{{ route('dashboard.home') }}">{{ Auth::user()->full_name }}</a>
                <a class="nav-item nav-link" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sign Out</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @else
                <a class="nav-item nav-link" href="{{ route('register') }}">Sign Up</a>
                <a class="nav-item nav-link" href="{{ route('signin.form', ['portal' => 'customer']) }}">Sign In</a>
            @endif
        </div>
    </div>
</nav>
