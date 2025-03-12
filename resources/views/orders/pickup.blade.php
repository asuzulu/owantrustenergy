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
                        <h2 class="tm-block-title">Pick Up Orders</h2>
                    </div>
                    <div class="col-auto">
                        <a href="{{ url('/management/cylinders') }}" class="btn btn-primary">Back to List</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped tm-table-striped-even mt-3">
                        <thead>
                            <tr class="tm-bg-gray">
                                <th>Location</th>
                                <th>Cylinder ID</th>
                                <th>Size</th>
                                <th>Customer</th>
                                <th>Date Assigned</th>
                                <th>Pick-Up Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pickups as $pickup)
                                <tr>
                                    <td>{{ $pickup->location }}</td>
                                    <td>{{ $pickup->cylinder }}</td>
                                    <td>{{ $pickup->size }}</td>
                                    <td>{{ $pickup->customer }}</td>
                                    <td>{{ $pickup->date_assigned }}</td>
                                    <td>{{ $pickup->pick_up_date }}</td>
                                    <td>
                                        <button class="btn btn-success mark-picked-up-btn"
                                                data-id="{{ $pickup->id }}"
                                                data-cylinder="{{ $pickup->cylinder }}">
                                            Mark as Picked Up
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($pickups->hasPages())
                    <div style="text-align: center; margin-top: 20px;">
                        {{ $pickups->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Pickup Confirmation Modal -->
    <div class="modal fade" id="pickupModal" tabindex="-1" aria-labelledby="pickupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pickupModalLabel">Confirm Pick-Up</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="pickupForm" method="POST" action="{{ route('orders.updatePickup') }}">
                    @csrf
                    <input type="hidden" name="order_id" id="pickupOrderId">
                    <input type="hidden" name="cylinder_id" id="pickupCylinderId">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="pickupDate">Pick-Up Date</label>
                            <input type="date" class="form-control" id="pickupDate" name="pickup_date" required>
                        </div>
                        <div class="form-group">
                            <label for="pickupTime">Pick-Up Time</label>
                            <input type="time" class="form-control" id="pickupTime" name="pickup_time" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.mark-picked-up-btn').click(function() {
            let orderId = $(this).data('id');
            let cylinderId = $(this).data('cylinder');

            $('#pickupOrderId').val(orderId);
            $('#pickupCylinderId').val(cylinderId);

            $('#pickupModal').modal('show');
        });

        $('#pickupForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('orders.updatePickup') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#pickupModal').modal('hide');
                        location.reload();
                    } else {
                        alert("Error updating the order. Please try again.");
                    }
                },
                error: function() {
                    alert("An error occurred while processing the request.");
                }
            });
        });
    });
</script>
@endpush
