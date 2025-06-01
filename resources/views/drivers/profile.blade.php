@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : 'layouts.drivers-dashboard'))

@section('content')
    <div class="container my-5">
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
                        {{-- Wrap the table in .table-responsive --}}
                        <div class="table-responsive">
                            <table class="table table-hover table-striped tm-table-striped-even mt-3">

                                @php
                                    // Check if at least one assigned delivery has driver_pickup_date AND driver_pickup_time not null
                                    $anyPickedUp = $drvDeliveries
                                        ->whereNotNull('driver_pickup_date')
                                        ->whereNotNull('driver_pickup_time')
                                        ->isNotEmpty();
                                @endphp

                                <thead>
                                    <tr>
                                        <th>Cylinder #</th>
                                        <th>Size</th>
                                        <th>Customer</th>
                                        <th>Address</th>
                                        <th>Delivery Date</th>
                                        <th>Delivery Time</th>
                                        {{-- Swap the heading based on whether any row has driver_pickup_date & driver_pickup_time --}}
                                        @if ($anyPickedUp)
                                            <th>Tracking</th>
                                        @else
                                            <th>Passcode</th>
                                        @endif
                                        <th>Status</th>
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
                                                {{-- 1) If the driver hasn’t picked up yet, still show the passcode --}}
                                                @if (is_null($d->driver_pickup_date) || is_null($d->driver_pickup_time))
                                                    {{ $d->passcode }}

                                                    {{-- 2) Driver has picked up but has not yet “started” the delivery --}}
                                                @elseif (is_null($d->delivery_start))
                                                    With driver

                                                    {{-- 3) Delivery has started, but not yet marked “delivered” --}}
                                                @elseif (is_null($d->date_delivered) || is_null($d->time_delivered))
                                                    Being delivered to customer

                                                    {{-- 4) Delivery is marked delivered, an image was uploaded, but approval is still pending --}}
                                                @elseif (!is_null($d->date_delivered) && !is_null($d->time_delivered) && !is_null($d->image_path) && is_null($d->approval))
                                                    Delivery pending approval

                                                    {{-- 5) Delivery was already approved/disapproved --}}
                                                @elseif (!is_null($d->approval))
                                                    {{ ucfirst($d->approval) }}

                                                    {{-- 6) Fallback: marked delivered but missing image/approval --}}
                                                @else
                                                    Delivered
                                                @endif
                                            </td>
                                            <td>
                                                {{-- Check if the user is a Driver --}}
                                                @if (Auth::user()->position === 'Driver' && $d)
                                                    {{-- Case 1: Delivery date is still null → driver hasn’t marked delivered yet, so show Start/Close buttons --}}
                                                    @if (is_null($d->date_delivered))
                                                        {{-- Show "Start Delivery" only if pickup has happened but delivery hasn’t started yet --}}
                                                        @if (!is_null($d->driver_pickup_date) && !is_null($d->driver_pickup_time) && is_null($d->delivery_start))
                                                            <form action="{{ route('deliveries.start', $d->id) }}"
                                                                method="POST" style="display:inline-block">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-primary">
                                                                    Start Delivery
                                                                </button>
                                                            </form>

                                                            {{-- Show "Close Delivery" only if delivery has started but not yet marked delivered --}}
                                                        @elseif (!is_null($d->delivery_start) && (is_null($d->date_delivered) || is_null($d->time_delivered)))
                                                            <a href="{{ route('drivers.delivering', $d->cylinder) }}"
                                                                class="btn btn-sm btn-warning">
                                                                Close Delivery
                                                            </a>
                                                        @endif

                                                        {{-- Case 2: The driver has already marked this delivered (i.e. date_delivered/time_delivered are set) AND there is an image_path → show “Delivered” --}}
                                                    @elseif (!is_null($d->image_path))
                                                        Delivered
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
                    <form action="{{ route('deliveries.confirm', $user->id) }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover table-striped tm-table-striped-even mt-3">
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
                                                @if (is_null($d->date_delivered) && (is_null($d->driver_pickup_date) || is_null($d->driver_pickup_time)))
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
                                                        Being delivered to customer
                                                    @elseif(!is_null($d->driver_pickup_date))
                                                        With driver
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
                                                {{-- ── ADDED: Show Approve/Disapprove buttons if image_path is set AND approval is null --}}
                                                @if (!is_null($d->image_path) && is_null($d->approval))
                                                    <form action="{{ route('deliveries.approve', $d->id) }}" method="POST"
                                                        style="display:inline-block">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm"
                                                            formaction="{{ route('deliveries.approve', $d->id) }}">
                                                            Approve
                                                        </button>
                                                    </form>

                                                    <button type="button" class="btn btn-warning btn-sm disapprove-btn"
                                                        data-id="{{ $d->id }}">
                                                        Disapprove
                                                    </button>
                                                    {{-- ── If already approved/disapproved, show status --}}
                                                @elseif(!is_null($d->approval))
                                                    {{ ucfirst($d->approval) }}
                                                    {{-- If not delivered yet (both date_delivered & time_delivered null), show “Not delivered yet” --}}
                                                @elseif (is_null($d->date_delivered) && is_null($d->time_delivered))
                                                    Not delivered yet

                                                    {{-- ── OTHERWISE if image is missing but no approval and already delivered), show a dash --}}
                                                @else
                                                    &mdash; {{-- no action until delivered or image missing --}}
                                                @endif

                                                {{-- Driver-specific “Start/Close Delivery” logic --}}
                                                @if (Auth::user()->position === 'Driver' && is_null($d->date_delivered))
                                                    {{-- Show Start Delivery button only if driver_pickup_date and driver_pickup_time are NOT NULL AND delivery_start IS NULL --}}
                                                    @if (!is_null($d->driver_pickup_date) && !is_null($d->driver_pickup_time) && is_null($d->delivery_start))
                                                        <form action="{{ route('deliveries.start', $d->id) }}"
                                                            method="POST" style="margin-top: 5px;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-primary">Start
                                                                Delivery</button>
                                                        </form>

                                                        {{-- Show Close Delivery button only if delivery_start is NOT NULL --}}
                                                    @elseif (!is_null($d->delivery_start))
                                                        <form action="{{ route('drivers.delivering', $d->id) }}"
                                                            method="POST" style="margin-top: 5px;">
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
                        </div>

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
                </div>
            </div>
        @else
            <p class="text-center">No deliveries to confirm for this driver.</p>
        @endif
    </div>

    <!-- Disapprove Delivery Modal (copied from “Deliveries” page) -->
    <div class="modal fade" id="disapproveModal" tabindex="-1" role="dialog" aria-labelledby="disapproveModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="disapproveForm" method="POST" action="{{ route('deliveries.disapprove', 0) }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="disapproveModalLabel">Disapprove Delivery</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="disapproveReason">Reason for Disapproval</label>
                            <textarea class="form-control" id="disapproveReason" name="reason" rows="4"
                                placeholder="Please explain why you are disapproving this delivery..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="submitDisapproveBtn" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('partials.dashboard.nin-modal')

    <style>
        .passcode-controls .form-control,
        .passcode-controls .btn {
            height: 50px;
            /* or use the height from Bootstrap inputs */
        }

        @media (max-width: 576px) {

            .btn,
            .btn-sm {
                padding: 0.5rem 1rem !important;
                font-size: 1rem !important;
                line-height: 1.5 !important;
            }
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

    {{-- ── ADDED: Manager/Employee Disapprove JS (same as in Deliveries page) --}}
    @if (in_array(Auth::user()->position, ['Manager', 'Employee']))
        <script>
            $(document).ready(function() {
                // When “Disapprove” button is clicked, show the modal and update form action
                $('.disapprove-btn').click(function() {
                    let deliveryId = $(this).data('id');
                    let form = $('#disapproveForm');
                    let baseAction = form.attr('action'); // e.g., "/deliveries/0/disapprove"
                    // Replace the trailing “/0” with “/{deliveryId}”
                    let newAction = baseAction.replace(/\/0(?!.*\/0)/, '/' + deliveryId);
                    form.attr('action', newAction);

                    // Clear any previous reason
                    $('#disapproveReason').val('');

                    // Show the modal
                    $('#disapproveModal').modal('show');
                });

                // Validate that the Disapproval reason is at least 10 words
                $('#disapproveForm').on('submit', function(e) {
                    let text = $('#disapproveReason').val().trim();
                    let wordCount = text.split(/\s+/).filter(function(w) {
                        return w.length > 0;
                    }).length;

                    if (wordCount < 10) {
                        e.preventDefault();
                        alert('Response is too short. Please provide at least 10 words.');
                        return false;
                    }
                    // Otherwise, allow the form to submit
                    return true;
                });
            });
        </script>
    @endif
@endsection
