@extends(
    match (Auth::user()->position) {
        'Manager' => 'layouts.management-dashboard',
        'Employee' => 'layouts.employee-dashboard',
        'Agent' => 'layouts.agent-dashboard',
        default => 'layouts.default-dashboard',
    }
)

@section('content')
    @if (Auth::user()->position === 'Customer')
        <script>
            window.location.href = "{{ route('dashboard') }}";
        </script>
        @php exit; @endphp
    @endif

    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">

                {{-- Header --}}
                <div class="row d-flex justify-content-between align-items-center">
                    <div class="col">
                        <h2 class="tm-block-title">Review Order Details</h2>
                    </div>
                    <div class="col-auto">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">← Back to List</a>
                    </div>
                </div>

                {{-- Details Table --}}
                <div class="table-responsive mt-3">
                    <table class="table table-hover table-striped tm-table-striped-even">
                        <thead>
                            <tr class="tm-bg-gray">
                                <th>Field</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- HIDE these two rows by adding d-none --}}
                            <tr class="d-none">
                                <td><strong>First Name</strong></td>
                                <td>{{ $order->first_name }}</td>
                            </tr>
                            <tr class="d-none">
                                <td><strong>Last Name</strong></td>
                                <td>{{ $order->last_name }}</td>
                            </tr>

                            <tr>
                                <td><strong>Customer</strong></td>
                                <td>
                                    {{ optional($order->customer)->first_name ?? '–' }}
                                    {{ optional($order->customer)->last_name ?? '' }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Cylinder Size</strong></td>
                                <td>{{ $order->cylinder_size }}</td>
                            </tr>
                            <tr>
                                <td><strong>Weight</strong></td>
                                <td>{{ $order->weight }}kg</td>
                            </tr>
                            <tr>
                                <td><strong>Order Type</strong></td>
                                <td>{{ ucfirst($order->order_type) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Retrieval Method</strong></td>
                                <td>{{ ucfirst($order->retrieval) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="d-flex justify-content-center mt-4">
                    <button id="contactCustomerBtn" class="btn btn-info mr-2">
                        Contact Customer
                    </button>
                    <button id="disapproveOrderBtn" class="btn btn-danger mr-2">
                        Disapprove
                    </button>
                    <button id="approveOrderBtn" class="btn btn-success">
                        Approve & Assign
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Contact Customer Modal --}}
    <div class="modal fade" id="contactCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Customer Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Name:</strong> <span id="custName">…</span></p>
                    <p><strong>Phone:</strong> <span id="custPhone">…</span></p>
                    <p><strong>Email:</strong> <span id="custEmail">…</span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- No‑Cylinders Available Modal --}}
    <div class="modal fade" id="noCylindersModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">No Cylinders Available</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Sorry, there are currently <strong>no</strong> cylinders of size
                        <em>{{ $order->cylinder_size }}</em> available in our warehouses. Please check back later.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Disapprove Confirmation Modal --}}
    <div class="modal fade" id="disapproveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Confirm Disapproval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this order request?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep</button>
                    <button type="button" id="confirmDisapproveBtn" class="btn btn-danger">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Styles --}}
    <style>
        .tm-table-striped-even th,
        .tm-table-striped-even td {
            padding: 15px;
        }

        .tm-table-striped-even th:first-child,
        .tm-table-striped-even td:first-child {
            width: 30%;
        }

        .tm-table-striped-even th:last-child,
        .tm-table-striped-even td:last-child {
            width: 70%;
        }
    </style>
@endsection

{{-- Scripts --}}
@push('scripts')
    <script>
        $(function() {
            // Contact Customer (unchanged)
            $('#contactCustomerBtn').click(function() {
                $.getJSON("{{ route('orders.contactCustomer', $order->id) }}", function(user) {
                    $('#custName').text(user.first_name + ' ' + user.last_name);
                    $('#custPhone').text(user.phone_number);
                    $('#custEmail').text(user.email);
                    $('#contactCustomerModal').modal('show');
                });
            });

            // Approve Order → availability check before redirect
            $('#approveOrderBtn').on('click', function() {
                $.get("{{ route('orders.checkAvailability', $order->id) }}")
                    .done(function(resp) {
                        if (resp.available) {
                            // let Laravel pick a cylinder and build the querystring
                            window.location.href = "{{ route('orders.approveOrder', $order->id) }}";
                        } else {
                            $('#noCylindersModal').modal('show');
                        }
                    })
                    .fail(function() {
                        // fallback to original flow
                        window.location.href = "{{ route('orders.approveOrder', $order->id) }}";
                    });
            });

            // Show Disapprove modal
            $('#disapproveOrderBtn').click(function() {
                $('#disapproveModal').modal('show');
            });

            // On confirm, call destroyMatching
            $('#confirmDisapproveBtn').click(function() {
                $.ajax({
                    url: "{{ route('orders.destroyMatching') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cust_fullname: "{{ $order->first_name }} {{ $order->last_name }}",
                        retrieval: "{{ $order->retrieval }}",
                        order_id: "{{ $order->id }}"
                    }
                }).done(function() {
                    location.href = "{{ route('orders.requests') }}"; // redirect back to list
                }).fail(function() {
                    alert('Failed to delete order.');
                });
            });
        });
    </script>
@endpush
