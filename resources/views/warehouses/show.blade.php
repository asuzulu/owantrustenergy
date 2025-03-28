@extends(Auth::check() && Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : 'layouts.default')

@section('content')
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">
                <div class="row align-items-center">
                    <div class="col-md-8 col-sm-12">
                        <h2 class="tm-block-title d-inline-block">Warehouse Details</h2>
                    </div>
                    <div class="col-md-4 col-sm-12 text-right">
                        <div class="d-flex flex-nowrap justify-content-end align-items-center" style="white-space: nowrap;">
                            <a href="{{ route('warehouses.index') }}" class="btn btn-small btn-secondary mr-2">Back to
                                WarehouseList</a>
                            @if (Auth::check() && Auth::user()->position === 'Manager')
                                <button class="btn btn-small btn-primary mr-2" data-toggle="modal"
                                    data-target="#editModal">Edit</button>
                                <button class="btn btn-small btn-danger" data-toggle="modal"
                                    data-target="#deleteModal">Delete</button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th scope="row" class="col-md-4">Name</th>
                                <td class="col-md-8">{{ $warehouse->name }}</td>
                            </tr>
                            <tr>
                                <th scope="row" class="col-md-4">Address</th>
                                <td class="col-md-8">{{ $warehouse->address }}</td>
                            </tr>
                            <tr>
                                <th scope="row" class="col-md-4">Phone Number</th>
                                <td class="col-md-8">{{ $warehouse->phone_number }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Warehouse Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Warehouse Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $warehouse->name) }}">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" id="address" name="address"
                                value="{{ old('address', $warehouse->address) }}">
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" maxlength="10"
                                value="{{ old('phone_number', $warehouse->phone_number) }}">
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

    <!-- Delete Warehouse Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this warehouse?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Agent Cylinders Distribution Section -->
    <div class="row tm-content-row tm-mt-big">
        <div class="bg-white tm-block">
            <h3 class="tm-block-title" style="text-align: center">Cylinders Distributed to Agents</h3>
            @php
                $agentCylinders = \Illuminate\Support\Facades\DB::table('agent_cylinders_distribution')
                    ->where('warehouse', $warehouse->name)
                    ->orderBy('created_at', 'desc')
                    ->get();
            @endphp
            @if ($agentCylinders->isNotEmpty())
                <form action="{{ route('warehouses.confirmAgentPickup', $warehouse->id) }}" method="POST">
                    @csrf
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Cylinder #</th>
                                <th>Size</th>
                                <th>Weight</th>
                                <th>Agent</th>
                                <th>Passcode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($agentCylinders as $record)
                                <tr>
                                    <td><input type="checkbox" name="pickup[]" value="{{ $record->id }}"></td>
                                    <td>{{ str_pad($record->cylinder_id, 9, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $record->cylinder_size }}</td>
                                    <td>{{ $record->cylinder_weight }}</td>
                                    <td>{{ $record->agent_name }}</td>
                                    <td>{{ $record->passcode ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Confirm Agent Pickup</button>
                    </div>
                </form>
            @else
                <p style="text-align: center">No distributed cylinders at this warehouse.</p>
            @endif
        </div>
    </div>
@endsection
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
@endsection
