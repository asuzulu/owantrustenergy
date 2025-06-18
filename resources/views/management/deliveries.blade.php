<?php
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
?>
@php
    $user = Auth::user();
@endphp

@if (!$user || $user->position === 'Customer')
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

@php
    $layout = match (Auth::user()->position) {
        'Manager' => 'layouts.management-dashboard',
        'Employee' => 'layouts.employee-dashboard',
        'Agent' => 'layouts.agent-dashboard',
        default => 'layouts.default-dashboard',
    };
@endphp

@extends($layout)

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

                {{-- Compute a flag indicating if any delivery has image_path --}}
                @php
                    // $deliveries is the collection/array passed to this view
                    $showActionColumn = $deliveries->contains(function ($d) {
                        return !is_null($d->image_path);
                    });
                @endphp

                {{-- Deliveries Table --}}
                <div class="table-responsive">
                    <table class="table table-hover table-striped tm-table-striped-even mt-3">
                        <thead>
                            <tr class="tm-bg-gray">
                                <th scope="col">Cylinder</th>
                                <th scope="col">Size</th>
                                <th scope="col">Driver</th>
                                <th scope="col">Customer</th>
                                <th scope="col">Delivery Date</th>
                                <th scope="col">Delivery Time</th>
                                <th scope="col">Tracking</th>
                                {{-- Only show “Action” header if:
                                1) user is Manager or Employee
                                2) and at least one $delivery has image_path != null --}}
                                @if (in_array(Auth::user()->position, ['Manager', 'Employee']) && $showActionColumn)
                                    <th scope="col">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deliveries as $delivery)
                                <tr id="delivery-row-{{ $delivery->id }}">
                                    {{-- Cylinder: no change, links to cylinder.show --}}
                                    <td class="click" style="cursor: pointer; color: rgb(2, 216, 2);">
                                        <a href="{{ route('cylinders.show', $delivery->cylinder) }}"
                                            style="display: block; width: 100%; height: 100%; text-decoration: none; color: inherit;">
                                            {{ $delivery->cylinder }}
                                        </a>
                                    </td>

                                    {{-- Size: now links to driver's profile --}}
                                    <td class="click" style="cursor: pointer;">
                                        <a href="{{ route('drivers.profile', $delivery->driver_id) }}"
                                            style="display: block; width: 100%; height: 100%; text-decoration: none; color: inherit;">
                                            {{ $delivery->size }}
                                        </a>
                                    </td>

                                    {{-- Driver: links to driver --}}
                                    <td class="click" style="cursor: pointer;">
                                        <a href="{{ route('drivers.profile', $delivery->driver_id) }}"
                                            style="display: block; width: 100%; height: 100%; text-decoration: none; color: inherit;">
                                            {{ $delivery->driver }}
                                        </a>
                                    </td>

                                    {{-- Customer: no change --}}
                                    <td class="click" style="cursor: pointer; color: rgb(2, 216, 2);">
                                        @if (isset($delivery->customer_id))
                                            <a href="{{ route('users.profile', $delivery->customer_id) }}"
                                                style="display: block; width: 100%; height: 100%; text-decoration: none; color: inherit;">
                                                {{ $delivery->customer }}
                                            </a>
                                        @else
                                            {{ $delivery->customer }}
                                        @endif
                                    </td>

                                    {{-- Delivery Date: links to driver --}}
                                    <td class="click" style="cursor: pointer;">
                                        <a href="{{ route('drivers.profile', $delivery->driver_id) }}"
                                            style="display: block; width: 100%; height: 100%; text-decoration: none; color: inherit;">
                                            {{ Carbon::parse($delivery->delivery_date)->format('d-m-Y') }} </a>
                                    </td>

                                    {{-- Delivery Time: links to driver --}}
                                    <td class="click" style="cursor: pointer;">
                                        <a href="{{ route('drivers.profile', $delivery->driver_id) }}"
                                            style="display: block; width: 100%; height: 100%; text-decoration: none; color: inherit;">
                                            {{ Carbon::parse($delivery->delivery_time)->format('h:i A') }} </a>
                                    </td>

                                    {{-- Tracking: links to driver --}}
                                    <td class="click" style="cursor: pointer;">
                                        <a href="{{ route('drivers.profile', $delivery->driver_id) }}"
                                            style="display: block; width: 100%; height: 100%; text-decoration: none; color: inherit;">
                                            {{ $delivery->status }}
                                        </a>
                                    </td>

                                    @if (in_array(Auth::user()->position, ['Manager', 'Employee']) && !is_null($delivery->image_path))
                                        <td class="text-center">
                                            @php $isDelivered = !empty($delivery->date_delivered); @endphp

                                            @if ($isDelivered && !$delivery->approval)
                                                <button type="button" class="btn btn-sm btn-blue ms-2"
                                                    data-bs-toggle="modal" data-bs-target="#imageModal{{ $delivery->id }}">
                                                    View Image
                                                </button>
                                            @elseif ($delivery->approval)
                                                @if ($delivery->approval === 'disapproved')
                                                    <span class="text-danger font-weight-bold">Disapproved</span>
                                                    <a href="{{ route('deliveries.review', $delivery->id) }}"
                                                        class="btn btn-sm btn-warning ms-2">Review</a>
                                                @elseif ($delivery->approval === 'approved')
                                                    <span class="text-success font-weight-bold">Approved</span>
                                                @else
                                                    {{ ucfirst($delivery->approval) }}
                                                @endif
                                            @else
                                                &mdash;
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

    {{-- Image Modals for each delivery --}}
    @foreach ($deliveries as $delivery)
        @php
            $isDelivered = !empty($delivery->date_delivered);
        @endphp
        @if ($isDelivered && !is_null($delivery->image_path) && is_null($delivery->approval))
            {{-- Image Modal --}}
            <div class="modal fade" id="imageModal{{ $delivery->id }}" tabindex="-1"
                aria-labelledby="imageModalLabel{{ $delivery->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="imageModalLabel{{ $delivery->id }}">Delivery Image</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="{{ asset('storage/' . $delivery->image_path) }}" class="img-fluid"
                                alt="Delivery Image">
                            <div class="mt-3">
                                @if (is_null($delivery->approval))
                                    {{-- Approve --}}
                                    <form action="{{ route('deliveries.approve', $delivery->id) }}" method="POST"
                                        style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Approve</button>
                                    </form>
                                    {{-- Disapprove --}}
                                    <button type="button" class="btn btn-warning disapprove-btn-in-modal"
                                        data-id="{{ $delivery->id }}">
                                        Disapprove
                                    </button>
                                @else
                                    {{ ucfirst($delivery->approval) }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- SINGLE Disapprove Modal (outside loop) --}}
    <div class="modal fade" id="disapproveModal" tabindex="-1" aria-labelledby="disapproveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="disapproveForm" method="POST" action="{{ route('deliveries.disapprove', 0) }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="disapproveModalLabel">Disapprove Delivery</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="disapproveReason">Reason for Disapproval</label>
                            <textarea class="form-control" id="disapproveReason" name="reason" rows="4"
                                placeholder="Please explain why you are disapproving this delivery..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="submitDisapproveBtn" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        .click:hover {
            font-weight: 800;
        }

        .btn-blue {
            background-color: #007bff;
            color: white;
            border: none;
        }

        .btn-blue:hover {
            background-color: #0069d9;
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
                // === Disapprove Delivery modal trigger ===
                document.querySelectorAll('.disapprove-btn-in-modal').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        const deliveryId = this.dataset.id;
                        // 1) Hide the image modal properly
                        const imgModalEl = document.getElementById('imageModal' + deliveryId);
                        const imgModal = bootstrap.Modal.getInstance(imgModalEl) || new bootstrap.Modal(
                            imgModalEl);
                        imgModal.hide();

                        // 2) Update form action
                        const form = document.getElementById('disapproveForm');
                        let baseAction = form.getAttribute('action'); // e.g. "/deliveries/0/disapprove"
                        let newAction = baseAction.replace(/\/0(?!.*\/0)/, '/' + deliveryId);
                        form.setAttribute('action', newAction);

                        // 3) Clear textarea
                        document.getElementById('disapproveReason').value = '';

                        // 4) Show Disapprove modal
                        const disEl = document.getElementById('disapproveModal');
                        const disModal = bootstrap.Modal.getOrCreateInstance(disEl);
                        disModal.show();
                    });
                });

                // === Disapprove form validation ===
                $('#disapproveForm').on('submit', function(e) {
                    let text = $('#disapproveReason').val().trim();
                    // Count words: split on whitespace, filter out empty
                    let wordCount = text.split(/\s+/).filter(function(w) {
                        return w.length > 0;
                    }).length;

                    if (wordCount < 10) {
                        e.preventDefault();
                        alert('Response is too short.');
                        return false;
                    }
                    // Otherwise, allow the form to submit normally
                    return true;
                });
            });
        </script>
    @endif
@endsection
