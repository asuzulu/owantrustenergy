@extends(auth()->user()->position === 'Manager' ? 'layouts.management-dashboard' : (auth()->user()->position === 'Employee' ? 'layouts.employee-dashboard' : 'layouts.drivers-dashboard'))

@section('content')
    <div class="container my-5">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100" style="margin-top: -6rem;">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="tm-block-title d-inline-block">Review Disapproved Deliveries</h2>
                    </div>
                </div>

                {{-- Table of Disapproved Deliveries --}}
                <div class="table-responsive">
                    <table class="table table-hover table-striped tm-table-striped-even mt-3">
                        <thead class="tm-bg-gray">
                            <tr>
                                <th>Cylinder ID</th>
                                <th>Size</th>
                                <th>Weight</th>
                                <th>Customer</th>
                                <th>Driver</th>
                                <th>Date Delivered</th>
                                <th>Time Delivered</th>
                                <th>Delivery Image</th>
                                <th>Disapproval Reason</th>
                                <th>Contact Customer</th>
                                <th>Contact Driver</th>
                                <th>Was Cylinder Delivered?</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $driver = DB::connection()->getDriverName();

                                if ($driver === 'mysql') {
                                    $disapproved = \App\Models\Delivery::where('approval', 'disapproved')
                                        ->orderByRaw("LPAD(cylinder, 9, '0')")
                                        ->paginate(10);
                                } else {
                                    // SQLite fallback (no LPAD, just order normally or cast as integer if needed)
                                    $disapproved = \App\Models\Delivery::where('approval', 'disapproved')
                                        ->orderByRaw('CAST(cylinder AS INTEGER)')
                                        ->paginate(10);
                                }
                            @endphp

                            @forelse ($disapproved as $d)
                                <tr>
                                    <td>{{ str_pad($d->cylinder, 9, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $d->size }}</td>
                                    <td>
                                        @switch($d->size)
                                            @case('Small')
                                                3kg
                                            @break

                                            @case('Medium')
                                                5kg
                                            @break

                                            @case('Large')
                                                12kg
                                            @break

                                            @case('Extra Large')
                                                25kg
                                            @break

                                            @default
                                                {{ $d->size }}
                                        @endswitch
                                    </td>
                                    <td>{{ $d->customer }}</td>
                                    <td>{{ $d->driver }}</td>
                                    <td>{{ optional($d->date_delivered)->format('d-m-Y') }}</td>
                                    <td>{{ optional($d->time_delivered)->format('H:i') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-toggle="modal"
                                            data-target="#deliveryImageModal" data-img="{{ $d->image_path }}">
                                            View
                                        </button>
                                    </td>
                                    <td>
                                        @if ($d->disapproval_reason)
                                            <button class="btn btn-sm btn-info" data-toggle="modal"
                                                data-target="#disapprovalReasonModal{{ $d->id }}">
                                                View
                                            </button>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                            data-target="#contactCustomerModal" data-customer="{{ $d->customer }}">
                                            Contact
                                        </button>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" data-toggle="modal"
                                            data-target="#contactDriverModal" data-driver="{{ $d->driver }}">
                                            Contact
                                        </button>
                                    </td>
                                    <td>
                                        <select class="form-control form-control-sm was-delivered-select"
                                            data-id="{{ $d->id }}" style="height: 3.5rem;">
                                            <option value="">Select</option>
                                            <option value="yes">Yes</option>
                                            <option value="no">No</option>
                                        </select>
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">No disapproved deliveries found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if ($disapproved->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $disapproved->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Delivery Image Modal --}}
        <div class="modal fade" id="deliveryImageModal" tabindex="-1" role="dialog" aria-labelledby="deliveryImageLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="deliveryImageLabel" class="modal-title">Delivery Image</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="" id="deliveryImage" class="img-fluid" alt="Delivery Image">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disapproval Reason Modal for Delivery ID: {{ $d->id }} -->
        <div class="modal fade" id="disapprovalReasonModal{{ $d->id }}" tabindex="-1" role="dialog"
            aria-labelledby="disapprovalReasonLabel{{ $d->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="disapprovalReasonLabel{{ $d->id }}" class="modal-title">Disapproval Reason for Cylinder
                            ID {{ str_pad($d->cylinder, 9, '0', STR_PAD_LEFT) }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                        <p class="text-justify">{{ $d->disapproval_reason }}</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Was Cylinder Delivered? Modal --}}
        <div class="modal fade" id="wasDeliveredModal" tabindex="-1" role="dialog" aria-labelledby="wasDeliveredLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="wasDeliveredForm" method="POST" action="{{ route('deliveries.markDelivered') }}">
                    @csrf
                    <input type="hidden" name="delivery_id" id="wasDeliveredId">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="wasDeliveredLabel" class="modal-title">Why was the cylinder not delivered?</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <textarea name="reason" id="wasDeliveredReason" class="form-control" rows="4" placeholder="Please explain..."
                                required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit Reason</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Contact Customer Modal --}}
        <div class="modal fade" id="contactCustomerModal" tabindex="-1" role="dialog"
            aria-labelledby="contactCustomerLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="contactCustomerForm" method="POST" action="{{ route('deliveries.contactCustomer') }}">
                    @csrf
                    <input type="hidden" name="delivery_id" id="contactCustomerId">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="contactCustomerLabel" class="modal-title">Customer Info</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>Name: <span id="custName"></span></p>
                            <p>Email: <span id="custEmail"></span></p>
                            <p>Phone: <span id="custPhone"></span></p>
                            <div class="form-group">
                                <label for="custResponse">Customer Response</label>
                                <textarea name="disapproval_customer_response" id="custResponse" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit Response</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Contact Driver Modal --}}
        <div class="modal fade" id="contactDriverModal" tabindex="-1" role="dialog" aria-labelledby="contactDriverLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="contactDriverForm" method="POST" action="{{ route('deliveries.contactDriver') }}">
                    @csrf
                    <input type="hidden" name="delivery_id" id="contactDriverId">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="contactDriverLabel" class="modal-title">Driver Info</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>Name: <span id="drvName"></span></p>
                            <p>Email: <span id="drvEmail"></span></p>
                            <p>Phone: <span id="drvPhone"></span></p>
                            <div class="form-group">
                                <label for="drvResponse">Driver Response</label>
                                <textarea name="disapproval_driver_response" id="drvResponse" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit Response</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endsection

    @push('scripts')
        <script>
            $(function() {
                // Delivery Image Modal population
                $('#deliveryImageModal').on('show.bs.modal', function(e) {
                    let img = $(e.relatedTarget).data('img');
                    $('#deliveryImage').attr('src', img);
                });

                // Was Delivered dropdown
                $('.was-delivered-select').change(function() {
                    let val = $(this).val(),
                        id = $(this).data('id');
                    if (val === 'no') {
                        $('#wasDeliveredId').val(id);
                        $('#wasDeliveredModal').modal('show');
                    } else {
                        // Mark as delivered without reason
                        $.post("{{ route('deliveries.markDelivered') }}", {
                            _token: "{{ csrf_token() }}",
                            delivery_id: id,
                            was_delivered: 'yes'
                        }).done(() => location.reload());
                    }
                });

                // Contact Customer Modal
                $('#contactCustomerModal').on('show.bs.modal', function(e) {
                    let cust = $(e.relatedTarget).data('customer');
                    let deliveryId = $(e.relatedTarget).closest('tr').find('.was-delivered-select').data('id');
                    $('#contactCustomerId').val(deliveryId);

                    // fetch user details
                    $.getJSON("{{ route('deliveries.customerInfo') }}", {
                            customer: cust
                        })
                        .done(data => {
                            $('#custName').text(data.name);
                            $('#custEmail').text(data.email);
                            $('#custPhone').text(data.phone);
                        });
                });

                // Contact Driver Modal
                $('#contactDriverModal').on('show.bs.modal', function(e) {
                    let drv = $(e.relatedTarget).data('driver');
                    let deliveryId = $(e.relatedTarget).closest('tr').find('.was-delivered-select').data('id');
                    $('#contactDriverId').val(deliveryId);

                    $.getJSON("{{ route('deliveries.driverInfo') }}", {
                            driver: drv
                        })
                        .done(data => {
                            $('#drvName').text(data.name);
                            $('#drvEmail').text(data.email);
                            $('#drvPhone').text(data.phone);
                        });
                });

                // Form validations
                $('#wasDeliveredForm').submit(function() {
                    let words = $('#wasDeliveredReason').val().trim().split(/\s+/).length;
                    if (words < 10) {
                        alert('Please provide at least ten words.');
                        return false;
                    }
                });
                $('#contactCustomerForm').submit(function() {
                    let words = $('#custResponse').val().trim().split(/\s+/).length;
                    if (words < 15) {
                        alert('Response too short.');
                        return false;
                    }
                });
                $('#contactDriverForm').submit(function() {
                    let words = $('#drvResponse').val().trim().split(/\s+/).length;
                    if (words < 15) {
                        alert('Response too short.');
                        return false;
                    }
                });
            });
        </script>
    @endpush
