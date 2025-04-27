@extends('layouts.app')

@section('title', 'Sign In - Owan Trust Energy')

@section('content')
    <!-- Sign In form section start -->
    <h1 class="about_text text-center" style="margin-top: 6rem;">Sign In</h1>
    <div class="contact_section layout_padding">
        <div class="container">
            <form action="{{ route('signin.store') }}" method="POST">
                @csrf
                <div class="form-row justify-content-center">
                    <div class="form-group col-md-4">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required
                            placeholder="Enter Email Address" />
                    </div>
                </div>
                <div class="form-row justify-content-center">
                    <div class="form-group col-md-4">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required
                            placeholder="Enter Password" />
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
                        <p>Don't have an account? <a href="{{ route('register') }}"><span style="color:green;">Sign Up</span></a></p>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Sign In form section end -->

    <!-- Error Modal (hidden by default) -->
    <div id="customErrorModal" class="modal" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="errorModalLabel">Sign In Error</h5>
                    <button type="button" class="btn-close close-custom" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if(session('login_error'))
                        <p>{{ session('login_error') }}</p>
                    @elseif($errors->has('old_password'))
                        <p>{{ $errors->first('old_password') }}</p>
                    @else
                        <p>Something went wrong. Please try again.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-custom">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Only inject styles & script if there’s an error --}}
    @if(session('login_error') || $errors->has('old_password'))
        <style>
            /* full-screen backdrop */
            #customErrorModal + .backdrop {
                position: fixed;
                top: 0; left: 0;
                width: 100%; height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
            }
            /* modal container */
            #customErrorModal {
                position: fixed;
                top: 0; left: 0;
                width: 100%; height: 100%;
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 1050;
            }
            /* show it */
            #customErrorModal.show {
                display: flex !important;
            }
        </style>
        <!-- backdrop element -->
        <div class="backdrop"></div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('customErrorModal');
                // show modal
                modal.classList.add('show');
                // wire up close buttons
                Array.from(document.querySelectorAll('.close-custom')).forEach(btn => {
                    btn.addEventListener('click', () => {
                        modal.classList.remove('show');
                        const bd = document.querySelector('.backdrop');
                        if (bd) bd.remove();
                    });
                });
            });
        </script>
    @endif
@endsection
