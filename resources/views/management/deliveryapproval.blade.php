<?php
use Illuminate\Support\Carbon;
?>

@extends(auth()->check() && auth()->user()->position === 'Manager' ? 'layouts.management-dashboard' : (auth()->check() && auth()->user()->position === 'Employee' ? 'layouts.employee-dashboard' : (auth()->check() && auth()->user()->position === 'Driver' ? 'layouts.drivers-dashboard' : 'layouts.default-dashboard')))

@php
    if (!auth()->check()) {
        header('Location: ' . url('/'));
        exit();
    }
@endphp

@section('content')
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">

                {{-- Header + Search --}}
                <div class="row mb-3 align-items-center">
                    <div class="col-md-4">
                        <h2 class="tm-block-title">Deliveries to be Approved</h2>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap justify-content-center align-items-start position-relative button-container mb-4"
                            style="min-height: 55px;">
                            <form method="GET" action="{{ route('management.deliveries') }}" class="d-flex mb-2 mx-2">
                                <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control"
                                    placeholder="Search by cylinder or customer..." aria-label="Search Deliveries"
                                    style="height: 55px; width: 300px;" />
                                <button type="submit" class="btn btn-primary ml-2" style="height: 55px;">
                                    Search
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <form id="approvalForm" method="POST" action="{{ route('deliveries.updateApproval') }}">
                    @csrf

                    <div class="table-responsive">
                        <table class="table table-hover table-striped tm-table-striped-even">
                            <thead>
                                <tr class="tm-bg-gray">
                                    <th>
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th>Cylinder #</th>
                                    <th>Customer</th>
                                    <th>Driver Name</th>
                                    <th>Driver Pickup</th>
                                    <th>Scheduled Delivery</th>
                                    <th>Status</th>
                                    <th>Image</th>
                                    <th>Approval</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deliveries as $d)
                                    @php
                                        $pickupDate = $d->driver_pickup_date
                                            ? Carbon::parse($d->driver_pickup_date)
                                            : null;
                                        $pickupTime = $d->driver_pickup_time
                                            ? Carbon::parse($d->driver_pickup_time)
                                            : null;

                                        $pickup =
                                            $pickupDate && $pickupTime
                                                ? $pickupDate->format('F j, Y') . ' at ' . $pickupTime->format('H:i')
                                                : '-';

                                        $schedDate = $d->scheduled_date ? Carbon::parse($d->scheduled_date) : null;
                                        $schedTime = $d->scheduled_time ? Carbon::parse($d->scheduled_time) : null;

                                        $sched =
                                            $schedDate && $schedTime
                                                ? $schedDate->format('F j, Y') . ' at ' . $schedTime->format('H:i')
                                                : '-';

                                        if (!$d->driver_pickup_date) {
                                            $status = 'Pending';
                                        } elseif ($d->date_delivered === null) {
                                            $status = 'Delivering';
                                        } else {
                                            $status = 'Delivered';
                                        }

                                        $driverName = $d->driverUser
                                            ? $d->driverUser->first_name . ' ' . $d->driverUser->last_name
                                            : '-';
                                    @endphp
                                    <tr>
                                        <td>
                                            @if ($status === 'Delivered')
                                                <input type="checkbox" class="delivery-checkbox" name="deliveries[]"
                                                    value="{{ $d->id }}">
                                            @endif
                                        </td>
                                        <td>{{ $d->cylinder }}</td>
                                        <td>{{ $d->customer }}</td>
                                        <td>{{ $driverName }}</td>
                                        <td>{{ $pickup }}</td>
                                        <td>{{ $sched }}</td>
                                        <td>{{ $status }}</td>
                                        <td>
                                            @if ($d->image_path)
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                                    data-target="#imageModal{{ $d->id }}">View</button>

                                                {{-- Image Modal --}}
                                                <div class="modal fade" id="imageModal{{ $d->id }}" tabindex="-1"
                                                    role="dialog">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Delivery Image</h5>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <img src="{{ asset('storage/' . $d->image_path) }}"
                                                                    alt="Delivery Image" class="img-fluid">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($d->approval === 'disapproved')
                                                <div class="text-danger font-weight-bold">Disapproved</div>
                                                <a href="{{ route('deliveries.review', $d->id) }}"
                                                    class="btn btn-sm btn-warning mt-1">Review</a>
                                            @else
                                                {{ ucfirst($d->approval ?? 'Awaiting Approval') }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No deliveries found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Always put your hidden “disapproved” input BEFORE the approve/disapprove buttons. -->
                    <input type="hidden" name="action" value="disapproved" id="disapproveActionInput"
                        style="display:none;">
                    <input type="hidden" name="reason" value="" id="disapproveReasonInput" style="display:none;">

                    {{-- Approve / Disapprove Buttons --}}
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" name="action" value="approved" class="btn btn-success mr-2">
                            Approve
                        </button>
                        <!-- Disapprove now opens a modal instead of directly submitting -->
                        <button type="button" class="btn btn-danger" id="openDisapproveModalBtn">
                            Disapprove
                        </button>
                    </div>
                </form>

                {{-- Pagination --}}
                @if ($deliveries->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $deliveries->links('pagination::bootstrap-4') }}
                    </div>
                @endif

            </div>
        </div>
    </div>
    <!-- Disapprove Bulk-Action Modal -->
    <div class="modal fade" id="bulkDisapproveModal" tabindex="-1" role="dialog"
        aria-labelledby="bulkDisapproveModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="bulkDisapproveForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkDisapproveModalLabel">Disapprove Deliveries</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="bulkDisapproveReason">Reason for Disapproval</label>
                            <textarea class="form-control" id="bulkDisapproveReason" rows="4"
                                placeholder="Please explain why you are disapproving these deliveries..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" id="submitBulkDisapproveBtn" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <style>
        .tm-table-striped-even th,
        .tm-table-striped-even td {
            padding: 15px;
        }
    </style>

    <script>
        // Select / Deselect all checkboxes
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.delivery-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        // When “Disapprove” button is clicked, open the modal
        document.getElementById('openDisapproveModalBtn').addEventListener('click', function() {
            // If no deliveries are checked, alert and do not open the modal
            const checkedBoxes = document.querySelectorAll('.delivery-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Please select at least one delivered item to disapprove.');
                return;
            }
            // Otherwise, show the modal
            $('#bulkDisapproveModal').modal('show');
        });

        // Handle “Submit” in the Disapprove modal
        document.getElementById('submitBulkDisapproveBtn').addEventListener('click', function() {
            const text = document.getElementById('bulkDisapproveReason').value.trim();
            const wordCount = text.split(/\s+/).filter(w => w.length > 0).length;

            if (wordCount < 10) {
                alert('Response is too short.');
                return;
            }

            // Populate hidden inputs in the main form
            document.getElementById('disapproveReasonInput').value = text;
            document.getElementById('disapproveActionInput').value = 'disapproved';

            // Finally, submit the main form
            document.getElementById('approvalForm').submit();
        });
    </script>
@endsection
