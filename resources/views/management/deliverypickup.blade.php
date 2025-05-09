@extends(
    auth()->check() && auth()->user()->position === 'Manager'
    ? 'layouts.management-dashboard'
    : (auth()->check() && auth()->user()->position === 'Employee'
        ? 'layouts.employee-dashboard'
        : (auth()->check() && auth()->user()->position === 'Driver'
            ? 'layouts.drivers-dashboard'
            : 'layouts.default-dashboard'))
)

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
                    <div class="col-md-4"><h2 class="tm-block-title">Deliveries</h2></div>
                    <div class="col-md-8">
                        <form method="GET" action="{{ route('delivery.pickup') }}" class="d-flex">
                            <input
                                type="text"
                                name="search"
                                value="{{ $search ?? '' }}"
                                class="form-control"
                                placeholder="Search by cylinder or customer..."
                                aria-label="Search Deliveries"
                            >
                            <button class="btn btn-primary ml-2">Search</button>
                        </form>
                    </div>
                </div>

                <form method="POST" action="{{ route('deliveries.updateApproval') }}">
                    @csrf

                    <div class="table-responsive">
                        <table class="table table-hover table-striped tm-table-striped-even">
                            <thead>
                                <tr class="tm-bg-gray">
                                    <th></th>
                                    <th>Cylinder #</th>
                                    <th>Customer</th>
                                    <th>Driver Name</th>
                                    <th>Driver Pickup</th>
                                    <th>Scheduled Delivery</th>
                                    <th>Status</th>
                                    <th>Approval</th>
                                    <th>Image</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deliveries as $d)
                                    @php
                                        $pickup = $d->driver_pickup_date
                                            ? $d->driver_pickup_date->format('F j, Y') . ' at ' . $d->driver_pickup_time->format('H:i')
                                            : '-';

                                        $sched = $d->delivery_date->format('F j, Y') . ' at ' . $d->delivery_time->format('H:i');

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
                                            @if($status==='Delivered')
                                                <input
                                                    type="checkbox"
                                                    name="deliveries[]"
                                                    value="{{ $d->id }}"
                                                >
                                            @endif
                                        </td>
                                        <td>{{ $d->cylinder }}</td>
                                        <td>{{ $d->customer }}</td>
                                        <td>{{ $driverName }}</td>
                                        <td>{{ $pickup }}</td>
                                        <td>{{ $sched }}</td>
                                        <td>{{ $status }}</td>
                                        <td>{{ ucfirst($d->approval ?? '—') }}</td>
                                        <td>
                                            @if($d->image_path)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-info"
                                                    data-toggle="modal"
                                                    data-target="#imageModal{{ $d->id }}"
                                                >View</button>

                                                {{-- Image Modal --}}
                                                <div class="modal fade" id="imageModal{{ $d->id }}" tabindex="-1" role="dialog">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Delivery Image</h5>
                                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <img
                                                                    src="{{ $d->image_path }}"
                                                                    alt="Delivery Image"
                                                                    class="img-fluid"
                                                                >
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                —
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

                    {{-- Approve / Disapprove Buttons --}}
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" name="action" value="approved" class="btn btn-success mr-2">
                            Approve
                        </button>
                        <button type="submit" name="action" value="disapproved" class="btn btn-danger">
                            Disapprove
                        </button>
                    </div>
                </form>

                {{-- Pagination --}}
                @if($deliveries->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $deliveries->links('pagination::bootstrap-4') }}
                    </div>
                @endif

            </div>
        </div>
    </div>

    <style>
        .tm-table-striped-even th,
        .tm-table-striped-even td { padding: 15px; }
    </style>
@endsection
