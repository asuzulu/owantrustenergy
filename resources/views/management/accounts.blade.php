@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : 'layouts.agent-dashboard'))

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
                        style="margin: 0 auto !important; width: 100% !important; font-size: 13px !important;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Number</th>
                                <th>Email</th>
                                <th>Position</th>
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
                                    <td>{{ $user->position }}</td>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
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
                    <!-- Centering the form with mx-auto and defined width -->
                    <form action="{{ route('register.store') }}" method="POST" id="registerForm" class="mx-auto"
                        style="width: 90% !important;">
                        @csrf
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>
                        <div class="form-group">
                            <label for="phoneNumber">Phone Number</label>
                            <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" class="form-control" id="dob" name="dob" required>
                        </div>
                        <div class="form-group">
                            <label>Gender</label><br>
                            <label class="radio-inline"><input type="radio" name="gender" value="male" required>
                                Male</label>
                            <label class="radio-inline"><input type="radio" name="gender" value="female" required>
                                Female</label>
                        </div>
                        <div class="form-group">
                            <label for="street">Street Address</label>
                            <input type="text" class="form-control" id="street" name="street" required>
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <select class="form-control" id="state" name="state" required style="height: calc(4rem);">
                                <option value="" disabled selected>Select State</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bvn">BVN</label>
                            <input type="text" class="form-control" id="bvn" name="bvn" required
                                pattern="\d{11}" maxlength="11">
                        </div>
                        <div class="form-group">
                            <label for="nin">NIN</label>
                            <input type="text" class="form-control" id="nin" name="nin" required
                                pattern="\d{11}" maxlength="11">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('partials.dashboard.scripts')
    <script>
        $(document).ready(function() {
            $('#registerForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                var formData = $(this).serialize(); // Get form data

                $.ajax({
                    type: 'POST',
                    url: '{{ route('register.modal') }}',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#addCustomerModal').modal('hide'); // Hide modal
                            location.reload(); // Reload page to reflect changes
                        } else {
                            alert(response.message || 'Registration failed. Please try again.');
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = 'An error occurred.';
                        if (errors) {
                            errorMessage = Object.values(errors).map(err => err.join(' ')).join('\n');
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        alert(errorMessage);
                    }
                });
            });
        });
    </script>
@endsection
