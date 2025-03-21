@extends(
    match (Auth::user()->position) {
        'Manager' => 'layouts.management-dashboard',
        'Employee' => 'layouts.employee-dashboard',
        'Agent' => 'layouts.agent-dashboard',
        default => 'layouts.drivers-dashboard',
    }
)

@section('content')
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">
                <div class="row">
                    <div class="col-md-8 col-sm-12">
                        <h2 class="tm-block-title d-inline-block">Cylinder Details</h2>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped tm-table-striped-even mt-3">
                        <thead>
                            <tr class="tm-bg-gray">
                                <th scope="col">Field</th>
                                <th scope="col">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Cylinder #</strong></td>
                                <td>{{ str_pad($cylinder->id, 9, '0', STR_PAD_LEFT) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Size</strong></td>
                                <td>{{ $cylinder->size }}</td>
                            </tr>
                            <tr>
                                <td><strong>Weight</strong></td>
                                <td>
                                    @if ($cylinder->size == 'Small')
                                        3 kg
                                    @elseif($cylinder->size == 'Medium')
                                        5 kg
                                    @elseif($cylinder->size == 'Large')
                                        12 kg
                                    @elseif($cylinder->size == 'Extra Large')
                                        25 kg
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Location</strong></td>
                                <td>
                                    @if ($cylinder->user)
                                        @if (in_array($cylinder->user->position, ['Manager', 'Employee', 'Agent']))
                                            {{ $cylinder->location }}
                                        @else
                                            {{ $cylinder->user->street }}, {{ $cylinder->user->city }},
                                            {{ $cylinder->user->state }}
                                        @endif
                                    @else
                                        {{ $cylinder->location }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Date Allocated</strong></td>
                                <td>{{ $cylinder->allocated_date ? $cylinder->allocated_date->format('d-m-Y') : 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Assigned User</strong></td>
                                <td>{{ $cylinder->user ? $cylinder->user->first_name . ' ' . $cylinder->user->last_name : 'Not Assigned' }}
                                </td>
                            </tr>
                            <tr>
                                @php
                                    $delivery = $deliveries->firstWhere('cylinder', $cylinder->id);
                                @endphp

                                @if ($delivery)
                            <tr>
                                <td><strong>Driver</strong></td>
                                <td>{{ $delivery->driver }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-start mt-3">
                    <a href="{{ Auth::user()->position === 'Driver' ? route('drivers.cylinders') : route('management.cylinders') }}"
                        class="btn btn-secondary mr-2">Back to Cylinders List</a>
                    @if (in_array(Auth::user()->position, ['Manager', 'Employee', 'Agent']))
                        <button type="button" class="btn btn-primary mr-2" data-toggle="modal"
                            data-target="#assignCylinderModal">Assign to User</button>
                    @endif
                    @if (Auth::user()->position === 'Manager')
                        <button type="button" class="btn btn-danger" data-toggle="modal"
                            data-target="#deleteCylinderModal">Delete Cylinder</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Cylinder Modal -->
    <div class="modal fade" id="assignCylinderModal" tabindex="-1" role="dialog"aria-labelledby="assignCylinderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignCylinderModalLabel">Assign Cylinder to Customer</h5> <button
                        type="button" class="close" data-dismiss="modal" aria-label="Close"> <span
                            aria-hidden="true">&times;</span> </button>
                </div>
                <div class="modal-body">
                    <div class="form-group search-container"> <label for="customerSearch">Search Customer:</label> <input
                            type="text" id="customerSearch" class="form-control" placeholder="Type customer name...">
                        <div id="customerDropdown" class="dropdown-menu w-100"></div>
                    </div> <input type="hidden" id="selectedUserId">
                    <div class="form-group"> <label>Assignment Type:</label>
                        <div> <input type="radio" id="deliveryOption" name="assignmentType" value="delivery"> <label
                                for="deliveryOption">Delivery</label> <input type="radio" id="pickupOption"
                                name="assignmentType" value="pickup"> <label for="pickupOption">Pick-Up</label> </div>
                    </div>
                    <div id="deliveryFields" style="display: none;">
                        <div class="form-group search-container"> <label for="driverSearch">Assign Driver:</label> <input
                                type="text" id="driverSearch" class="form-control" placeholder="Type driver name...">
                            <div id="driverDropdown" class="dropdown-menu w-100"></div>
                        </div> <input type="hidden" id="selectedDriverId">
                        <div class="form-group"> <label for="deliveryDate">Delivery Date:</label> <input type="date"
                                id="deliveryDate" class="form-control"> </div>
                        <div class="form-group"> <label for="deliveryTime">Delivery Time:</label> <input type="time"
                                id="deliveryTime" class="form-control"> </div>
                    </div>
                    <div id="pickupFields" style="display: none;">
                        <div class="form-group"> <label for="pickupLocation">Pick Up Location:</label> <select
                                id="pickupLocation" class="form-control" style="height: 4rem;">
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->name }}">{{ $warehouse->name }}</option>
                                    @endforeach
                            </select> </div>
                        <div class="form-group"> <label for="pickupDate">Pick-Up Date:</label> <input type="date"
                                id="pickupDate" class="form-control"> </div>
                    </div>
                </div>
                <div class="modal-footer"> <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">Close</button> <button type="button" id="assignCylinderBtn"
                        class="btn btn-primary" disabled>Assign Cylinder</button> </div>
            </div>
        </div>
    </div> <!-- Delete Cylinder Modal -->
    <div class="modal fade" id="deleteCylinderModal" tabindex="-1"
        role="dialog"aria-labelledby="deleteCylinderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCylinderModalLabel">Confirm Delete</h5> <button type="button"
                        class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body"> Are you sure you want to delete this cylinder? </div>
                <div class="modal-footer"> <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">Cancel</button>
                    <form action="{{ route('cylinders.destroy', $cylinder->id) }}" method="POST"> @csrf
                        @method('DELETE') <div class="tm-table-actions-col-right">
                            @if (Auth::user()->position === 'Manager')
                                <button type="button" class="btn btn-danger" data-toggle="modal"
                                    data-target="#deleteCylinderModal">Delete Cylinder</button>
                            @endif
                        </div>
                    </form>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function enableAssignButton() {
                let isCustomerSelected = $('#selectedUserId').val() ? $('#selectedUserId').val().length > 0 : false;
                let isAssignmentTypeSelected = $('input[name="assignmentType"]:checked').length > 0;
                let isValid = isCustomerSelected && isAssignmentTypeSelected;
                if ($('input[name="assignmentType"]:checked').val() === 'delivery') {
                    let isDriverSelected = $('#selectedDriverId').val() ? $('#selectedDriverId').val().length > 0 :
                        false;
                    isValid = isValid && isDriverSelected;
                }
                $('#assignCylinderBtn').prop('disabled', !isValid);
            }

            function setupSearch(inputSelector, dropdownSelector, route, hiddenInputSelector) {
                $(inputSelector).on('input', function() {
                    let query = $(this).val().trim();
                    let dropdown = $(dropdownSelector);
                    if (query.length === 0) {
                        dropdown.hide();
                        return;
                    }
                    $.ajax({
                        url: route,
                        type: "GET",
                        data: {
                            query: query
                        },
                        success: function(response) {
                            dropdown.empty().show();
                            if (response.length === 0) {
                                dropdown.append(
                                    '<div class="dropdown-item text-muted">No matches found</div>'
                                    );
                            } else {
                                response.forEach(user => {
                                    let item = $('<div class="dropdown-item"></div>')
                                        .text(user.first_name + ' ' + user.last_name)
                                        .attr('data-id', user.id).on('click',
                                    function() {
                                            $(inputSelector).val($(this).text());
                                            $(hiddenInputSelector).val($(this).attr(
                                                'data-id'));
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
                } else if ($('#pickupOption').is(':checked')) {
                    $('#pickupFields').show();
                    $('#deliveryFields').hide();
                }
                enableAssignButton();
            });
            $('#assignCylinderBtn').on('click', function() {
                let userId = $('#selectedUserId').val().trim();
                if (!userId) {
                    alert("Please select a customer before assigning the cylinder.");
                    return;
                }
            });
            $('#assignCylinderBtn').on('click', function() {
                let userId = $('#selectedUserId').val().trim();
                let cylinderId = {{ $cylinder->id }};
                let assignmentType = $('input[name="assignmentType"]:checked').val();
                let driverId = $('#selectedDriverId').val() || null;
                let deliveryDate = $('#deliveryDate').val();
                let deliveryTime = $('#deliveryTime').val();
                let pickupLocation = $('#pickupLocation').val();
                let pickupDate = $('#pickupDate').val();
                if (!userId) {
                    alert("Please select a customer before assigning the cylinder.");
                    return;
                }
                if (assignmentType === 'delivery' && !driverId) {
                    alert("Please select a driver before assigning a delivery.");
                    return;
                }
                $.post("{{ route('cylinders.assign') }}", {
                    _token: "{{ csrf_token() }}",
                    user_id: userId,
                    cylinder_id: cylinderId,
                    assignment_type: assignmentType
                }).done(function() {
                    let postData = {
                        _token: "{{ csrf_token() }}",
                        cylinder_id: cylinderId,
                        customer_id: userId
                    };
                    if (assignmentType === 'delivery') {
                        postData.driver_id = driverId;
                        postData.delivery_date = deliveryDate;
                        postData.delivery_time = deliveryTime;
                        $.post("{{ route('deliveries.store') }}", postData).done(() => location
                            .reload()).fail(xhr => alert('Delivery record failed: ' + xhr
                            .responseText));
                    } else {
                        postData.pickup_location = pickupLocation;
                        postData.pick_up_date = pickupDate;
                        $.post("{{ route('pickups.store') }}", postData).done(() => location
                            .reload()).fail(xhr => alert('Pickup record failed: ' + xhr
                            .responseText));
                    }
                }).fail(xhr => alert('Cylinder assignment failed: ' + xhr.responseText));
            });
        });
    </script>
@endsection
