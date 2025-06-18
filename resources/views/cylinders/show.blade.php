@extends(
    match (Auth::user()->position) {
        'Manager' => 'layouts.management-dashboard',
        'Employee' => 'layouts.employee-dashboard',
        'Agent' => 'layouts.agent-dashboard',
        default => 'layouts.drivers-dashboard',
    }
)

@section('content')
    @php
        // Compute the 9-digit, zero-padded cylinder ID:
        $paddedId = str_pad($cylinder->id, 9, '0', STR_PAD_LEFT);

        // Check if this padded ID is in the agent_cylinders_distribution table:
        $inAgentDistribution = \Illuminate\Support\Facades\DB::table('agent_cylinders_distribution')
            ->where('cylinder_id', $paddedId)
            ->exists();
    @endphp

    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">

                {{-- Header --}}
                <div class="row">
                    <div class="col-md-8 col-sm-12">
                        <h2 class="tm-block-title d-inline-block">Cylinder Details</h2>
                    </div>
                </div>

                {{-- Details Table --}}
                <div class="table-responsive mt-3">
                    <table class="table table-hover table-striped tm-table-striped-even">
                        <thead>
                            <tr class="tm-bg-gray">
                                <th>Field</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Cylinder #</strong></td>
                                <td>{{ $paddedId }}</td>
                            </tr>
                            <tr>
                                <td><strong>Size</strong></td>
                                <td>{{ $cylinder->size }}</td>
                            </tr>
                            <tr>
                                <td><strong>Weight</strong></td>
                                <td>
                                    @switch($cylinder->size)
                                        @case('Small')
                                            3 kg
                                        @break

                                        @case('Medium')
                                            5 kg
                                        @break

                                        @case('Large')
                                            12 kg
                                        @break

                                        @case('Extra Large')
                                            25 kg
                                        @break

                                        @default
                                            N/A
                                    @endswitch
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Location</strong></td>
                                <td>
                                    @if ($cylinder->user)
                                        @if (in_array($cylinder->user->position, ['Manager', 'Employee', 'Agent']))
                                            {{ $cylinder->location }}
                                        @else
                                            {{ $cylinder->user->street }},
                                            {{ $cylinder->user->city }},
                                            {{ $cylinder->user->state }}
                                        @endif
                                    @else
                                        {{ $cylinder->location }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Date Allocated</strong></td>
                                <td>
                                    {{ $cylinder->allocated_date ? $cylinder->allocated_date->format('d-m-Y') : 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                {{-- Changed label based on assigned user position --}}
                                @php
                                    $label = 'Assigned to Customer';
                                    if (!$cylinder->user) {
                                        $label = 'Allocated By';
                                    } elseif ($cylinder->user->position === 'Agent') {
                                        $label = 'In Possession of Agent';
                                    } elseif ($cylinder->user->position !== 'Customer') {
                                        $label = 'Allocated By';
                                    }
                                @endphp
                                <td><strong>{{ $label }}</strong></td>
                                <td>
                                    @if ($cylinder->user)
                                        {{ $cylinder->user->first_name . ' ' . $cylinder->user->last_name }}
                                    @else
                                        {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                                    @endif
                                </td>
                            </tr>

                            {{-- Driver row, if there is a delivery record at all --}}
                            @if ($delivery)
                                <tr>
                                    <td><strong>Driver</strong></td>
                                    <td class="click" style="cursor: pointer;">
                                        {{-- Wrap driver name in a link to their profile --}}
                                        <a href="{{ route('drivers.profile', $delivery->driver_id) }}"
                                            style="text-decoration: none; color: inherit;">
                                            {{ $delivery->driver }}
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            {{-- Pickup row, if there is a pickup record --}}
                            @if ($pickup)
                                <tr>
                                    <td><strong>Pick Up Location</strong></td>
                                    <td>{{ $pickup->location }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-start mt-3">
                    <a href="{{ Auth::user()->position === 'Driver' ? route('drivers.cylinders') : route('management.cylinders') }}"
                        class="btn btn-secondary mr-2">
                        Back to Cylinders List
                    </a>

                    @if (in_array(Auth::user()->position, ['Manager', 'Employee', 'Agent']))
                        @if ($cylinder->user && $cylinder->user->position === 'Customer')
                            {{-- Return to Warehouse button always shows when it’s currently with a Customer --}}
                            <button type="button" class="btn btn-warning mr-2" data-toggle="modal"
                                data-target="#returnWarehouseModal">
                                Return to Warehouse
                            </button>
                        @else
                            {{--
        Assign to User button:
        • Show for Managers and Agents unconditionally.
        • For Employees, only show if cylinder is NOT in agent_cylinders_distribution.
    --}}
                            @php
                                // Current user is Employee AND cylinder is in agent_cylinders_distribution?
                                $hideForEmployee = Auth::user()->position === 'Employee' && $inAgentDistribution;
                            @endphp

                            @if (!$hideForEmployee)
                                <button type="button" class="btn btn-primary mr-2" data-bs-toggle="modal"
                                    data-bs-target="#assignCylinderModal">
                                    Assign to User
                                </button>
                            @endif
                        @endif
                    @endif

                    @if (Auth::user()->position === 'Manager')
                        <button type="button" class="btn btn-danger mr-2" data-toggle="modal"
                            data-target="#deleteCylinderModal">
                            Delete Cylinder
                        </button>
                    @endif

                    {{-- Deliver button only for drivers on undelivered cylinders --}}
                    @if (Auth::user()->position === 'Driver' && $delivery)
                        {{-- 1) If the delivery is already closed, hide all buttons. --}}
                        @if (!is_null($delivery->date_delivered) && !is_null($delivery->time_delivered))
                            {{-- Delivery is fully completed; nothing to show here. --}}

                            {{-- 2) Driver has not yet “picked up” from warehouse → no buttons yet. --}}
                        @elseif(is_null($delivery->driver_pickup_date) || is_null($delivery->driver_pickup_time))
                            {{-- Warehouse hasn’t set driver_pickup_date/time yet → no “Start” or “Close” buttons. --}}

                            {{-- 3) Driver has picked up but has not clicked “Start Delivery” yet → show “Start Delivery”. --}}
                        @elseif(is_null($delivery->delivery_start))
                            <form action="{{ route('deliveries.start', $delivery->id) }}" method="POST"
                                style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary">
                                    Start Delivery
                                </button>
                            </form>

                            {{-- 4) Driver has clicked “Start Delivery” (so delivery_start is NOT null) but delivery not closed → show “Close Delivery”. --}}
                        @elseif(!is_null($delivery->delivery_start) && (is_null($delivery->date_delivered) || is_null($delivery->time_delivered)))
                            <a href="{{ route('drivers.delivering', $delivery->cylinder) }}"
                                class="btn btn-sm btn-warning">
                                Close Delivery
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Cylinder Modal -->
    <div class="modal fade" id="assignCylinderModal" tabindex="-1" role="dialog"
        aria-labelledby="assignCylinderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignCylinderModalLabel">Assign Cylinder to Customer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group search-container">
                        <label for="customerSearch">Search Customer:</label>
                        <input type="text" id="customerSearch" class="form-control" placeholder="Type customer name...">
                        <div id="customerDropdown" class="dropdown-menu w-100"></div>
                    </div>
                    <input type="hidden" id="selectedUserId">
                    <div class="form-group">
                        <label>Assignment Type:</label>
                        <div>
                            <input type="radio" id="deliveryOption" name="assignmentType" value="delivery">
                            <label for="deliveryOption">Delivery</label>
                            <input type="radio" id="pickupOption" name="assignmentType" value="pickup">
                            <label for="pickupOption">Pick-Up</label>
                        </div>
                    </div>
                    <div id="deliveryFields" style="display: none;">
                        <div class="form-group search-container">
                            <label for="driverSearch">Assign Driver:</label>
                            <input type="text" id="driverSearch" class="form-control" placeholder="Type driver name...">
                            <div id="driverDropdown" class="dropdown-menu w-100"></div>
                        </div>
                        <input type="hidden" id="selectedDriverId">
                        <div class="form-group">
                            <label for="deliveryDate">Delivery Date:</label>
                            <input type="date" id="deliveryDate" class="form-control" min="{{ $today }}">
                        </div>
                        <div class="form-group">
                            <label for="deliveryTime">Delivery Time:</label>
                            <input type="time" id="deliveryTime" class="form-control">
                        </div>
                    </div>
                    <div id="pickupFields" style="display: none;">
                        <div class="form-group">
                            <label for="pickupLocation">Pick Up Location:</label>
                            <select id="pickupLocation" class="form-control" style="height: 4rem;">
                                @foreach ($warehouses as $warehouse)
                                    @if ($warehouse->name !== 'Unassigned')
                                        <!-- Exclude 'Unassigned' warehouse -->
                                        <option value="{{ $warehouse->name }}"
                                            {{ $cylinder->location === $warehouse->name ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="pickupDate">Pick-Up Date:</label>
                            <input type="date" id="pickupDate" class="form-control" min="{{ $today }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="assignCylinderBtn" class="btn btn-primary" disabled>
                        Assign Cylinder
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="assignSuccessModal" tabindex="-1" role="dialog"
        aria-labelledby="assignSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="assignSuccessModalLabel">Success</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Cylinder has been successfully assigned.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="assignErrorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Assignment Failed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Sorry, something went wrong while assigning this cylinder. Please try again.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Return to Warehouse Modal -->
    <div class="modal fade" id="returnWarehouseModal" tabindex="-1" role="dialog"
        aria-labelledby="returnWarehouseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="returnWarehouseModalLabel" class="modal-title">Return Cylinder to Warehouse</h5>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><strong>Select Warehouse:</strong></label>
                        <select id="warehouseSelect" class="form-control" style="height:4rem;">
                            @foreach ($warehouses as $wh)
                                @if ($wh->name !== 'Unassigned')
                                    <option value="{{ $wh->name }}">{{ $wh->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button id="triggerReturnWarning" class="btn btn-primary">Confirm Return</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Warning Modal -->
    <div class="modal fade" id="returnWarningModal" tabindex="-1" role="dialog"
        aria-labelledby="returnWarningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 id="returnWarningModalLabel" class="modal-title">Confirm Action</h5>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>For making this change you will be logged for this action.<br>
                        Are you sure you want to continue?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-ismiss="modal">Cancel</button>
                    <button id="submitReturnForm" class="btn btn-warning">Yes, Proceed</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rwturn Success Modal -->
    <div class="modal fade" id="returnSuccessModal" tabindex="-1" role="dialog"
        aria-labelledby="returnSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 id="returnSuccessModalLabel" class="modal-title">Success</h5>
                    <button class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Cylinder was returned to the warehouse successfully.</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <form id="returnForm" method="POST" action="{{ route('cylinders.return', ['id' => $paddedId]) }}"
        style="display:none;">
        @csrf
        <input type="hidden" name="warehouse" id="returnWarehouseInput">
    </form>

    <!-- Delete Cylinder Modal -->
    <div class="modal fade" id="deleteCylinderModal" tabindex="-1" role="dialog"
        aria-labelledby="deleteCylinderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this cylinder?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>
                    <form action="{{ route('cylinders.destroy', $paddedId) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            Yes, Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Start Delivery Success Modal (NEW) -->
    <div class="modal fade" id="startDeliverySuccessModal" tabindex="-1" role="dialog"
        aria-labelledby="startDeliverySuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 id="startDeliverySuccessModalLabel" class="modal-title">Success</h5>
                    <button class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>You have started delivery successfully.</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .search-container {
            position: relative;
        }

        .tm-table-striped-even th,
        .tm-table-striped-even td {
            padding: 15px;
        }

        .tm-table-striped-even th:first-child,
        .tm-table-striped-even td:first-child {
            width: 25%;
        }

        .tm-table-striped-even th:last-child,
        .tm-table-striped-even td:last-child {
            width: 50%;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(function() {
            // ── CSRF SETUP ───────────────────────────────────────
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Modal instance
            const assignModalEl = document.getElementById('assignCylinderModal');
            const assignModal = new bootstrap.Modal(assignModalEl);

            // extract once
            let orderCustName;
            let orderType;
            const params = new URLSearchParams(window.location.search);
            if (params.get('openAssign') === '1') {
                // ← store for deletion
                orderCustName = decodeURIComponent(params.get('cust') || '');
                orderType = params.get('type') || 'delivery';

                // prefill search
                if (orderCustName) {
                    $('#customerSearch').val(orderCustName).trigger('input');
                    $.get("{{ route('search.customers') }}", {
                            query: orderCustName
                        })
                        .done(resp => {
                            if (resp.length === 1) {
                                $('#customerSearch').val(resp[0].first_name + ' ' + resp[0].last_name);
                                $('#selectedUserId').val(resp[0].id);
                                enableAssignButton();
                            }
                        });
                }

                setTimeout(() => {
                    // set radio
                    $('#' + (orderType === 'delivery' ? 'deliveryOption' : 'pickupOption'))
                        .prop('checked', true).trigger('change');
                    assignModal.show();
                    enableAssignButton();
                    history.replaceState({}, document.title, window.location.pathname);
                }, 300);
            }

            // ── ASSIGNMENT LOGIC ─────────────────────────────────
            function enableAssignButton() {
                let isCustomerSelected = $('#selectedUserId').val()?.length > 0;
                let isAssignmentTypeSelected = $('input[name="assignmentType"]:checked').length > 0;
                let isValid = isCustomerSelected && isAssignmentTypeSelected;
                if ($('input[name="assignmentType"]:checked').val() === 'delivery') {
                    let isDriverSelected = $('#selectedDriverId').val()?.length > 0;
                    isValid = isValid && isDriverSelected;
                }
                $('#assignCylinderBtn').prop('disabled', !isValid);
            }

            function setupSearch(inputSelector, dropdownSelector, route, hiddenInputSelector) {
                $(inputSelector).on('input', function() {
                    let query = $(this).val().trim();
                    let dropdown = $(dropdownSelector);
                    if (!query) {
                        dropdown.hide();
                        $('#' + hiddenInputSelector.replace('#', '')).val(''); // clear hidden if any
                        enableAssignButton();
                        return;
                    }
                    $.ajax({
                        url: route,
                        type: "GET",
                        data: {
                            query
                        },
                        success: function(response) {
                            dropdown.empty().show();
                            if (!response.length) {
                                dropdown.append(
                                    '<div class="dropdown-item text-muted">No matches found</div>'
                                );
                                $(hiddenInputSelector).val(''); // clear hidden
                                enableAssignButton();
                            } else {
                                response.forEach(user => {
                                    let item = $('<div class="dropdown-item"></div>')
                                        .text(user.first_name + ' ' + user.last_name)
                                        .attr('data-id', user.id)
                                        .on('click', function() {
                                            $(inputSelector).val($(this).text());
                                            $(hiddenInputSelector).val($(this).data(
                                                'id'));
                                            dropdown.hide();
                                            enableAssignButton();
                                        });
                                    dropdown.append(item);
                                });
                            }
                        }
                    });
                });
            }

            setupSearch('#customerSearch', '#customerDropdown', "{{ route('search.customers') }}",
                '#selectedUserId');
            setupSearch('#driverSearch', '#driverDropdown', "{{ route('search.drivers') }}", '#selectedDriverId');

            $('input[name="assignmentType"]').on('change', function() {
                if ($('#deliveryOption').is(':checked')) {
                    $('#deliveryFields').show();
                    $('#pickupFields').hide();
                } else {
                    $('#pickupFields').show();
                    $('#deliveryFields').hide();
                }
                enableAssignButton();
            });

            $('#assignCylinderBtn').off('click').on('click', function() {
                const userId = $('#selectedUserId').val().trim();
                const cylinderId = "{{ $paddedId }}";
                const assignmentType = $('input[name="assignmentType"]:checked').val();
                const driverId = $('#selectedDriverId').val();
                const deliveryDate = $('#deliveryDate').val();
                const deliveryTime = $('#deliveryTime').val();
                const pickupLocation = $('#pickupLocation').val();
                const pickupDate = $('#pickupDate').val();

                if (!userId) {
                    return alert("Please select a customer.");
                }

                // First: assign the cylinder record
                $.post("{{ route('cylinders.assign') }}", {
                        _token: '{{ csrf_token() }}',
                        user_id: userId,
                        cylinder_id: cylinderId,
                        assignment_type: assignmentType
                    })
                    .done(function() {
                        assignModal.hide();

                        // Now build the payload for whichever secondary record
                        let postData = {
                            _token: '{{ csrf_token() }}',
                            cylinder_id: cylinderId,
                            customer_id: userId
                        };

                        if (assignmentType === 'delivery') {
                            // **Ensure these three fields are present**:
                            postData.driver_id = driverId;
                            postData.delivery_date = deliveryDate;
                            postData.delivery_time = deliveryTime;

                            return $.post("{{ route('deliveries.store') }}", postData)
                                .done(deleteMatchingOrder)
                                .fail(xhr => alert('Delivery record failed: ' + xhr.responseText));

                        } else {
                            // pickup
                            postData.pickup_location = pickupLocation;
                            postData.pick_up_date = pickupDate;

                            return $.post("{{ route('pickups.store') }}", postData)
                                .done(deleteMatchingOrder)
                                .fail(xhr => alert('Pickup record failed: ' + xhr.responseText));
                        }
                    })
                    .fail(xhr => alert('Cylinder assignment failed: ' + xhr.responseText));
            });

            // helper: delete by matching first_name + last_name AND retrieval type
            function deleteMatchingOrder() {
                $.ajax({
                    url: "{{ route('orders.destroyMatching') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cust_fullname: orderCustName,
                        retrieval: orderType
                    }
                }).always(function() {
                    $('#assignSuccessModal').modal('show');
                });
            }

            // ── SUCCESS MODAL “OK” CLICK ─────────────────────────
            $('#assignSuccessModal').on('click', '.btn-success', function(e) {
                e.preventDefault();
                $('#assignSuccessModal').modal('hide');
                location.reload();
            });

            // ── RETURN TO WAREHOUSE FLOW ─────────────────────────
            $('#triggerReturnWarning').click(function(e) {
                e.preventDefault();
                let wh = $('#warehouseSelect').val();
                $('#returnWarehouseInput').val(wh);
                $('#returnWarehouseModal').modal('hide');
                $('#returnWarningModal').modal('show');
            });

            // ── ONLY “Cancel” BUTTONS ────────────────────────────
            $('#returnWarehouseModal, #returnWarningModal').on('click', '.btn-secondary', function(e) {
                e.preventDefault();
                $(this).closest('.modal').modal('hide');
            });

            // ── CONFIRM RETURN ───────────────────────────────────
            $('#submitReturnForm').click(function(e) {
                e.preventDefault();
                let url = $('#returnForm').attr('action');
                let data = $('#returnForm').serialize();

                $('#returnWarningModal').modal('hide');
                $.post(url, data)
                    .done(function() {
                        $('#returnSuccessModal').modal('show');
                    })
                    .fail(function(xhr) {
                        console.error('Return failed response:', xhr);
                        alert('Return failed: ' + (xhr.responseJSON?.message || xhr.statusText));
                    });
            });

            // ── SHOW RETURN SUCCESS IF SESSION FLAG ─────────────
            @if (session('success'))
                $('#returnSuccessModal').modal('show');
            @endif

            $('#returnSuccessModal').on('click', '.btn-success', function(e) {
                e.preventDefault();
                $('#returnSuccessModal').modal('hide');
                setTimeout(() => location.reload(), 200);
            });

            // ── SHOW START DELIVERY SUCCESS IF session('delivery_started') ─────────────
            @if (session('delivery_started'))
                $('#startDeliverySuccessModal').modal('show');
            @endif

            $('#startDeliverySuccessModal').on('click', '.btn-success', function(e) {
                e.preventDefault();
                $('#startDeliverySuccessModal').modal('hide');
                setTimeout(() => location.reload(), 200);
            });
        });
    </script>
@endpush
