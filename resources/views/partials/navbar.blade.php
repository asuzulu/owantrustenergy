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
            <a class="nav-item nav-link" href="{{ route('register') }}">Sign Up</a>
            <a class="nav-item nav-link" href="{{ route('signin') }}">Sign In</a>
        </div>
    </div>  
</nav>
