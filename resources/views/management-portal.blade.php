@extends('layouts.app')

@section('title', 'Management Portal Login - Owan Trust Energy')

@section('content')
<!-- Management Portal Login form section start -->
<h1 class="about_text text-center" style="margin-top: 6rem;">Management Portal Login</h1>
<div class="contact_section layout_padding">
    <div class="container">
        <form action="{{ route('signin.store') }}" method="POST">
            @csrf

            <!-- Hidden input to identify portal type -->
            <input type="hidden" name="portal" value="management">

            <div class="form-row justify-content-center">
                <div class="form-group col-md-4">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="Enter Email Address" />
                </div>
            </div>

            <div class="form-row justify-content-center">
                <div class="form-group col-md-4">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="Enter Password" />
                </div>
            </div>

            <div class="form-row justify-content-center">
                <div class="form-group col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>
                </div>
            </div>

            <div class="form-row justify-content-center">
                <input type="submit" class="btn btn-primary" value="Sign In" />
            </div>

            <div class="form-row mt-3 justify-content-center">
                <div class="col-md-4 text-center">
                    <a href="{{ route('password.request') }}"><span style="color:blue;">Forgot your password?</span></a>
                </div>
            </div>

            <div class="form-row mt-2 justify-content-center">
                <div class="col-md-4 text-center">
                    <p>Don't have an account? <a href="{{ route('register') }}"><span style="color:green;">Sign Up </span></a></p>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Management Portal Login form section end -->
@endsection
