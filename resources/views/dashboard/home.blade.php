@extends('layouts.user-dashboard')

@section('content') <!-- Start of the section -->
    <div class="container">
        <div class="row tm-content-row tm-mt-big">
            <div class="tm-col tm-col-big">
                <div class="bg-white tm-block">
                    <div class="row">
                        <div class="col-24">
                            <h2 class="tm-block-title">Account Details</h2>
                            <!-- Display logged-in user's details -->
                            Name: {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                            <br>
                            Email: {{ Auth::user()->email }}
                            <br>
                            Gender: {{ Auth::user()->gender }}
                            <br>
                            Phone: {{ Auth::user()->phone_number }}
                            <br>
                            Street: {{ Auth::user()->street }}
                            <br>
                            City: {{ Auth::user()->city }}
                            <br>
                            State: {{ Auth::user()->state }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="tm-col tm-col-small">
                <div class="bg-white tm-block">
                    <!-- Include the Profile Image Display partial -->
                    @include('partials.dashboard.profile-image.display')
                </div>
            </div>
        </div>

        <!-- Display the total number of cylinders assigned -->
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block">
                <h3 class="tm-block-title">Cylinders Assigned</h3>
                <p>You have been assigned a total of {{ $totalCylinders }} cylinder(s).</p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('partials.dashboard.scripts')
@endsection
