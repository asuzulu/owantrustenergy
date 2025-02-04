@extends('layouts.app')

@section('title', 'Owan Trust Energy Home')

@section('content')
<!-- banner section start -->
<div class="banner_section layout_padding">
    <div class="container">
        <section class="slide-wrapper">
            <div class="container">
                <div id="myCarousel" class="carousel slide" data-ride="carousel">
                    <div class="text_style">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <h1 class="yoga_text" style="color: white; text-shadow: 2px 2px 0px #00b400;">Owan Trust Energy</h1>
                                <h2 class="font_style3"><b>Serving the People of Nigeria.</b></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Separate Banner Text Section -->
<div class="banner_text">
    <div class="container">
        <p class="font_style4">
            <strong>Gas Cylinders:</strong> Track your cylinder with AI Technology
            <br>
            <strong>Gas Stoves and Burners:</strong> A blend of world-class engineering with a local touch
            <br>
            <strong>Accessories:</strong> Hoses, control valves, grease trays, counter traps, countertops, etc.
        </p>
        <div class="contact_bt">
            <a href="{{ route('contact') }}">Contact Us</a>
        </div>
    </div>
</div>

<!-- banner section end -->

<!-- trainer section start -->
<div class="trainer_section layout_padding">
    <div class="container">
        <div class="row">
            <div class="col-sm-3">
                <div class="image_1">
                    <img src="{{ asset('images/cylinder-logo.png') }}" alt="Gas Cylinders">
                </div>
                <h1 class="meditation_text">Gas Cylinders</h1>
            </div>
            <div class="col-sm-3">
                <div class="image_1">
                    <img src="{{ asset('images/stove-logo.png') }}" alt="Gas Stoves & Burners">
                </div>
                <h4 class="meditation_text">Gas Stoves & Burners</h4>
            </div>
            <div class="col-sm-3">
                <div class="image_1">
                    <img src="{{ asset('images/accessories-logo.png') }}" alt="Accessories">
                </div>
                <h1 class="meditation_text">Accessories</h1>
            </div>
        </div>
    </div>
</div>
<!-- trainer section end -->

<!-- about section start -->
<div class="about_section layout_padding2">
    <div class="container">
        <div class="about_main">
            <h1 class="about_text">Owan Trust Energy</h1>
            <p class="ipsum_text"><strong>Owan Trust Energy</strong>, based in Ojavun, Owan East, Edo State, Nigeria, specializes in selling and refilling branded gas cylinders, alongside offering efficient gas stoves, burners, and various kitchen accessories...</p>
        </div>
        <div class="about_bt_main">
            <div class="about_bt">
                <a href="{{ route('about') }}">More</a>
            </div>
        </div>
    </div>
</div>
<!-- about section end -->

<!-- client section start -->
<div class="client_section layout_padding">
    <div class="container">
        <h1 class="costomer_taital">Our Customers Say</h1>
        <div id="main_slider" class="carousel slide" data-ride="carousel">
            <!-- Indicators -->
            <ol class="carousel-indicators">
                <li data-target="#main_slider" data-slide-to="0" class="active"></li>
                <li data-target="#main_slider" data-slide-to="1"></li>
                <li data-target="#main_slider" data-slide-to="2"></li>
            </ol>

            <!-- Carousel Inner -->
            <div class="carousel-inner">
                <!-- First Slide -->
                <div class="carousel-item active">
                    <div class="client_section_2">
                        <div class="row">
                            <div class="col-md-4 offset-md-2"> <!-- Added offset to move content to the right -->
                                <div class="client_icon">
                                    <img src="{{ asset('images/client-icon.png') }}" alt="Client">
                                    <h1 class="carklo_text">Chinonso A.</h1>
                                    <p class="lorem_dolar_text">University Student, Benin City</p>
                                    <p class="ipsum_dolor_text">"I'm really into Owan Trust Energy's cylinders. They're safe and reliable, and tracking them with those unique numbers is pretty cool."</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="client_icon">
                                    <img src="{{ asset('images/client-icon.png') }}" alt="Client">
                                    <h1 class="carklo_text">Esosa O.</h1>
                                    <p class="lorem_dolar_text">Mother of Three, Ekpoma</p>
                                    <p class="ipsum_dolor_text">"Just got our new gas cylinder from Owan Trust Energy and it's top-notch! Plus, their loyalty program is a sweet deal for families like ours."</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second Slide -->
                <div class="carousel-item">
                    <div class="client_section_2">
                        <div class="row">
                            <div class="col-md-4 offset-md-2"> <!-- Added offset to move content to the right -->
                                <div class="client_icon">
                                    <img src="{{ asset('images/client-icon.png') }}" alt="Client">
                                    <h1 class="carklo_text">Ehis I.</h1>
                                    <p class="lorem_dolar_text">Young Professional, Auchi</p>
                                    <p class="ipsum_dolor_text">"Their cylinders are easy to track and the loyalty rewards are a nice touch. It's good to see a company that adds a personal touch to their products."</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="client_icon">
                                    <img src="{{ asset('images/client-icon.png') }}" alt="Client">
                                    <h1 class="carklo_text">Ose K.</h1>
                                    <p class="lorem_dolar_text">Graduate Student, Benin City</p>
                                    <p class="ipsum_dolor_text">"The unique serial numbers are a smart idea. It's great to see a company innovating in such a practical way."</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Third Slide -->
                <div class="carousel-item">
                    <div class="client_section_2">
                        <div class="row">
                            <div class="col-md-4 offset-md-2"> <!-- Added offset to move content to the right -->
                                <div class="client_icon">
                                    <img src="{{ asset('images/client-icon.png') }}" alt="Client">
                                    <h1 class="carklo_text">Isoken U.</h1>
                                    <p class="lorem_dolar_text">Small Business Owner, Uromi</p>
                                    <p class="ipsum_dolor_text">"Their cylinders are top quality, and the unique serial numbers make reordering and tracking so easy."</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="client_icon">
                                    <img src="{{ asset('images/client-icon.png') }}" alt="Client">
                                    <h1 class="carklo_text">Osariemen O.</h1>
                                    <p class="lorem_dolar_text">Homemaker, Okada</p>
                                    <p class="ipsum_dolor_text">"The safety and tracking system give me peace of mind. Plus, their loyalty program is a great bonus."</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <a class="carousel-control-prev" href="#main_slider" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#main_slider" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </div>
</div>
<!-- client section end -->

@endsection
