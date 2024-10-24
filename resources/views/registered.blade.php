@extends('layouts.app')

@section('title', 'Registered - Owan Trust Energy')

@section('content')
<!-- Thank You Section Start -->
<h1 class="about_text" style="margin-top: 6rem;">
    <span style="color: rgb(51, 51, 51);">Thank you for Registering with</span><br>OWAN TRUST ENERGY
</h1>

<div class="contact_section layout_padding">
    <div class="container">
        <h2 style="color: white; text-align: center;">
            Thank you for choosing Owan Trust Energy! We greatly appreciate your decision to register with us. By doing so, you've not only shown trust in our products and services but also become a valued member of our community. Your registration enables us to better serve you, ensuring that you receive the utmost care and attention as our customer.
        </h2>
    </div>
    <a href="{{ route('home') }}" class="btn btn-primary" style="width: 200px; margin-top: 60px;">Return to Homepage</a>
</div>
<!-- Thank You Section End -->
@endsection
