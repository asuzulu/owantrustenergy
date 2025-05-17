@if (!Auth::check() || Auth::user()->position === 'Customer')
    <script>
        window.location.href = "{{ route('home') }}";
    </script>
    @php exit; @endphp
@endif

@if (Auth::user()->position === null)
    <script>
        window.location.href = "/";
    </script>
@endif

@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : (Auth::user()->position === 'Agent' ? 'layouts.agent-dashboard' : 'layouts.default-dashboard')))

@section('content')
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">
                <div class="row">
                    <div class="col-md-2 col-sm-12">
                        <h2 class="tm-block-title d-inline-block">Deliveries</h2>
                    </div>
                    <div class="col-md-10 col-sm-12">
                        <div
                            class="d-flex justify-content-center align-items-center flex-wrap position-relative button-container">
                            <a href="{{ route('management.cylinders') }}" class="btn btn-primary mb-2 mx-2">Back to Cylinders
                                List</a>

                            <a href="{{ route('drivers.index') }}" class="btn btn-primary mb-2 mx-2">Back to Drivers
                                List</a>

                            <a href="{{ route('orders.pickup') }}" class="btn btn-primary mb-2 mx-2">Pick Ups</a>

                            <a href="{{ route('management.orders.requests') }}" class="btn btn-primary mb-2 mx-2">Customers'
                                Requests</a>
                        </div>
                    </div>
                </div>

                {{-- Deliveries Table --}}
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
                                @if (in_array(Auth::user()->position, ['Manager', 'Employee']))
                                    <th scope="col">Action</th> {{-- ── NEW: only for Manager/Employee --}}
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deliveries as $delivery)
                                <tr id="delivery-row-{{ $delivery->id }}">
                                    <td>{{ $delivery->id }}</td>
                                    <td class=click style="color: rgb(2, 216, 2)">
                                        <a href="{{ route('cylinders.show', $delivery->cylinder) }}"
                                            style="text-decoration: none; color: inherit;">
                                            {{ $delivery->cylinder }}
                                        </a>
                                    </td>
                                    <td>{{ $delivery->size }}</td>
                                    <td class=click style="color: rgb(2, 216, 2);">
                                        <a href="{{ route('drivers.profile', $delivery->driver_id) }}"
                                            style="text-decoration: none; color: inherit;">
                                            {{ $delivery->driver }}
                                        </a>
                                    </td>
                                    <td class=click style="color: rgb(2, 216, 2);">
                                        @if (isset($delivery->customer_id))
                                            <a href="{{ route('users.profile', $delivery->customer_id) }}"
                                                style="text-decoration: none; color: inherit;">
                                                {{ $delivery->customer }}
                                            </a>
                                        @else
                                            {{ $delivery->customer }}
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($delivery->delivery_date)->format('d-m-Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($delivery->delivery_time)->format('h:i A') }}</td>
                                    <td>{{ $delivery->status }}</td>

                                    @if (in_array(Auth::user()->position, ['Manager', 'Employee']))
                                        <td>
                                            {{-- ── CHANGED: if delivered, show Approve/Disapprove or archived label --}}
                                            @if ($delivery->date_delivered && !$delivery->approval)
                                                <form action="{{ route('deliveries.approve', $delivery->id) }}"
                                                    method="POST" style="display:inline-block">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                </form>
                                                <form action="{{ route('deliveries.disapprove', $delivery->id) }}"
                                                    method="POST" style="display:inline-block">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-warning btn-sm">Disapprove</button>
                                                </form>
                                            @elseif($delivery->approval)
                                                {{ ucfirst($delivery->approval) }} {{-- Show “Approved” or “Disapproved” --}}
                                            @else
                                                &mdash; {{-- no action until delivered --}}
                                            @endif
                                        </td>
                                    @endif

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($deliveries->hasPages())
                    <div style="text-align: center; margin-top: 20px;">
                        {{ $deliveries->links('pagination::bootstrap-4') }}
                    </div>
                @endif

            </div>
        </div>
    </div>

    <style>
        .click:hover {
            font-weight: 800;
        }
    </style>
@endsection

@section('scripts')
    @include('partials.dashboard.scripts')

    @if (in_array(Auth::user()->position, ['Manager', 'Employee']))
        <script>
            $(document).ready(function() {
                $('.delete-delivery').click(function() {
                    let deliveryId = $(this).data('id');
                    if (!confirm('Are you sure you want to delete this delivery?')) return;
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
                });
            });
        </script>
    @endif
@endsection
