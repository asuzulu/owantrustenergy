@extends('layouts.app')

@section('title', 'Sign Up - Owan Trust Energy')

@section('content')
<!-- register form section start -->
<h1 class="about_text text-center" style="margin-top: 6rem;">Sign Up</h1>
<div class="contact_section layout_padding">
    <div class="container">
        <form action="{{ route('register.store') }}" method="POST">
            @csrf <!-- Include CSRF token for form protection -->

            <div class="form-row justify-content-center">
                <div class="form-group col-md-3">
                    <label for="firstName">First Name</label>
                    <input type="text" class="form-control" id="firstName" name="firstName" required placeholder="Enter First Name" />
                </div>
                <div class="form-group col-md-3">
                    <label for="lastName">Last Name</label>
                    <input type="text" class="form-control" id="lastName" name="lastName" required placeholder="Enter Last Name" />
                </div>
            </div>

            <div class="form-row justify-content-center">
                <div class="form-group col-md-3">
                    <label for="phoneNumber">Phone Number</label>
                    <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" required placeholder="Enter Phone Number" />
                </div>
                <div class="form-group col-md-3">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="Enter Email Address" />
                </div>
            </div>

            <div class="form-row justify-content-center">
                <div class="form-group col-md-6 text-center">
                    <label for="gender">Gender</label>
                    <div class="gender-group text-center">
                        <label for="male" class="radio-inline">
                            <input type="radio" name="gender" value="male" id="male" required />Male
                        </label>
                        <label for="female" class="radio-inline">
                            <input type="radio" name="gender" value="female" id="female" required />Female
                        </label>
                    </div>
                </div>
            </div>

            <!-- Nigerian Address Section -->
            <div class="form-row justify-content-center">
                <div class="form-group col-md-6">
                    <label for="street">Street Address</label>
                    <input type="text" class="form-control" id="street" name="street" required placeholder="Enter Street Address" />
                </div>
            </div>
            <div class="form-row justify-content-center">
                <div class="form-group col-md-3">
                    <label for="city">City</label>
                    <input type="text" class="form-control" id="city" name="city" required placeholder="Enter City" />
                </div>
                <div class="form-group col-md-3">
                    <label for="state">State</label>
                    <select class="form-control" id="state" name="state_id" required>
                        <option value="" disabled selected>Select State</option>
                        @foreach($states as $state)
                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row justify-content-center">
                <div class="form-group col-md-3">
                    <label for="bvn">BVN (Bank Verification Number)</label>
                    <input type="text" class="form-control" id="bvn" name="bvn" required placeholder="Enter 11-digit BVN" pattern="\d{11}" title="Please enter exactly 11 digits" maxlength="11" />
                </div>
                <div class="form-group col-md-3">
                    <label for="nin">NIN (National Identification Number)</label>
                    <input type="text" class="form-control" id="nin" name="nin" required placeholder="Enter 11-digit NIN" pattern="\d{11}" title="Please enter exactly 11 digits" maxlength="11" />
                </div>
            </div>

            <div class="form-row justify-content-center">
                <div class="form-group col-md-3">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="At least 8 characters" />
                </div>
                <div class="form-group col-md-3">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Confirm Password" />
                </div>
            </div>

            <div class="form-row justify-content-center">
                <input type="submit" class="btn btn-primary" />
            </div>
        </form>
    </div>
</div>
<!-- register form section end -->
@endsection
