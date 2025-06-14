{{-- resources/views/orders/review.blade.php --}}
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
                        <h2 class="tm-block-title">Order Details</h2>
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

    {{-- Scripts --}}
    @push('scripts')
        <script>
            $(function() {
                // Contact Customer
                $('#contactCustomerBtn').click(function() {
                    $.getJSON("{{ route('orders.contactCustomer', $order->id) }}", function(user) {
                        $('#custName').text(user.first_name + ' ' + user.last_name);
                        $('#custPhone').text(user.phone);
                        $('#custEmail').text(user.email);
                        $('#contactCustomerModal').modal('show');
                    });
                });

                // Approve Order → redirect via the approve route
                $('#approveOrderBtn').on('click', function() {
                    window.location = "{{ route('orders.approveOrder', $order->id) }}";
                });

                // Assign to User → redirect to cylinder show, open modal,
                // prefill with customer name & assignmentType
                $('#assignToUserBtn').click(function() {
                    const cylId = "{{ $order->cylinder_id }}";
                    const custName = encodeURIComponent("{{ $order->first_name }} {{ $order->last_name }}");
                    const assignType = "{{ $order->retrieval === 'delivery' ? 'delivery' : 'pickup' }}";
                    const url =
                        `/cylinders/${cylId}?openAssign=1&customerName=${custName}&assignmentType=${assignType}`;
                    window.location = url;
                });
            });
        </script>
    @endpush
@endsection
