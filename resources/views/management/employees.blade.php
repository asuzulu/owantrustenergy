<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

@extends(auth()->check() && auth()->user()->position === 'Manager' ? 'layouts.management-dashboard' : (auth()->check() && auth()->user()->position === 'Employee' ? 'layouts.employee-dashboard' : (auth()->check() && auth()->user()->position === 'Agent' ? 'layouts.agent-dashboard' : 'layouts.default-dashboard')))

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
                        <h2 class="tm-block-title">Employees</h2>
                    </div>
                    @if (Auth::user()->position !== 'Agent')
                        <div class="col-md-10 col-sm-12">
                            <div class="d-flex flex-wrap justify-content-center align-items-start position-relative button-container mb-4" style="min-height: 55px;">
                                <form method="GET" action="{{ route('management.employees') }}" class="d-flex mb-2 mx-2">
                                    <input type="text" name="search" class="form-control" placeholder="Search employees..." value="{{ request('search') }}" aria-label="Search Employees" style="height: 55px;">
                                </form>

                                @if (Auth::user()->position !== 'Agent')
                                    <button class="btn btn-primary mx-2 mb-2 ml-auto" data-toggle="modal" data-target="#addEmployeeModal" style="height: 100%;">
                                        Add New Employee
                                    </button>
                                @endif
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
                            @foreach ($employees as $employee)
                                <tr onclick="window.location='{{ route('employees.show', $employee->id) }}'" style="cursor: pointer;">
                                    <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                                    <td>{{ $employee->phone_number }}</td>
                                    <td>{{ $employee->email }}</td>
                                    <td>{{ $employee->city }}</td>
                                    <td>{{ $employee->state }}</td>
                                    <td>{{ $employee->dob ? \Carbon\Carbon::parse($employee->dob)->age : 'N/A' }}</td>
                                </tr>
                            @endforeach

                            @if ($employees->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center">No employees found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if ($employees->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $employees->links('pagination::bootstrap-4') }}
                    </div>
                @endif

            </div>
        </div>
    </div>

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

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="employeeForm" action="{{ route('employees.store') }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="firstName">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required placeholder="Enter First Name" value="{{ old('firstName') }}" />
                                @error('firstName')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="lastName">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required placeholder="Enter Last Name" value="{{ old('lastName') }}" />
                                @error('lastName')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="phoneNumber">Phone Number</label>
                                <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" required placeholder="Enter Phone Number" maxlength="10" value="{{ old('phoneNumber') }}" />
                                @error('phoneNumber')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="Enter Email Address" value="{{ old('email') }}" />
                                @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="dob">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob" required value="{{ old('dob') }}" />
                                @error('dob')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="gender">Gender</label>
                                <div class="form-check form-check-inline" style="margin-top: 2rem;">
                                    <input class="form-check-input" type="radio" name="gender" id="male" value="male" required {{ old('gender') == 'male' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="male">Male</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="female" value="female" required {{ old('gender') == 'female' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="female">Female</label>
                                </div>
                                @error('gender')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="street">Street Address</label>
                            <input type="text" class="form-control" id="street" name="street" required placeholder="Enter Street Address" value="{{ old('street') }}" />
                            @error('street')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="city">City</label>
                                <input type="text" class="form-control" id="city" name="city" required placeholder="Enter City" value="{{ old('city') }}" />
                                @error('city')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="state">State</label>
                                <select class="form-control" id="state" name="state" required>
                                    <option value="" disabled selected>Select State</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state->id }}" {{ old('state') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
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
                                <input type="text" class="form-control" id="bvn" name="bvn" required placeholder="Enter 11-digit BVN" pattern="\d{11}" title="Please enter exactly 11 digits" maxlength="11" value="{{ old('bvn') }}" />
                                @error('bvn')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="nin">NIN (National Identification Number)</label>
                                <input type="text" class="form-control" id="nin" name="nin" required placeholder="Enter 11-digit NIN" pattern="\d{11}" title="Please enter exactly 11 digits" maxlength="11" value="{{ old('nin') }}" />
                                @error('nin')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="At least 8 characters" />
                                @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="password_confirmation">Confirm Password</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Confirm Password" />
                                @error('password_confirmation')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <input type="hidden" name="position" value="Employee" />

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">Add Employee</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('partials.dashboard.scripts')
    <script>
        $(document).ready(function() {
            $("#employeeForm").submit(function(event) {
                event.preventDefault();

                // Clear previous errors
                $('.text-danger').remove();
                $('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: "{{ route('employees.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        // Show success message (use your own modal if you like)
                        alert("Employee added successfully!");
                        location.reload(); // Or dynamically update the table if needed
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            for (let field in errors) {
                                let input = $(`[name="${field}"]`);
                                input.addClass('is-invalid');
                                input.after(
                                    `<div class="text-danger">${errors[field][0]}</div>`);
                            }
                        } else {
                            alert('An unexpected error occurred. Please try again.');
                        }
                    }
                });

                $('#successModal').on('hidden.bs.modal', function() {
                    location.reload();
                });
            });
        });
    </script>
@endpush
