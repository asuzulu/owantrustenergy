<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : 'layouts.employee-dashboard')

@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<div class="container" style="margin-top: -6rem;">
    <div class="row tm-content-row tm-mt-big">
        <div class="bg-white tm-block h-100">
            <div class="row">
                <div class="col-md-8 col-sm-12">
                    <h2 class="tm-block-title d-inline-block">Cylinders</h2>
                </div>
                <div class="col-md-4 col-sm-12 text-right">
                    <button class="btn btn-small btn-primary" data-toggle="modal" data-target="#addCylinderModal">Add New Cylinder</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped tm-table-striped-even mt-3">
                    <thead>
                        <tr class="tm-bg-gray">
                            <th scope="col">Cylinder #</th>
                            <th scope="col">Size</th>
                            <th scope="col">Location</th>
                            <th scope="col">Date Allocated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cylinders as $cylinder)
                            <tr onclick="window.location='{{ route('cylinders.show', $cylinder->id) }}'">
                                <td>{{ str_pad($cylinder->id, 9, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $cylinder->size }}</td>
                                <td>{{ $cylinder->location }}</td>
                                <td>{{ $cylinder->allocated_date->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Pagination Links --}}
        @if ($cylinders->hasPages())
        <div style="text-align: center; margin-top: 20px;">
        {{ $cylinders->links('pagination::bootstrap-4') }}
        </div>
        @endif
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
                            <option value="Small">5kg (Small)</option>
                            <option value="Medium">12kg (Medium)</option>
                            <option value="Large">25kg (Large)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="location">Warehouse Location</label>
                        <select class="form-control" id="location" name="location" required style="height: calc(4rem);"></select>
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
    $(document).ready(function () {
        // Fetch warehouse locations dynamically
        $.get("{{ route('locations.getWarehouses') }}", function (data) {
            $("#location").empty().append(data.map(name => `<option value="${name}">${name}</option>`));
        });

        $("#cylinderForm").submit(function (event) {
            event.preventDefault();
            $.ajax({
                url: "{{ route('cylinders.store') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function (response) {
                    alert("Cylinder added successfully!");
                    location.reload();
                },
                error: function (xhr) {
                    alert("Error adding cylinder.");
                }
            });
        });
    });
</script>
@endsection
