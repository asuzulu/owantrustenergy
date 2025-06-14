@if (Auth::user()->position === 'Customer')
    <script>
        window.location.href = "{{ route('dashboard') }}"; // Redirect to a safe page
    </script>
    @php exit; @endphp
@endif

@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : (Auth::user()->position === 'Agent' ? 'layouts.agent-dashboard' : 'layouts.default-dashboard')))

@section('content')
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">
                <div class="row d-flex justify-content-between align-items-center">
                    <div class="col">
                        <h2 class="tm-block-title">Customer Requests</h2>
                    </div>
                    <div class="col-auto">
                        <a href="{{ url('/management/cylinders') }}" class="btn btn-primary">Back to Cylinders List</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped tm-table-striped-even mt-3">
                        <thead>
                            <tr class="tm-bg-gray">
                                <th>ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Cylinder Size</th>
                                <th>Weight</th>
                                <th>Order Type</th>
                                <th>Retrieval Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr class="clickable-row" data-href="{{ route('orders.review', $order->id) }}"
                                    style="cursor: pointer;">
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->first_name }}</td>
                                    <td>{{ $order->last_name }}</td>
                                    <td>{{ $order->cylinder_size }}</td>
                                    <td>{{ $order->weight }}</td>
                                    <td>{{ $order->order_type }}</td>
                                    <td>{{ $order->retrieval }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No orders found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($orders->hasPages())
                    <div style="text-align: center; margin-top: 20px;">
                        {{ $orders->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.clickable-row').forEach(function(row) {
                row.addEventListener('click', function() {
                    window.location.href = this.dataset.href;
                });
            });
        });
    </script>
@endpush
