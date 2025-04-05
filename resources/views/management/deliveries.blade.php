@if (!Auth::check() || Auth::user()->position === 'Customer')
    <script>
        window.location.href = "{{ route('home') }}";
    </script>
    @php exit; @endphp
@endif

@if (Auth::user()->position === null)
    <script>window.location.href = "/";</script>
@endif

@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : (Auth::user()->position === 'Agent' ? 'layouts.agent-dashboard' : 'layouts.default-dashboard')))
@section('content')
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">
                <div class="row">
                    <div class="col-md-3 col-sm-12">
                        <h2 class="tm-block-title d-inline-block">Deliveries</h2>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <a href="{{ route('management.cylinders') }}" class="btn btn-primary">Back to Cylinders List</a>
                    </div>
                    <div class="col-md-6 col-sm-12 text-end">
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('orders.pickup') }}" class="btn btn-primary me-3">Pick Up Orders</a>
                            <a href="{{ route('management.orders.requests') }}" class="btn btn-primary">Customers' Requests</a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped tm-table-striped-even mt-3">
                        <thead>
                            <tr class="tm-bg-gray">
                                <th scope="col">ID</th>
                                <th scope="col">Cylinder</th>
                                <th scope="col">Size</th>
                                <th scope="col">Driver</th>
                                <th scope="col">Customer</th>
                                <th scope="col">Delivery Date</th>
                                <th scope="col">Delivery Time</th>
                                <th scope="col">Status</th>
                                @if (Auth::user()->position === 'Manager' || Auth::user()->position === 'Employee')
                                    <th scope="col">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deliveries as $delivery)
                                <tr id="delivery-row-{{ $delivery->id }}">
                                    <td>{{ $delivery->id }}</td>
                                    <td>
                                        <a href="{{ route('cylinders.show', $delivery->cylinder) }}" style="text-decoration: none; color: inherit;">
                                            {{ $delivery->cylinder }}
                                        </a>
                                    </td>
                                    <td>{{ $delivery->size }}</td>
                                    <td>
                                        <a href="{{ route('drivers.profile', $delivery->driver_id) }}" style="text-decoration: none; color: inherit;">
                                            {{ $delivery->driver }}
                                        </a>
                                    </td>
                                    <td>
                                        @if(isset($delivery->customer_id))
                                            <a href="{{ route('users.profile', $delivery->customer_id) }}" style="text-decoration: none; color: inherit;">
                                                {{ $delivery->customer }}
                                            </a>
                                        @else
                                            {{ $delivery->customer }}
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($delivery->delivery_date)->format('d-m-Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($delivery->delivery_time)->format('h:i A') }}</td>
                                    <td>
                                        @if ($delivery->date_delivered)
                                            Delivered on {{ \Carbon\Carbon::parse($delivery->date_delivered)->format('d-m-Y') }}
                                        @else
                                            Pending
                                        @endif
                                    </td>
                                    @if (Auth::user()->position === 'Manager' || Auth::user()->position === 'Employee')
                                        <td>
                                            <button class="btn btn-danger btn-sm delete-delivery" data-id="{{ $delivery->id }}">Delete</button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($deliveries->hasPages())
                <div style="text-align: center; margin-top: 20px;">
                    {{ $deliveries->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
    @endsection

    @section('scripts')
    @include('partials.dashboard.scripts')
    @if (Auth::user()->position === 'Manager' || Auth::user()->position === 'Employee')
        <script>
            $(document).ready(function() {
                $('.delete-delivery').click(function() {
                    let deliveryId = $(this).data('id');
                    if (confirm('Are you sure you want to delete this delivery?')) {
                        $.ajax({
                            url: "{{ route('management.deliveries.delete') }}",
                            type: "DELETE",
                            data: {
                                _token: "{{ csrf_token() }}",
                                id: deliveryId
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#delivery-row-' + deliveryId).remove();
                                    alert('Delivery deleted successfully.');
                                } else {
                                    alert('Error deleting delivery.');
                                }
                            },
                            error: function() {
                                alert('Error deleting delivery.');
                            }
                        });
                    }
                });
            });
        </script>
    @endif
@endsection
