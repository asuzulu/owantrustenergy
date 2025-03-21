@extends('layouts.app')

@section('title', 'Sign Up - Owan Trust Energy')

@section('content')
<!-- register form section start -->
<h1 class="about_text text-center" style="margin-top: 6rem;">Sign Up</h1>
<div class="contact_section layout_padding">
    <div class="container">
        <form action="{{ route('register.store') }}" method="POST" id="registerForm">
            @csrf <!-- Include CSRF token for form protection -->

            <div class="form-row justify-content-center">
                <div class="form-group col-md-3">
                    <label for="firstName">First Name</label>
                    <input type="text" class="form-control" id="firstName" name="firstName" required placeholder="Enter First Name" value="{{ old('firstName') }}" />
                    @error('firstName')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-md-3">
                    <label for="lastName">Last Name</label>
                    <input type="text" class="form-control" id="lastName" name="lastName" required placeholder="Enter Last Name" value="{{ old('lastName') }}" />
                    @error('lastName')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row justify-content-center">
                <div class="form-group col-md-3">
                    <label for="phoneNumber">Phone Number</label>
                    <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="Enter Phone Number" maxlength="10" value="{{ old('phoneNumber') }}" required/>
                    @error('phoneNumber')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-md-3">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="Enter Email Address" value="{{ old('email') }}" />
                    @error('email')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row justify-content-center">
                <div class="form-group col-md-3">
                    <label for="dob">Date of Birth</label>
                    <input type="date" class="form-control" id="dob" name="dob" required value="{{ old('dob') }}" />
                    @error('dob')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-md-3 text-center">
                    <label for="gender">Gender</label>
                    <div class="gender-group text-center">
                        <label for="male" class="radio-inline">
                            <input type="radio" name="gender" value="male" id="male" required {{ old('gender') == 'male' ? 'checked' : '' }} />Male
                        </label>
                        <label for="female" class="radio-inline">
                            <input type="radio" name="gender" value="female" id="female" required {{ old('gender') == 'female' ? 'checked' : '' }} />Female
                        </label>
                    </div>
                    @error('gender')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Nigerian Address Section -->
            <div class="form-row justify-content-center">
                <div class="form-group col-md-6">
                    <label for="street">Street Address</label>
                    <input type="text" class="form-control" id="street" name="street" required placeholder="Enter Street Address" value="{{ old('street') }}" />
                    @error('street')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-row justify-content-center">
                <div class="form-group col-md-3">
                    <label for="city">City</label>
                    <input type="text" class="form-control" id="city" name="city" required placeholder="Enter City" value="{{ old('city') }}" />
                    @error('city')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-md-3">
                    <label for="state">State</label>
                    <select class="form-control" id="state" name="state" required>
                        <option value="" disabled selected>Select State</option>
                        @foreach($states as $state)
                            <option value="{{ $state->id }}" {{ old('state') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                        @endforeach
                    </select>
                    @error('state')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row justify-content-center">
                <div class="form-group col-md-3">
                    <label for="bvn">BVN (Bank Verification Number)</label>
                    <input type="text" class="form-control" id="bvn" name="bvn" required placeholder="Enter 11-digit BVN" pattern="\d{11}" title="Please enter exactly 11 digits" maxlength="11" value="{{ old('bvn') }}" />
                    @error('bvn')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-md-3">
                    <label for="nin">NIN (National Identification Number)</label>
                    <input type="text" class="form-control" id="nin" name="nin" required placeholder="Enter 11-digit NIN" pattern="\d{11}" title="Please enter exactly 11 digits" maxlength="11" value="{{ old('nin') }}" />
                    @error('nin')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row justify-content-center">
                <div class="form-group col-md-3">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="At least 8 characters" />
                    @error('password')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-md-3">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Confirm Password" />
                    @error('password_confirmation')<div class="text-danger">{{ $message }}</div>@enderror
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

@section('scripts')
<script>
    @if(session('error'))
        alert("{{ session('error') }}");
    @endif
</script>
@endsection
