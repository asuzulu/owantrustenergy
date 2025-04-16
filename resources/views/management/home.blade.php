<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

@if (!Auth::check() || Auth::user()->position === 'Customer')
    <script>
        window.location.href = "{{ route('home') }}";
    </script>
    @php exit; @endphp
@endif

@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : (Auth::user()->position === 'Agent' ? 'layouts.agent-dashboard' : 'layouts.default-dashboard')))

@section('content')
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">
                <div class="row">
                    <div class="col-md-3 col-sm-12">
                        <h2 class="tm-block-title d-inline-block">Cylinders</h2>
                    </div>
                    @if (Auth::user()->position !== 'Agent')
                        <div class="col-md-9 col-sm-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex">
                                    <a href="{{ route('cylinders.unassigned') }}" class="btn btn-primary ms-3">Unassigned</a>
                                    <a href="{{ route('management.orders.requests') }}" class="btn btn-primary">Customers' Requests</a>
                                    <a href="{{ route('orders.pickup') }}" class="btn btn-primary ms-3">Pick Ups</a>
                                    <a href="{{ route('management.deliveries') }}" class="btn btn-primary ms-3">Deliveries</a>
                                    <button class="btn btn-small btn-primary ms-3" data-toggle="modal" data-target="#addCylinderModal">Add Cylinder</button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped tm-table-striped-even mt-3">
                        <thead>
                            <tr class="tm-bg-gray">
                                <th scope="col">Cylinder #</th>
                                <th scope="col">Size</th>
                                <th scope="col">Weight</th>
                                <th scope="col">Location</th>
                                <th scope="col">Date Allocated</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tbody>
                            @foreach ($cylinders as $cylinder)
                                <tr onclick="window.location='{{ route('management.cylinders.show', $cylinder->id) }}'"
                                    style="cursor: pointer;">
                                    <td>{{ str_pad($cylinder->id, 9, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $cylinder->size }}</td>
                                    <td>
                                        @if ($cylinder->size == 'Small')
                                            3 kg
                                        @elseif($cylinder->size == 'Medium')
                                            5 kg
                                        @elseif($cylinder->size == 'Large')
                                            12 kg
                                        @elseif($cylinder->size == 'Extra Large')
                                            25 kg
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $cylinder->location }}</td>
                                    <td>{{ $cylinder->allocated_date->format('d-m-Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- Pagination Links --}}
                    @if ($cylinders->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $cylinders->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add Cylinder Modal -->
    <div class="modal fade" id="addCylinderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Cylinder</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="cylinderForm">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group">
                            <label for="size">Cylinder Size</label>
                            <select class="form-control" id="size" name="size" required style="height: calc(4rem);">
                                <option value="Small">3kg (Small)</option>
                                <option value="Medium">5kg (Medium)</option>
                                <option value="Large">12kg (Large)</option>
                                <option value="Extra Large">25kg (XL)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="location">Warehouse Location</label>
                            <select class="form-control" id="location" name="location" required
                                style="height: calc(4rem);"></select>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Add</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Fetch warehouse locations dynamically
            $.get("{{ route('locations.getWarehouses') }}", function(data) {
                $("#location").empty().append(data.map(name => `<option value="${name}">${name}</option>`));
            });

            $("#cylinderForm").submit(function(event) {
                event.preventDefault();
                $.ajax({
                    url: "{{ route('cylinders.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        alert("Cylinder added successfully!");
                        location.reload();
                    },
                    error: function(xhr) {
                        alert("Error adding cylinder.");
                    }
                });
            });
        });
    </script>
@endsection
