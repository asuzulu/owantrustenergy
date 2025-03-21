@if (Auth::user()->position === 'Customer')
    <script>
        window.location.href = "{{ route('dashboard') }}"; // Redirect to a safe page
    </script>
    @php exit; @endphp
@endif

@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : (Auth::user()->position === 'Driver' ? 'layouts.drivers-dashboard' : 'layouts.default-dashboard')))

@section('content')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">
                <div class="row">
                    <div class="col-md-8 col-sm-12">
                        <h2 class="tm-block-title d-inline-block">Drivers</h2>
                    </div>
                    @if (Auth::user()->position !== 'Driver')
                        <div class="col-md-4 col-sm-12 text-right">
                            <button class="btn btn-small btn-primary" data-toggle="modal" data-target="#addDriverModal">Add
                                New Driver</button>
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
                                <th scope="col">Position</th>
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
                                    <td>{{ $driver->position }}</td>
                                    <td>{{ $driver->city }}</td>
                                    <td>{{ $driver->state }}</td>
                                    <td>{{ $driver->dob ? \Carbon\Carbon::parse($driver->dob)->age : 'N/A' }}</td>
                                </tr>
                            @endforeach
                            @if ($drivers->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center">No drivers found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Pagination Links --}}
            @if ($drivers->hasPages())
                <div style="text-align: center; margin-top: 20px;">
                    {{ $drivers->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Add Driver Modal -->
    <div class="modal fade" id="addDriverModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Driver</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="driverForm" action="{{ route('drivers.store') }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="firstName">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required
                                    placeholder="Enter First Name" value="{{ old('firstName') }}" />
                                @error('firstName')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="lastName">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required
                                    placeholder="Enter Last Name" value="{{ old('lastName') }}" />
                                @error('lastName')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="phoneNumber">Phone Number</label>
                                <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" required
                                    placeholder="Enter Phone Number" value="{{ old('phoneNumber') }}" />
                                @error('phoneNumber')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    placeholder="Enter Email Address" value="{{ old('email') }}" />
                                @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="dob">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob" required
                                    value="{{ old('dob') }}" />
                                @error('dob')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="gender">Gender</label>
                                <div class="form-check form-check-inline" style="margin-top: 2rem;">
                                    <input class="form-check-input" type="radio" name="gender" id="male"
                                        value="male" required {{ old('gender') == 'male' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="male">Male</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="female"
                                        value="female" required {{ old('gender') == 'female' ? 'checked' : '' }}>
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
                                placeholder="Enter Street Address" value="{{ old('street') }}" />
                            @error('street')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="city">City</label>
                                <input type="text" class="form-control" id="city" name="city" required
                                    placeholder="Enter City" value="{{ old('city') }}" />
                                @error('city')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="state">State</label>
                                <select class="form-control" id="state" name="state" required>
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
                                    title="Please enter exactly 11 digits" maxlength="11" value="{{ old('bvn') }}" />
                                @error('bvn')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="nin">NIN (National Identification Number)</label>
                                <input type="text" class="form-control" id="nin" name="nin" required
                                    placeholder="Enter 11-digit NIN" pattern="\d{11}"
                                    title="Please enter exactly 11 digits" maxlength="11" value="{{ old('nin') }}" />
                                @error('nin')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required
                                    placeholder="At least 8 characters" />
                                @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="password_confirmation">Confirm Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required placeholder="Confirm Password" />
                                @error('password_confirmation')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <input type="hidden" name="position" value="Driver" />

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">Add Driver</button>
                        </div>
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
            $("#driverForm").submit(function(event) {
                event.preventDefault();
                $.ajax({
                    url: "{{ route('drivers.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        alert("Driver added successfully!");
                        location.reload();
                    },
                    error: function(xhr) {
                        alert("Error adding driver.");
                    }
                });
            });
        });
    </script>
@endsection
