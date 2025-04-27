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
                        <div class="col-md-2 col-sm-12">
                            <h2 class="tm-block-title d-inline-block">Cylinders</h2>
                        </div>
                        @if (Auth::user()->position !== 'Agent')
                            <div class="col-md-10 col-sm-12">
                                <div class="d-flex justify-content-center align-items-center flex-wrap position-relative button-container">
                                    <a href="{{ route('management.orders.requests') }}" class="btn btn-primary mb-2 mx-2">
                                        Customer Requests
                                    </a>
                                    <a href="{{ route('cylinders.unassigned') }}" class="btn btn-primary mb-2 mx-2">
                                        Unassigned
                                    </a>
                                    <a href="{{ route('orders.pickup') }}" class="btn btn-primary mb-2 mx-2">
                                        Pick Ups
                                    </a>
                                    <a href="{{ route('management.deliveries') }}" class="btn btn-primary mb-2 mx-2">
                                        Deliveries
                                    </a>
                                    <a href="#" class="btn btn-primary mb-2 mx-2" data-toggle="modal" data-target="#addCylinderModal">
                                        Add Cylinder
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- FILTER + SEARCH FORM --}}
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
                                    <option value="Small" {{ request('size') === 'Small' ? 'selected' : '' }}>
                                        Small
                                    </option>
                                    <option value="Medium" {{ request('size') === 'Medium' ? 'selected' : '' }}>
                                        Medium
                                    </option>
                                    <option value="Large" {{ request('size') === 'Large' ? 'selected' : '' }}>
                                        Large
                                    </option>
                                    <option value="Extra Large" {{ request('size') === 'Extra Large' ? 'selected' : '' }}>
                                        Extra Large
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="cylinderSearch"><strong>Search:</strong></label>
                                <input type="text" name="search" id="cylinderSearch" class="form-control"
                                    value="{{ request('search') }}" placeholder="Search cylinders..." style="height: 60px;"
                                    onkeydown="if(event.key === 'Enter'){ this.form.submit(); }">
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
                                    @php
                                        // pad the numeric ID to 9 digits with leading zeroes
                                        $paddedId = str_pad($cylinder->id, 9, '0', STR_PAD_LEFT);
                                    @endphp
                                    <tr onclick="window.location='{{ route('management.cylinders.show', $paddedId) }}';"
                                        style="cursor: pointer;">
                                        <td>{{ $paddedId }}</td>
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
                        <form id="cylinderForm" method="POST" action="{{ route('cylinders.store') }}">
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
                                <select class="form-control" id="location" name="location" required style="height: 60px;">
                                    @foreach ($warehouses as $warehouse)
                                        @if ($warehouse->name !== 'Unassigned')
                                            <option value="{{ $warehouse->name }}">{{ $warehouse->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="amount">How many?</label>
                                <input type="number" min="1" step="1" id="amount" name="amount"
                                    class="form-control" required placeholder="Enter quantity">
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Add</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endsection
