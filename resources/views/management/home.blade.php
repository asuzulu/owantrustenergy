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
                                    <a href="{{ route('management.orders.requests') }}" class="btn btn-primary">Customers'
                                        Requests</a>
                                    <a href="{{ route('orders.pickup') }}" class="btn btn-primary ms-3">Pick Ups</a>
                                    <a href="{{ route('management.deliveries') }}"
                                        class="btn btn-primary ms-3">Deliveries</a>
                                    <a href="{{ route('cylinders.unassigned') }}"
                                        class="btn btn-primary ms-3">Unassigned</a>
                                    <button class="btn btn-small btn-primary ms-3" data-toggle="modal"
                                        data-target="#addCylinderModal">
                                        Add Cylinder
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- FILTER FORM --}}
                <form id="filterForm" method="GET" action="{{ route('management.cylinders') }}">
                    <div class="row mt-4 mb-2">
                        <div class="col-md-4">
                            <label for="warehouseFilter"><strong>Location:</strong></label>
                            <select name="warehouse" id="warehouseFilter" class="form-control" style="height: 60px;"
                                onchange="this.form.submit()">
                                <option value="">All Cylinders</option>
                                @foreach ($warehouses as $warehouseOption)
                                    <option value="{{ $warehouseOption->name }}"
                                        {{ request('warehouse') === $warehouseOption->name ? 'selected' : '' }}>
                                        {{ $warehouseOption->name }}
                                    </option>
                                @endforeach
                                <option value="Customer" {{ request('warehouse') === 'Customer' ? 'selected' : '' }}>
                                    Customers
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="sizeFilter"><strong>Size:</strong></label>
                            <select name="size" id="sizeFilter" class="form-control" style="height: 60px;"
                                onchange="this.form.submit()">
                                <option value="">All Sizes</option>
                                <option value="Small" {{ request('size') === 'Small' ? 'selected' : '' }}>Small</option>
                                <option value="Medium" {{ request('size') === 'Medium' ? 'selected' : '' }}>Medium</option>
                                <option value="Large" {{ request('size') === 'Large' ? 'selected' : '' }}>Large</option>
                                <option value="Extra Large" {{ request('size') === 'Extra Large' ? 'selected' : '' }}>Extra
                                    Large</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="cylinderSearch"><strong>Search:</strong></label>
                            <input type="text" id="cylinderSearch" class="form-control" placeholder="Search cylinders..."
                                style="height: 60px;">
                        </div>
                    </div>
                </form>

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
                            @foreach ($cylinders as $cylinder)
                                <tr onclick="window.location='{{ route('management.cylinders.show', $cylinder->id) }}';" style="cursor: pointer;">
                                    <td>{{ str_pad($cylinder->id, 9, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $cylinder->size }}</td>
                                    <td>
                                        @switch($cylinder->size)
                                            @case('Small')
                                                3 kg
                                            @break
                                            @case('Medium')
                                                5 kg
                                            @break
                                            @case('Large')
                                                12 kg
                                            @break
                                            @case('Extra Large')
                                                25 kg
                                            @break
                                            @default
                                                N/A
                                        @endswitch
                                    </td>
                                    <td>{{ $cylinder->location }}</td>
                                    <td>{{ $cylinder->allocated_date->format('d-m-Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if ($cylinders->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $cylinders->appends(request()->only(['warehouse', 'size']))->links('pagination::bootstrap-4') }}
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
                        @csrf
                        <div class="form-group">
                            <label for="size">Cylinder Size</label>
                            <select class="form-control" id="size" name="size" required style="height: 60px;">
                                <option value="Small">3kg (Small)</option>
                                <option value="Medium">5kg (Medium)</option>
                                <option value="Large">12kg (Large)</option>
                                <option value="Extra Large">25kg (XL)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="location">Warehouse Location</label>
                            <select class="form-control" id="location" name="location" required
                                style="height: 60px;"></select>
                        </div>

                        <div class="form-group">
                            <label for="amount">How many?</label>
                            <input type="number" min="1" step="1" id="amount" name="amount"
                                class="form-control" required placeholder="Enter quantity">
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
        let allCylinders = @json($cylinders->items());
    </script>

<script>
    $(document).ready(function() {
        // Handle filtering by size and warehouse location
        let filteredCylinders = allCylinders;

        function filterCylinders() {
            let warehouseFilter = $('#warehouseFilter').val();
            let sizeFilter = $('#sizeFilter').val();
            let searchQuery = $('#cylinderSearch').val().toLowerCase();

            filteredCylinders = allCylinders.filter(function(cylinder) {
                let matchesWarehouse = warehouseFilter ? cylinder.location.includes(warehouseFilter) : true;
                let matchesSize = sizeFilter ? cylinder.size === sizeFilter : true;
                let matchesSearch = searchQuery ? (
                    cylinder.id.toString().includes(searchQuery) ||
                    cylinder.size.toLowerCase().includes(searchQuery) ||
                    cylinder.location.toLowerCase().includes(searchQuery)
                ) : true;

                return matchesWarehouse && matchesSize && matchesSearch;
            });

            renderTable(filteredCylinders);
        }

        // Render filtered cylinders in the table
        function renderTable(cylinders) {
            let tableBody = $('table tbody');
            tableBody.empty();

            let startIndex = 0;
            let perPage = 10;
            let paginated = cylinders.slice(startIndex, startIndex + perPage);

            paginated.forEach(function(cylinder) {
                tableBody.append(`
                    <tr onclick="window.location='{{ route('management.cylinders.show', ['id' => $cylinder->id]) }}';" style="cursor: pointer;">
                        <td>${String(cylinder.id).padStart(9, '0')}</td>
                        <td>${cylinder.size}</td>
                        <td>${getCylinderWeight(cylinder.size)}</td>
                        <td>${cylinder.location}</td>
                        <td>${new Date(cylinder.allocated_date).toLocaleDateString()}</td>
                    </tr>
                `);
            });

            // Update pagination (simplified pagination for now)
            updatePagination(cylinders);
        }

        function getCylinderWeight(size) {
            switch (size) {
                case 'Small': return '3 kg';
                case 'Medium': return '5 kg';
                case 'Large': return '12 kg';
                case 'Extra Large': return '25 kg';
                default: return 'N/A';
            }
        }

        function updatePagination(cylinders) {
            // Handle pagination logic here (just a basic example)
            let totalPages = Math.ceil(cylinders.length / 10);
            // Update your pagination UI accordingly
            console.log(`Total Pages: ${totalPages}`);
        }

        // Event listeners for filters
        $('#warehouseFilter, #sizeFilter, #cylinderSearch').on('change keyup', function() {
            filterCylinders();
        });

        // Initialize the table with the full list
        filterCylinders();
    });
</script>
@endsection
