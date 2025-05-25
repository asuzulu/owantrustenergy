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
                    <div class="col-md-3 col-sm-12">
                        <a href="{{ route('management.cylinders') }}" class="btn btn-primary">Back to Cylinders List</a>
                    </div>
                    <div class="col-md-6 col-sm-12 text-end">
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('management.deliveries') }}" class="btn btn-primary me-3">Deliveries</a>
                            <a href="{{ route('management.orders.requests') }}" class="btn btn-primary">Customers'
                                Requests</a>
                        </div>
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pickups as $pickup)
                                <tr>
                                    <td>{{ $pickup->location }}</td>
                                    <td>{{ $pickup->cylinder }}</td>
                                    <td>{{ $pickup->size }}</td>
                                    <td>{{ $pickup->customer }}</td>
                                    <td>{{ $pickup->date_assigned }}</td>
                                    <td>{{ $pickup->pick_up_date }}</td>
                                    <td>
                                        <button class="btn btn-success mark-picked-up-btn" data-id="{{ $pickup->id }}">
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to confirm customer pick-up?</p>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="pickupId"> {{-- Holds the pickup ID --}}
                <input type="hidden" name="_token" id="csrfToken" value="{{ csrf_token() }}"> {{-- CSRF token --}}
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmPickupBtn" class="btn btn-primary">Yes, Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // When "Mark as Picked Up" button is clicked, store its ID and show modal
            $('.mark-picked-up-btn').click(function() {
                let pickupId = $(this).data('id');
                $('#pickupId').val(pickupId);
                let modal = new bootstrap.Modal(document.getElementById('pickupModal'));
                modal.show();
            });

            // When "Yes, Confirm" is clicked, send AJAX to update with current date/time
            $('#confirmPickupBtn').click(function() {
                let pickupId = $('#pickupId').val();
                let token = $('#csrfToken').val();

                $.ajax({
                    url: "{{ route('pickups.update') }}",
                    method: "POST",
                    data: {
                        _token: token,
                        pickup_id: pickupId
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#pickupModal').modal('hide');
                            location.reload();
                        } else {
                            alert("Error updating pickup. Please try again.");
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
