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
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary mt-3">Edit Profile</a>
                                @if (Auth::user()->position === 'Manager' || !$user->photo_id)
                                    <button class="btn btn-secondary mt-3" data-bs-toggle="modal"
                                        data-bs-target="#uploadNinModal">
                                        Upload NIN
                                    </button>
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
                <h3 class="tm-block-title" style="text-align: center">Deliveries</h3>
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
                                @php
                                    $paddedId = str_pad($delivery->cylinder, 9, '0', STR_PAD_LEFT);
                                @endphp
                                <tr style="cursor: pointer;"
                                    onclick="window.location='{{ route('cylinders.show', ['cylinder' => $paddedId]) }}';">
                                    <td>{{ $paddedId }}</td>
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

        @include('partials.dashboard.nin-modal')

    @endsection

    @section('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

        @if (Auth::guest())
            <script>
                window.location = "{{ url('/') }}";
            </script>
        @endif
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
                                $(".tm-block").html(`\
                                Name: ${response.user.first_name} ${response.user.last_name}<br>\
                                Email: ${response.user.email}<br>\
                                Gender: ${response.user.gender}<br>\
                                Phone: ${response.user.phone_number}<br>\
                                Street: ${response.user.street}<br>\
                                City: ${response.user.city}<br>\
                                State: ${response.user.state}<br>\
                                Age: ${response.user.age}<br>\
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
