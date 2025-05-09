@extends(auth()->check() && auth()->user()->position === 'Manager' ? 'layouts.management-dashboard' : (auth()->check() && auth()->user()->position === 'Employee' ? 'layouts.employee-dashboard' : (auth()->check() && auth()->user()->position === 'Driver' ? 'layouts.drivers-dashboard' : 'layouts.default-dashboard')))

@php
    if (!auth()->check()) {
        header('Location: ' . url('/'));
        exit();
    }
@endphp

@section('content')
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">
                <div class="row">
                    <div class="col-md-2 col-sm-12">
                        <h2 class="tm-block-title">Drivers</h2>
                    </div>
                    @if (Auth::user()->position !== 'Driver')
                        <div class="col-md-10 col-sm-12">
                            <div class="d-flex flex-wrap justify-content-center align-items-start position-relative button-container mb-4"
                                style="min-height: 55px;">
                                <form method="GET" action="{{ route('management.drivers') }}" class="d-flex mb-2 mx-2">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search drivers..." value="{{ request('search') }}"
                                        aria-label="Search Drivers" style="height: 55px;">
                                </form>

                                <div class="ml-auto d-flex">
                                    <a href="{{ route('delivery.pickup') }}" class="btn btn-primary mx-2 mb-2"
                                        style="height: 100%;">
                                        Delivery Pick Up
                                    </a>
                                    <button class="btn btn-primary mx-2 mb-2" data-toggle="modal"
                                        data-target="#addDriverModal" style="height: 100%;">
                                        Add New Driver
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped tm-table-striped-even mt-3">
                        <thead>
                            <tr class="tm-bg-gray">
                                <th scope="col">Name</th>
                                <th scope="col">Phone Number</th>
                                <th scope="col">Email</th>
                                <th scope="col">City</th>
                                <th scope="col">State</th>
                                <th scope="col">Age</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($drivers as $driver)
                                <tr onclick="window.location='{{ route('drivers.profile', $driver->id) }}'"
                                    style="cursor: pointer;">
                                    <td>{{ $driver->first_name }} {{ $driver->last_name }}</td>
                                    <td>{{ $driver->phone_number }}</td>
                                    <td>{{ $driver->email }}</td>
                                    <td>{{ $driver->city }}</td>
                                    <td>{{ $driver->state }}</td>
                                    <td>{{ $driver->dob ? \Carbon\Carbon::parse($driver->dob)->age : 'N/A' }}</td>
                                </tr>
                            @endforeach
                            @if ($drivers->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center">No drivers found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if ($drivers->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $drivers->links('pagination::bootstrap-4') }}
                    </div>
                @endif

                <style>
                    @media (max-width: 1080px) {

                        /* center the whole button‐container row */
                        .button-container {
                            justify-content: center !important;
                        }

                        /* remove auto margins on each item */
                        .button-container form,
                        .button-container a,
                        .button-container button {
                            flex: 0 0 auto;
                            margin-left: 0 !important;
                            margin-right: 0 !important;
                        }
                    }

                    @media (min-width: 1079px) {
                        .customer-requests-btn {
                            /* override only on wide screens */
                            margin-left: 7rem !important;
                        }
                    }
                </style>
            </div>
        </div>
    </div>

    <!-- Add Driver Modal -->
    <div class="modal fade" id="addDriverModal" tabindex="-1" role="dialog" aria-labelledby="addDriverModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="font-size: 13px !important;">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDriverModalLabel">Add New Driver</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="driverForm" action="{{ route('drivers.store') }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="firstName">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required
                                    placeholder="Enter First Name" value="{{ old('firstName') }}"
                                    aria-label="First Name" />
                                @error('firstName')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="lastName">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required
                                    placeholder="Enter Last Name" value="{{ old('lastName') }}" aria-label="Last Name" />
                                @error('lastName')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="phoneNumber">Phone Number</label>
                                <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" required
                                    placeholder="Enter Phone Number" maxlength="10" value="{{ old('phoneNumber') }}"
                                    aria-label="Phone Number" />
                                @error('phoneNumber')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    placeholder="Enter Email Address" value="{{ old('email') }}" aria-label="Email" />
                                @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="dob">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob" required
                                    value="{{ old('dob') }}" aria-label="Date of Birth" />
                                @error('dob')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="gender">Gender</label>
                                <div class="form-check form-check-inline" style="margin-top: 2rem;">
                                    <input class="form-check-input" type="radio" name="gender" id="male"
                                        value="male" required {{ old('gender') == 'male' ? 'checked' : '' }}
                                        aria-label="Male" />
                                    <label class="form-check-label" for="male">Male</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="female"
                                        value="female" required {{ old('gender') == 'female' ? 'checked' : '' }}
                                        aria-label="Female" />
                                    <label class="form-check-label" for="female">Female</label>
                                </div>
                                @error('gender')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="street">Street Address</label>
                            <input type="text" class="form-control" id="street" name="street" required
                                placeholder="Enter Street Address" value="{{ old('street') }}"
                                aria-label="Street Address" pattern=".*[a-zA-Z]+.*"
                                title="Must be a valid stree address" />
                            @error('street')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="city">City</label>
                                <input type="text" class="form-control" id="city" name="city" required
                                    placeholder="Enter City" value="{{ old('city') }}" aria-label="City" />
                                @error('city')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="state">State</label>
                                <select class="form-control" id="state" name="state" required aria-label="State">
                                    <option value="" disabled selected>Select State</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state->id }}"
                                            {{ old('state') == $state->id ? 'selected' : '' }}>{{ $state->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('state')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="bvn">BVN (Bank Verification Number)</label>
                                <input type="text" class="form-control" id="bvn" name="bvn" required
                                    placeholder="Enter 11-digit BVN" pattern="\d{11}"
                                    title="Please enter exactly 11 digits" maxlength="11" value="{{ old('bvn') }}"
                                    aria-label="BVN" />
                                @error('bvn')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="nin">NIN (National Identification Number)</label>
                                <input type="text" class="form-control" id="nin" name="nin" required
                                    placeholder="Enter 11-digit NIN" pattern="\d{11}"
                                    title="Please enter exactly 11 digits" maxlength="11" value="{{ old('nin') }}"
                                    aria-label="NIN" />
                                @error('nin')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required
                                    placeholder="At least 8 characters" aria-label="Password" />
                                @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="password_confirmation">Confirm Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required placeholder="Confirm Password"
                                    aria-label="Confirm Password" />
                                @error('password_confirmation')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <input type="hidden" name="position" value="Driver" />

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary" aria-label="Submit Add Driver">
                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                    aria-hidden="true"></span>
                                Add Driver
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Driver Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Success</h5>
                </div>
                <div class="modal-body">
                    <p>Driver successfully added!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="reloadDriversPage" class="btn btn-primary">OK</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="{{ asset('dashboard/js/moment.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#driverForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous error messages
                $('.text-danger').remove();

                const form = $(this);
                const url = form.attr('action');
                const formData = form.serialize();
                const submitButton = form.find('button[type="submit"]');
                const spinner = submitButton.find('.spinner-border');

                // Disable button and show loading spinner
                submitButton.prop('disabled', true);
                spinner.removeClass('d-none');

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: formData,
                    success: function(response) {
                        // Reset the form
                        form[0].reset();

                        // Hide the add driver modal
                        $('#addDriverModal').modal('hide');

                        // Show success modal
                        const successModal = `
                            <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content text-center">
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title w-100" id="successModalLabel">Success</h5>
                                        </div>
                                        <div class="modal-body">
                                            New driver has been successfully added!
                                        </div>
                                        <div class="modal-footer justify-content-center border-0">
                                            <button type="button" class="btn btn-success" data-dismiss="modal" id="reloadDriversPage">
                                                OK
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('body').append(successModal);
                        $('#successModal').modal('show');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            for (const field in errors) {
                                const errorMessage =
                                    `<div class="text-danger mt-1">${errors[field][0]}</div>`;
                                $(`[name="${field}"]`).after(errorMessage);
                            }
                        } else {
                            alert('An unexpected error occurred. Please try again.');
                        }
                    },
                    complete: function() {
                        // Re-enable button and hide spinner
                        submitButton.prop('disabled', false);
                        spinner.addClass('d-none');
                    }
                });
            });

            // Refresh the page when success modal is dismissed
            $(document).on('click', '#reloadDriversPage', function() {
                location.reload();
            });
        });
    </script>
@endpush
