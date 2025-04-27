@if (Auth::user()->position === 'Customer' || Auth::user()->position === 'Agent')
    <script>
        window.location.href = "{{ route('dashboard.profile') }}";
    </script>
@else
    @extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : 'layouts.app'))
@endif

@section('content')
    <div class="container">
        <div class="row tm-content-row tm-mt-big justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="bg-white tm-block">
                    <h2 class="tm-block-title">Edit User Profile</h2>
                    <form id="editUserForm" action="{{ route('users.update', $user->id) }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                value="{{ old('first_name', $user->first_name) }}" required pattern="^[a-zA-Z\s]+$"
                                title="First name can only contain letters and spaces.">
                            <div class="invalid-feedback">Please enter a valid first name.</div>
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                value="{{ old('last_name', $user->last_name) }}" required pattern="^[a-zA-Z\s]+$"
                                title="Last name can only contain letters and spaces.">
                            <div class="invalid-feedback">Please enter a valid last name.</div>
                        </div>

                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number" maxlength="10"
                                value="{{ old('phone_number', $user->phone_number) }}" required pattern="^\d{10}$"
                                title="Phone number must be exactly 10 digits.">
                            <div class="invalid-feedback">Please enter a valid 10-digit phone number.</div>
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select class="form-control" id="gender" name="gender" required style="height: 60px;">
                                <option value="" disabled>Select Gender</option>
                                <option value="male" {{ $user->gender === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $user->gender === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ $user->gender === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <div class="invalid-feedback">Please select a gender.</div>
                        </div>

                        <div class="form-group">
                            <label for="street">Street</label>
                            <input type="text" class="form-control" id="street" name="street"
                                value="{{ old('street', $user->street) }}" required pattern=".*[a-zA-Z]+.*"
                                title="Street address must contain at least one letter.">
                            <div class="invalid-feedback">Please enter a valid street address.</div>
                        </div>

                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" class="form-control" id="city" name="city"
                                value="{{ old('city', $user->city) }}" required pattern="^[a-zA-Z\s]+$"
                                title="City can only contain letters and spaces.">
                            <div class="invalid-feedback">Please enter a valid city.</div>
                        </div>

                        <div class="form-group">
                            <label for="state">State</label>
                            <select class="form-control" id="state" name="state" required style="height: calc(4rem);">
                                <option value="" disabled>Select State</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}"
                                        {{ $user->state === $state->name ? 'selected' : '' }}>
                                        {{ $state->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a state.</div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email', $user->email) }}" required
                                pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                                title="Enter a valid email address like example@domain.com">
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                    </form> <!-- CLOSE editUserForm HERE -->

                    @if (Auth::user()->position === 'Manager')
                        <div class="d-flex justify-content-center align-items-center gap-2 mt-3 flex-wrap">
                            {{-- Save Changes Button (outside the form) --}}
                            <button type="button" id="saveChangesBtn" class="btn btn-primary">Save Changes</button>

                            {{-- Delete button form (separate) --}}
                            <form id="deleteUserForm" action="{{ route('users.destroy', $user->id) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>

                            {{-- Cancel button --}}
                            <a href="{{ route('users.profile', $user->id) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Error Modal --}}
    <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Validation Errors</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul id="errorList" class="mb-0"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Success Modal --}}
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel"
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
                    <p id="successMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- Bootstrap JS (make sure it's included on your page) --}}
    <script>
        $(document).ready(function() {
            // Handle Save Changes button click
            $("#saveChangesBtn").click(function(event) {
                event.preventDefault();
                // perform HTML5 validation
                let form = document.getElementById('editUserForm');
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }
                let formData = $("#editUserForm").serialize();
                $.ajax({
                    url: $("#editUserForm").attr('action'),
                    type: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#successMessage').text(response.message);
                            $('#successModal').modal('show');
                            $('#successModal').on('hidden.bs.modal', function() {
                                window.location.href = response.redirect;
                            });
                        } else {
                            $('#errorList').empty();
                            if (response.errors) {
                                $.each(response.errors, function(key, messages) {
                                    messages.forEach(function(msg) {
                                        $('#errorList').append('<li>' + msg +
                                            '</li>');
                                    });
                                });
                            } else {
                                $('#errorList').append('<li>' + response.message + '</li>');
                            }
                            $('#errorModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON;
                        $('#errorList').empty();
                        if (res && res.errors) {
                            $.each(res.errors, function(key, msgs) {
                                $('#errorList').append('<li>' + msgs[0] + '</li>');
                            });
                        } else {
                            $('#errorList').append(
                                '<li>An error occurred while updating the user profile.</li>'
                            );
                        }
                        $('#errorModal').modal('show');
                    }
                });
            });

            // Handle Delete User button click
            $("#deleteUserForm").submit(function(event) {
                event.preventDefault();
                if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                    let formData = $(this).serialize();
                    $.ajax({
                        url: $(this).attr('action'),
                        type: "POST",
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#successMessage').text(response.message);
                                $('#successModal').modal('show');
                                $('#successModal').on('hidden.bs.modal', function() {
                                    window.location.href = response.redirect;
                                });
                            } else {
                                $('#errorList').empty();
                                $('#errorList').append('<li>' + response.message + '</li>');
                                $('#errorModal').modal('show');
                            }
                        },
                        error: function(xhr) {
                            $('#errorList').empty();
                            $('#errorList').append(
                                '<li>An error occurred while deleting the user.</li>');
                            $('#errorModal').modal('show');
                        }
                    });
                }
            });
        });
    </script>
@endsection
