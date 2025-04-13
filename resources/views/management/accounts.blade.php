@extends(auth()->check() && auth()->user()->position === 'Manager' ? 'layouts.management-dashboard' : (auth()->check() && auth()->user()->position === 'Employee' ? 'layouts.employee-dashboard' : (auth()->check() && auth()->user()->position === 'Agent' ? 'layouts.agent-dashboard' : 'layouts.app')))

@php
    if (!auth()->check()) {
        header('Location: ' . url('/'));
        exit();
    }
@endphp

@section('content')
    <div class="container">
        <div class="row tm-content-row tm-mt-big justify-content-center" style="margin-top: -50px !important;">
            <div class="col-12 col-lg-10">
                <div class="bg-white tm-block" style="width: 100% !important; font-size: 13px !important;">
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <h2 class="tm-block-title">Customer Accounts</h2>
                                <button class="btn btn-primary" data-toggle="modal" data-target="#addCustomerModal">Add
                                    Customer</button>
                            </div>
                        </div>
                    </div>
                    <table class="table table-striped"
                        style="margin: 0 auto !important; width: 100% !important;">
                        <thead>
                            <tr style="font-size: 17px !important; font-weight: 1000 !important;">
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Age</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr onclick="window.location.href='{{ route('users.profile', $user->id) }}'"
                                    style="cursor: pointer;">
                                    <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                    <td>{{ $user->phone_number }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->city }}</td>
                                    <td>{{ $user->state }}</td>
                                    <td>{{ $user->dob ? \Carbon\Carbon::parse($user->dob)->age : 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if ($users->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $users->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer Registration Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="font-size: 13px !important;">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">Add Customer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="registerForm" method="POST" enctype="multipart/form-data">
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
                                aria-label="Street Address" />
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

                        <input type="hidden" name="position" value="Customer" />

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary" aria-label="Submit Add Customer">
                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                    aria-hidden="true"></span>
                                Add Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Customer registration was successful.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="okButton">OK</button>
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
            const form = $('#registerForm');
            form.attr('action', '{{ route('register.modal') }}'); // set action via JS after DOM is ready

            form.on('submit', function(e) {
                e.preventDefault();

                form.find('.form-control').removeClass('is-invalid');
                form.find('.text-danger').remove();

                let submitButton = form.find('button[type="submit"]');
                let spinner = submitButton.find('.spinner-border');
                spinner.removeClass('d-none');
                submitButton.prop('disabled', true).contents().last()[0].textContent = ' Registering...';

                $('#okButton').on('click', function() {
                    location.href = 'management.accounts';
                });

                const formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#successModal').modal('show');
                        $('#addCustomerModal').modal('hide');
                        form[0].reset();
                    },
                    error: function(xhr) {
                        submitButton.prop('disabled', false).text('Register');

                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, messages) {
                                let input = form.find(`[name="${key}"]`);
                                input.addClass('is-invalid');

                                // Avoid duplicates
                                if (input.next('.text-danger').length === 0) {
                                    input.after(
                                        `<span class="text-danger">${messages[0]}</span>`
                                    );
                                }
                            });
                        } else {
                            alert('An unexpected error occurred. Please try again.');
                        }
                        let firstErrorField = form.find('.is-invalid').first();
                        if (firstErrorField.length) {
                            $('#addCustomerModal').animate({
                                scrollTop: firstErrorField.offset().top - $(
                                    '#addCustomerModal').offset().top
                            }, 500);
                        }
                    }
                });
                $.each(errors, function(key, messages) {
                    let input = form.find(`[name="${key}"]`);
                    input.addClass('is-invalid');

                    if (input.next('.text-danger').length === 0) {
                        messages.forEach(msg => {
                            input.after(`<span class="text-danger">${msg}</span>`);
                        });
                    }
                });

            });
        });
    </script>
@endpush
