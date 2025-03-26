<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@if (Auth::user()->position === 'Customer')
    <script>
        window.location.href = "{{ route('dashboard') }}"; // Redirect to a safe page
    </script>
    @php exit;
@endif

@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : 'layouts.drivers-dashboard')

@section('content')
    <div class="container" style="-5rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="tm-col tm-col-big">
                @if (isset($user))
                    <div class="bg-white tm-block">
                        <div class="row">
                            <div class="col-24">
                                <h2 class="tm-block-title">Driver Account Details</h2>
                                Name: {{ $user->first_name }} {{ $user->last_name }}
                                <br>
                                Email: {{ $user->email }}
                                <br>
                                Gender: {{ $user->gender }}
                                <br>
                                Phone: {{ $user->phone_number }}
                                <br>
                                Street: {{ $user->street }}
                                <br>
                                City: {{ $user->city }}
                                <br>
                                State: {{ $user->state }}
                                <br>
                                Age: {{ $user->dob ? \Carbon\Carbon::parse($user->dob)->age : 'N/A' }}<br>
                                <button type="button" class="btn btn-primary mt-3" data-toggle="modal"
                                    data-target="#editDriverModal">Edit
                                    Profile</button>
                                @if (Auth::user()->position === 'Manager' || !$user->photo_id)
                                    <button type="button" class="btn btn-secondary mt-3" data-bs-toggle="modal"
                                        data-bs-target="#uploadNinModal">Upload NIN</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <p>No driver selected.</p>
                @endif
            </div>
            <div class="tm-col tm-col-small">
                <div class="bg-white tm-block">
                    @include('partials.dashboard.profile-image.display')
                </div>
            </div>
        </div>

        <!-- Display the cylinders assigned to the driver -->
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block">
                <h3 class="tm-block-title" style="text-align: center">Cylinders Assigned</h3>
                @if ($deliveries->isNotEmpty())
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cylinder #</th>
                                <th>Size</th>
                                <th>Customer</th>
                                <th>Delivery Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deliveries as $delivery)
                                <tr onclick="window.location='{{ route('cylinders.show', ['cylinder' => $delivery->cylinder]) }}';"
                                    style="cursor: pointer;">
                                    <td>{{ str_pad($delivery->cylinder, 9, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $delivery->size }}</td>
                                    <td>{{ $delivery->customer }}</td>
                                    <td>{{ \Carbon\Carbon::parse($delivery->delivery_date)->format('d-m-Y') ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $deliveries->links('pagination::bootstrap-4') }}
                @else
                    <p>No cylinders assigned to this driver.</p>
                @endif
                <p style="text-align: center">You have been assigned a total of <strong>{{ $totalCylinders }}</strong>
                    cylinder(s).</p>
            </div>
        </div>

        <!-- Edit Driver Modal -->
        <div class="modal fade" id="editDriverModal" tabindex="-1" role="dialog" aria-labelledby="editDriverModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Driver Profile</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="editDriverForm" action="{{ route('drivers.update', $user->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="editFirstName">First Name</label>
                                <input type="text" class="form-control" id="editFirstName" name="firstName"
                                    value="{{ $user->first_name }}" required>
                            </div>
                            <div class="form-group">
                                <label for="editLastName">Last Name</label>
                                <input type="text" class="form-control" id="editLastName" name="lastName"
                                    value="{{ $user->last_name }}" required>
                            </div>
                            <div class="form-group">
                                <label for="editPhoneNumber">Phone Number</label>
                                <input type="tel" class="form-control" id="editPhoneNumber" name="phoneNumber"
                                    maxlength="10" value="{{ $user->phone_number }}" required>
                            </div>
                            <div class="form-group">
                                <label>Gender</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="editMale"
                                        value="male" {{ $user->gender == 'male' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="editMale">Male</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="editFemale"
                                        value="female" {{ $user->gender == 'female' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="editFemale">Female</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="editStreet">Street Address</label>
                                <input type="text" class="form-control" id="editStreet" name="street"
                                    value="{{ $user->street }}" required>
                            </div>
                            <div class="form-group">
                                <label for="editCity">City</label>
                                <input type="text" class="form-control" id="editCity" name="city"
                                    value="{{ $user->city }}" required>
                            </div>
                            <div class="form-group">
                                <label for="editState">State</label>
                                <select class="form-control" id="editState" name="state" required>
                                    <option value="" disabled>Select State</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state->id }}"
                                            {{ $user->state == $state->name ? 'selected' : '' }}>{{ $state->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="editEmail">Email</label>
                                <input type="email" class="form-control" id="editEmail" name="email"
                                    value="{{ $user->email }}" required>
                            </div>
                            <div class="form-group">
                                <label for="editDob">Date of Birth</label>
                                <input type="date" class="form-control" id="editDob" name="dob"
                                    value="{{ \Carbon\Carbon::parse($user->dob)->format('Y-m-d') }}" required>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @include('partials.dashboard.nin-modal')

    @endsection

    @section('scripts')
    <script>
        $(document).ready(function() {
            $("#editDriverForm").on("submit", function(e) {
                e.preventDefault(); // Prevent normal form submission

                let formData = $(this).serialize(); // Serialize form data
                let actionUrl = $(this).attr("action");

                $.ajax({
                    url: actionUrl,
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            // Close the modal
                            $("#editDriverModal").modal("hide");

                            // Update user details on the page dynamically
                            $("#editFirstName").val(response.user.first_name);
                            $("#editLastName").val(response.user.last_name);
                            $("#editPhoneNumber").val(response.user.phone_number);
                            $("#editStreet").val(response.user.street);
                            $("#editCity").val(response.user.city);
                            $("#editState").val(response.user.state);
                            $("#editEmail").val(response.user.email);
                            $("#editDob").val(response.user.dob);

                            // Update displayed profile details
                            $(".tm-block").html(`
                                Name: ${response.user.first_name} ${response.user.last_name}<br>
                                Email: ${response.user.email}<br>
                                Gender: ${response.user.gender}<br>
                                Phone: ${response.user.phone_number}<br>
                                Street: ${response.user.street}<br>
                                City: ${response.user.city}<br>
                                State: ${response.user.state}<br>
                                Age: ${response.user.age}<br>
                            `);

                            alert("Profile updated successfully!");
                        } else {
                            alert("Something went wrong. Please try again.");
                        }
                    },
                    error: function(xhr) {
                        alert("Error updating profile: " + xhr.responseJSON.message);
                    }
                });
            });
        });
    </script>
@endsection

