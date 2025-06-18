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
                                                    {{-- Show passcode with copy button --}}
                                                    <span class="passcode-text">{{ $d->passcode }}</span>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-secondary copy-passcode-btn"
                                                        data-passcode="{{ $d->passcode }}" title="Copy passcode"
                                                        style="margin-left: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.9rem;">
                                                        <i class="fa fa-copy"></i>
                                                    </button>

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
                    <form id="passcodeForm" action="{{ route('deliveries.confirm', $user->id) }}" method="POST">
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
                                                @php
                                                    // Determine if delivered: date_delivered not null
                                                    $isDelivered = !is_null($d->date_delivered);
                                                @endphp

                                                {{-- If delivered AND image exists AND not yet approved/disapproved → show “View Image” --}}
                                                @if ($isDelivered && !is_null($d->image_path) && is_null($d->approval))
                                                    <button type="button" class="btn btn-info btn-sm view-image-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#imageModal{{ $d->id }}">
                                                        View Image
                                                    </button>

                                                    {{-- If already approved/disapproved → show status --}}
                                                @elseif(!is_null($d->approval))
                                                    {{-- NEW: if disapproved, show Review button next to text --}}
                                                    @if ($d->approval === 'disapproved')
                                                        <span class="text-danger font-weight-bold">Disapproved</span>
                                                        <a href="{{ route('deliveries.review', $d->id) }}"
                                                            class="btn btn-sm btn-warning ms-2">Review</a>
                                                    @elseif ($d->approval === 'approved')
                                                        <span class="text-success font-weight-bold">Approved</span>
                                                    @else
                                                        {{-- fallback for unexpected statuses --}}
                                                        {{ ucfirst($d->approval) }}
                                                    @endif

                                                    {{-- Not delivered yet --}}
                                                @elseif (is_null($d->date_delivered) && is_null($d->time_delivered))
                                                    Not delivered yet

                                                    {{-- Delivered but missing image or other fallback --}}
                                                @else
                                                    &mdash;
                                                @endif

                                                {{-- Driver-specific Start/Close Delivery logic remains unchanged below --}}
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

                    {{-- Success Modal for Passcode --}}
                    <div class="modal fade" id="passcodeSuccessModal" tabindex="-1" role="dialog"
                        aria-labelledby="passcodeSuccessModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="passcodeSuccessModalLabel">Success</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Passcode verified successfully.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-success" data-bs-dismiss="modal"
                                        id="passcodeSuccessOk">OK</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Error Modal for Passcode --}}
                    <div class="modal fade" id="passcodeErrorModal" tabindex="-1" role="dialog"
                        aria-labelledby="passcodeErrorModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="passcodeErrorModalLabel">Error</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="passcodeErrorMessage">
                                    Incorrect passcode. Please try again.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @else
            <p class="text-center">No deliveries to confirm for this driver.</p>
        @endif
    </div>

    {{-- Image Modals for each delivered delivery with image --}}
    @foreach ($drvDeliveries as $d)
        @php
            $isDelivered = !is_null($d->date_delivered);
        @endphp
        @if ($isDelivered && !is_null($d->image_path))
            <div class="modal fade" id="imageModal{{ $d->id }}" tabindex="-1" role="dialog"
                aria-labelledby="imageModalLabel{{ $d->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="imageModalLabel{{ $d->id }}">Delivery Image</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="{{ asset('storage/' . $d->image_path) }}" alt="Delivery Image" class="img-fluid">
                            <div class="mt-3">
                                @if (is_null($d->approval))
                                    {{-- Approve --}}
                                    <form action="{{ route('deliveries.approve', $d->id) }}" method="POST"
                                        style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Approve</button>
                                    </form>
                                    {{-- Disapprove --}}
                                    <button type="button" class="btn btn-warning disapprove-btn-in-modal"
                                        data-id="{{ $d->id }}">
                                        Disapprove
                                    </button>
                                @else
                                    {{-- Already approved/disapproved --}}
                                    {{ ucfirst($d->approval) }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

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

    {{-- Radio button selection controls --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select_all_deliveries');
            if (selectAll) {
                const cbs = document.querySelectorAll('input[name="selected_deliveries[]"]');
                // Clear all checkboxes on load:
                cbs.forEach(cb => cb.checked = false);
                selectAll.checked = false;

                cbs.forEach(cb => cb.addEventListener('click', e => e.stopPropagation()));
                selectAll.addEventListener('change', function() {
                    cbs.forEach(cb => cb.checked = this.checked);
                });
            }
        });
    </script>

    {{-- Driver copy passcode button --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Attach copy logic to each copy-passcode-btn
            document.querySelectorAll('.copy-passcode-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    // Prevent any unintended side effects
                    e.stopPropagation();

                    const code = this.getAttribute('data-passcode') || '';
                    if (!navigator.clipboard) {
                        // Fallback if Clipboard API not available
                        const tempInput = document.createElement('input');
                        tempInput.value = code;
                        document.body.appendChild(tempInput);
                        tempInput.select();
                        try {
                            document.execCommand('copy');
                            alert('Passcode copied to clipboard');
                        } catch (err) {
                            console.error('Fallback: copy command failed', err);
                            alert('Unable to copy');
                        }
                        document.body.removeChild(tempInput);
                    } else {
                        navigator.clipboard.writeText(code).then(() => {
                            alert('Passcode copied to clipboard');
                        }).catch(err => {
                            console.error('Could not copy text: ', err);
                            alert('Unable to copy');
                        });
                    }
                });
            });
        });
    </script>

    {{-- ── AJAX for Passcode confirmation ─────────────────────────────────── --}}
    @if (in_array(Auth::user()->position, ['Manager', 'Employee', 'Agent']))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('passcodeForm');
                if (!form) return;

                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Collect selected deliveries
                    const checked = form.querySelectorAll('input[name="selected_deliveries[]"]:checked');
                    if (checked.length === 0) {
                        // No rows selected
                        // Show error modal with message
                        const errMsgEl = document.getElementById('passcodeErrorMessage');
                        if (errMsgEl) {
                            errMsgEl.textContent = 'Please select at least one delivery.';
                        }
                        const errModal = new bootstrap.Modal(document.getElementById('passcodeErrorModal'));
                        errModal.show();
                        return;
                    }

                    const selectedIds = Array.from(checked).map(cb => cb.value);
                    const passcodeInput = document.getElementById('passcode');
                    const passcode = passcodeInput ? passcodeInput.value.trim() : '';

                    if (!passcode) {
                        // Show error modal
                        const errMsgEl = document.getElementById('passcodeErrorMessage');
                        if (errMsgEl) {
                            errMsgEl.textContent = 'Passcode is required.';
                        }
                        const errModal = new bootstrap.Modal(document.getElementById('passcodeErrorModal'));
                        errModal.show();
                        return;
                    }

                    // Build FormData
                    const formData = new FormData();
                    selectedIds.forEach(id => formData.append('selected_deliveries[]', id));
                    formData.append('passcode', passcode);
                    // Add CSRF token
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    formData.append('_token', token);

                    // Send AJAX POST
                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                            },
                            body: formData,
                        })
                        .then(response => {
                            if (response.ok) {
                                return response.json();
                            } else {
                                // 422 or other error
                                return response.json().then(json => {
                                    throw json;
                                });
                            }
                        })
                        .then(json => {
                            if (json.success) {
                                // Show success modal
                                const successModalEl = document.getElementById('passcodeSuccessModal');
                                const successModal = new bootstrap.Modal(successModalEl);
                                successModal.show();

                                // When OK clicked: reload page
                                const okBtn = document.getElementById('passcodeSuccessOk');
                                if (okBtn) {
                                    okBtn.addEventListener('click', function() {
                                        // After modal hides, reload
                                        location.reload();
                                    }, {
                                        once: true
                                    });
                                }
                            } else {
                                // Show error modal with message from JSON
                                const errMsgEl = document.getElementById('passcodeErrorMessage');
                                if (errMsgEl) {
                                    errMsgEl.textContent = json.message ||
                                        'Incorrect passcode. Please try again.';
                                }
                                const errModal = new bootstrap.Modal(document.getElementById(
                                    'passcodeErrorModal'));
                                errModal.show();
                            }
                        })
                        .catch(errorJson => {
                            // Could be network error or JSON with message
                            let msg = 'An error occurred. Please try again.';
                            if (errorJson && typeof errorJson === 'object' && errorJson.message) {
                                msg = errorJson.message;
                            }
                            const errMsgEl = document.getElementById('passcodeErrorMessage');
                            if (errMsgEl) {
                                errMsgEl.textContent = msg;
                            }
                            const errModal = new bootstrap.Modal(document.getElementById(
                                'passcodeErrorModal'));
                            errModal.show();
                        });
                });
            });
        </script>
    @endif

    {{-- ── ADDED: Manager/Employee Disapprove JS (same as in Deliveries page) --}}
    @if (in_array(Auth::user()->position, ['Manager', 'Employee']))
        <script>
            $(document).ready(function() {
                // When “Disapprove” button is clicked, show the modal and update form action
                $('.disapprove-btn-in-modal').click(function() {
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
