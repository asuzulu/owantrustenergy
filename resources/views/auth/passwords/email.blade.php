@extends('layouts.app')

@section('title', 'Forgot Password - Owan Trust Energy')

@section('content')
<!-- Password Reset Request form section start -->
<h1 class="about_text" style="margin-top: 6rem;">Forgot Your Password?</h1>
<div class="contact_section layout_padding">
    <div class="container">
        <form action="{{ route('password.email') }}" method="POST">
            @csrf <!-- Include CSRF token for form protection -->

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="Enter Email Address" />
                </div>
            </div>

            <input type="submit" class="btn btn-primary" value="Send Password Reset Link" />

            <div class="form-row mt-3">
                <div class="col-md-12 text-center">
                    <p>Remembered your password? <a href="{{ route('signin') }}">Sign In</a></p>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Password Reset Request form section end -->
@endsection
