@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : 'layouts.drivers-dashboard'))

@section('content')
    <div class="container" style="-5rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="tm-col tm-col-big">
                @if (isset($user))
                    <div class="bg-white tm-block">
                        <div class="row">
                            <div class="col-24">
                                <h2 class="tm-block-title">Driver Account Details</h2>
                                Name: {{ $user->first_name }} {{ $user->last_name }}<br>
                                Email: {{ $user->email }}<br>
                                Gender: {{ $user->gender }}<br>
                                Phone: {{ $user->phone_number }}<br>
                                Street: {{ $user->street }}<br>
                                City: {{ $user->city }}<br>
                                State: {{ $user->state }}<br>
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

        {{-- DRIVER VIEW --}}
        @if (Auth::user()->position === 'Driver')
            <div class="row tm-content-row tm-mt-big">
                <div class="bg-white tm-block">
                    <h3 class="tm-block-title text-center">Your Assigned Deliveries</h3>
                    @php
                        $drvDeliveries = \App\Models\Delivery::where('driver_id', $user->id)
                            ->orderBy('delivery_date', 'desc')
                            ->get();
                    @endphp
                    @if ($drvDeliveries->isNotEmpty())
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cylinder #</th>
                                    <th>Size</th>
                                    <th>Customer</th>
                                    <th>Address</th>
                                    <th>Delivery Date</th>
                                    <th>Delivery Time</th>
                                    <th>Passcode</th>
                                    <th>Delivery</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($drvDeliveries as $d)
                                    <tr>
                                        <td>{{ str_pad($d->cylinder, 9, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $d->size }}</td>
                                        <td>{{ $d->customer }}</td>
                                        <td>{{ $d->address }}</td>
                                        <td>{{ \Carbon\Carbon::parse($d->delivery_date)->format('d-m-Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($d->delivery_time)->format('H:i') }}</td>
                                        <td>
                                            {{ $d->date_delivered && $d->time_delivered ? 'Picked up' : $d->passcode }}
                                        </td>
                                        <td>
                                            {{-- Check if the user is a Driver --}}
                                            @if (Auth::user()->position === 'Driver' && $delivery && is_null($delivery->date_delivered))
                                                {{-- Show Start Delivery button only if driver_pickup_date and driver_pickup_time are NOT NULL AND delivery_start IS NULL --}}
                                                @if (
                                                    !is_null($delivery->driver_pickup_date) &&
                                                        !is_null($delivery->driver_pickup_time) &&
                                                        is_null($delivery->delivery_start))
                                                    <form action="{{ route('deliveries.start', $delivery->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary">Start
                                                            Delivery</button>
                                                    </form>

                                                    {{-- Show Close Delivery button only if delivery_start is NOT NULL --}}
                                                @elseif (!is_null($delivery->delivery_start))
                                                    <form action="{{ route('deliveries.close', $delivery->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger">Close
                                                            Delivery</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-center">No deliveries assigned to you yet.</p>
                    @endif
                </div>
            </div>

            {{-- MANAGER/EMPLOYEE/AGENT VIEW --}}
        @elseif(in_array(Auth::user()->position, ['Manager', 'Employee', 'Agent']))
            <div class="row tm-content-row tm-mt-big">
                <div class="bg-white tm-block">
                    <h3 class="tm-block-title text-center">Deliveries Assigned to {{ $user->first_name }}</h3>
                    @php
                        $drvDeliveries = \App\Models\Delivery::where('driver_id', $user->id)
                            ->orderBy('delivery_date', 'desc')
                            ->get();
                    @endphp
                    @if ($drvDeliveries->isNotEmpty())
                        <form action="{{ route('deliveries.confirm', $user->id) }}" method="POST">
                            @csrf
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select_all_deliveries"> Select All</th>
                                        <th>Cylinder #</th>
                                        <th>Size</th>
                                        <th>Customer</th>
                                        <th>Address</th>
                                        <th>Delivery Date</th>
                                        <th>Delivery Time</th>
                                        <th>Status</th>
                                        <th>Approval</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($drvDeliveries as $d)
                                        <tr>
                                            <td>
                                                @if (is_null($d->date_delivered))
                                                    <input type="checkbox" name="selected_deliveries[]"
                                                        value="{{ $d->id }}">
                                                @endif
                                            </td>
                                            <td>{{ str_pad($d->cylinder, 9, '0', STR_PAD_LEFT) }}</td>
                                            <td>{{ $d->size }}</td>
                                            <td>{{ $d->customer }}</td>
                                            <td>{{ $d->address }}</td>
                                            <td>{{ \Carbon\Carbon::parse($d->delivery_date)->format('d-m-Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($d->delivery_time)->format('H:i') }}</td>
                                            <td>
                                                @if (is_null($d->date_delivered))
                                                    @if (!is_null($d->delivery_start))
                                                        Being Delivered
                                                    @elseif(!is_null($d->driver_pickup_date))
                                                        With Driver
                                                    @else
                                                        Still at warehouse
                                                    @endif
                                                @else
                                                    Delivered on
                                                    {{ \Carbon\Carbon::parse($d->date_delivered)->format('d-m-Y') }}
                                                    at {{ \Carbon\Carbon::parse($d->time_delivered)->format('H:i') }}
                                                @endif
                                            </td>
                                            <td>
                                                @if (!is_null($d->date_delivered) && !is_null($d->time_delivered))
                                                    @if ($d->approval === 'approved')
                                                        {{-- Already approved: show “Approved” text and Review button --}}
                                                        <span class="text-success">Approved</span>
                                                        <a href="{{ route('deliveries.review', $d->id) }}"
                                                            class="btn btn-sm btn-secondary">
                                                            Review
                                                        </a>
                                                    @else
                                                        {{-- Not approved yet: show Approve Delivery button --}}
                                                        <form action="{{ route('deliveries.approve', $d->id) }}"
                                                            method="POST" style="display:inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                                Approve Delivery
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            @if ($drvDeliveries->whereNull('date_delivered')->isNotEmpty())
                                <div class="form-group row passcode-form mt-4">
                                    <label for="passcode" class="col-sm-3 col-form-label text-sm-right text-center">
                                        Enter Passcode:
                                    </label>
                                    <div class="col-sm-6 col-md-4 d-flex passcode-controls">
                                        <input type="text" name="passcode" id="passcode"
                                            class="form-control mr-2 passcode-input" required>
                                        <button type="submit" class="btn btn-primary">Confirm</button>
                                    </div>
                                </div>
                            @endif
                        </form>
                    @else
                        <p class="text-center">No deliveries to confirm for this driver.</p>
                    @endif
                </div>
            </div>
        @endif

    </div>

    @include('partials.dashboard.nin-modal')

    <style>
        .passcode-controls .form-control,
        .passcode-controls .btn {
            height: 50px;
            /* or use the height from Bootstrap inputs */
        }
    </style>

@endsection

@section('scripts')
    @if (Auth::guest())
        <script>
            window.location = "{{ url('/') }}";
        </script>
    @endif
    <script>
        $(document).ready(function() {
            $("#editDriverForm").on("submit", function(e) {
                e.preventDefault();
                let formData = $(this).serialize();
                let actionUrl = $(this).attr("action");

                $.ajax({
                    url: actionUrl,
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $("#editDriverModal").modal("hide");
                            $("#editFirstName").val(response.user.first_name);
                            $("#editLastName").val(response.user.last_name);
                            $("#editPhoneNumber").val(response.user.phone_number);
                            $("#editStreet").val(response.user.street);
                            $("#editCity").val(response.user.city);
                            $("#editState").val(response.user.state);
                            $("#editEmail").val(response.user.email);
                            $("#editDob").val(response.user.dob);

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select_all_deliveries');
            if (selectAll) {
                const cbs = document.querySelectorAll('input[name="selected_deliveries[]"]');
                cbs.forEach(cb => cb.addEventListener('click', e => e.stopPropagation()));
                selectAll.addEventListener('change', function() {
                    cbs.forEach(cb => cb.checked = this.checked);
                });
            }
        });
    </script>
@endsection
