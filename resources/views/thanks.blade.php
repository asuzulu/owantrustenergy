@extends('layouts.app')

@section('title', 'Owan Trust Energy - Thank You')

@section('content')
<!-- submitted section start -->
<h1 class="about_text" style="margin-top: 6rem;">
    <span style="color: rgb(51, 51, 51);">Thank you for reaching out to</span><br>OWAN TRUST ENERGY
</h1>
<div class="contact_section layout_padding">
    <div class="container">
        <h2 style="color: white; text-align: center;">
            Thank you for reaching out to Owan Trust Energy! Your message has been received, and we appreciate you taking the time to contact us. Our team is dedicated to providing prompt and efficient assistance to all inquiries. Rest assured, we will review your message carefully and get back to you as soon as possible. If you have any further questions or concerns in the meantime, feel free to explore our website for additional information about our products and services. Thank you for choosing Owan Trust Energy, and we look forward to speaking with you soon!
        </h2>
    </div>
    <a href="{{ route('home') }}" class="btn btn-primary" style="width: 200px; margin-top: 60px;">Return to Homepage</a>
</div>
<!-- submitted section end -->
@endsection
