// resources/views/warehouses/show.blade.php
@extends((Auth::check() && Auth::user()->position === 'Manager') ? 'layouts.management-dashboard' : 'layouts.default')

@section('content')
<div class="container" style="margin-top: -6rem;">
    <div class="row tm-content-row tm-mt-big">
        <div class="bg-white tm-block h-100">
            <div class="row">
                <div class="col-md-8 col-sm-12">
                    <h2 class="tm-block-title d-inline-block">Warehouse Details</h2>
                </div>
                <div class="col-md-4 col-sm-12 text-right">
                    <a href="{{ route('warehouses.index') }}" class="btn btn-small btn-secondary">Back to WarehouseList</a>
                    @if(Auth::check() && Auth::user()->position === 'Manager')
                    <button class="btn btn-small btn-primary" data-toggle="modal" data-target="#editModal">Edit</button>
                    <button class="btn btn-small btn-danger" data-toggle="modal" data-target="#deleteModal">Delete</button>
                    @endif
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
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $warehouse->name) }}">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $warehouse->address) }}">
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $warehouse->phone_number) }}">
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
