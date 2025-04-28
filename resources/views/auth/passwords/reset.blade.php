@extends('layouts.app')

@section('title', 'Reset Password - Owan Trust Energy')

@section('content')
<!-- Password Reset form section start -->
<h1 class="about_text" style="margin-top: 6rem;">Reset Your Password</h1>
<div class="contact_section layout_padding">
    <div class="container">
        <form action="{{ route('password.update') }}" method="POST">
            @csrf <!-- Include CSRF token for form protection -->
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="Enter Email Address" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="password">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="Enter New Password" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Confirm New Password" />
                </div>
            </div>

            <input type="submit" class="btn btn-primary" value="Reset Password" />

            <div class="form-row mt-3">
                <div class="col-md-12 text-center">
                    <p>Remembered your password? <a href="{{ route('login') }}">Sign In</a></p>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Password Reset form section end -->
@endsection
