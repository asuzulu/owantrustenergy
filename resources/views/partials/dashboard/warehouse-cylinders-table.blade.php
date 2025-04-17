<div class="bg-white tm-block">
    <h3 class="tm-block-title text-center">Cylinders in {{ $warehouse->name }}</h3>

    @if ($warehouseCylinders->isNotEmpty())
        {{-- Size Filter Form --}}
        <form id="sizeFilterForm" method="GET" action="{{ route('warehouses.show', $warehouse->id) }}">
            <input type="hidden" name="table" value="warehouse">
            <div class="form-group mt-3 mb-4 text-center">
                <label for="sizeFilter"><strong>Filter by Size:</strong></label>
                <select name="size" id="sizeFilter" class="form-control d-inline-block mx-auto"
                    style="width: 250px; height: 60px;" onchange="this.form.submit()">
                    <option value="" {{ request('size') == '' ? 'selected' : '' }}>All Sizes</option>
                    <option value="Small" {{ request('size') == 'Small' ? 'selected' : '' }}>Small</option>
                    <option value="Medium" {{ request('size') == 'Medium' ? 'selected' : '' }}>Medium</option>
                    <option value="Large" {{ request('size') == 'Large' ? 'selected' : '' }}>Large</option>
                    <option value="Extra Large" {{ request('size') == 'Extra Large' ? 'selected' : '' }}>Extra Large
                    </option>
                </select>
            </div>
        </form>

        <div class="table-responsive mt-3">
            <table class="table table-hover" id="cylinderTable">
                <thead>
                    <tr>
                        <th>Cylinder #</th>
                        <th>Size</th>
                        <th>Weight</th>
                        <th>Date Allocated</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($warehouseCylinders as $cyl)
                        <tr>
                            <td>{{ str_pad($cyl->id, 9, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $cyl->size }}</td>
                            <td>
                                @switch($cyl->size)
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
                            <td>{{ \Carbon\Carbon::parse($cyl->allocated_date)->format('d-m-Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($warehouseCylinders->hasPages())
            <div class="d-flex justify-content-center mt-3" id="warehouse-cylinders-pagination">
                {{ $warehouseCylinders->appends(['size' => request('size')])->links('pagination::bootstrap-4') }}
            </div>
        @endif
    @else
        <p class="text-center mt-4">No cylinders assigned to this warehouse.</p>
    @endif
</div>
