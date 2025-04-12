<div class="modal fade" id="distributeCylindersModal" tabindex="-1" role="dialog"
    aria-labelledby="distributeCylindersModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width:80%;">
        <div class="modal-content">
            <form id="cylinderDistributionForm" action="{{ route('cylinders.distribute', ['id' => $user->id]) }}"
                method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="distributeCylindersModalLabel">Distribute Cylinders</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @php
                        use Illuminate\Support\Facades\DB;

                        $cylinderSizes = [
                            [
                                'id' => 'small',
                                'size' => 'small',
                                'weight' => '3kg',
                                'label' => 'Small (3kg)',
                                'image' => 'dashboard/img/3kg.jpg',
                            ],
                            [
                                'id' => 'medium',
                                'size' => 'medium',
                                'weight' => '5kg',
                                'label' => 'Medium (5kg)',
                                'image' => 'dashboard/img/5kg.jpg',
                            ],
                            [
                                'id' => 'large',
                                'size' => 'large',
                                'weight' => '12kg',
                                'label' => 'Large (12kg)',
                                'image' => 'dashboard/img/12kg.jpg',
                            ],
                            [
                                'id' => 'xl',
                                'size' => 'extra large',
                                'weight' => '25kg',
                                'label' => 'XL (25kg)',
                                'image' => 'dashboard/img/25kg.jpg',
                            ],
                        ];

                        $warehouses = \App\Models\Warehouse::all();
                    @endphp

                    @foreach ($cylinderSizes as $cylinder)
                        <div class="row mb-4 border p-3">
                            <div class="col-md-3 text-center">
                                <h5>{{ $cylinder['label'] }}</h5>
                                <img src="{{ asset($cylinder['image']) }}" alt="{{ $cylinder['label'] }}"
                                    class="img-fluid" style="width: 80%; height: auto;">
                                @php
                                    // Get all warehouse names and convert them to lowercase
                                    $warehouseNames = $warehouses->pluck('name')->toArray();
                                    $warehouseNamesLower = array_map('strtolower', $warehouseNames);
                                    // Count total cylinders per size in all warehouses (case-insensitive)
                                    $totalForSize = DB::table('cylinders')
                                        ->whereRaw('LOWER(size) = ?', [strtolower($cylinder['size'])])
                                        ->whereIn(DB::raw('LOWER(TRIM(location))'), $warehouseNamesLower)
                                        ->count();
                                @endphp
                                <p class="mt-2">Total in Warehouses: <span
                                        id="total-{{ $cylinder['id'] }}">{{ $totalForSize }}</span></p>
                            </div>
                            <div class="col-md-9">
                                <h6>Distribution per Warehouse</h6>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Warehouse</th>
                                            <th>Available Cylinders</th>
                                            <th>Assign Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($warehouses as $warehouse)
                                            @php
                                                $warehouseTotal = DB::table('cylinders')
                                                    ->whereRaw('LOWER(size) = ?', [strtolower($cylinder['size'])])
                                                    ->whereRaw('LOWER(TRIM(location)) = ?', [strtolower($warehouse->name)])
                                                    ->count();
                                            @endphp
                                            <tr>
                                                <td>{{ $warehouse->name }}</td>
                                                <td>
                                                    <span class="available-count" data-original="{{ $warehouseTotal }}">
                                                        {{ $warehouseTotal }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <input type="number" min="0" max="{{ $warehouseTotal }}"
                                                        name="distribution[{{ $cylinder['size'] }}][{{ $warehouse->id }}]"
                                                        class="form-control distribution-input"
                                                        data-size="{{ $cylinder['id'] }}"
                                                        data-warehouse-max="{{ $warehouseTotal }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="text-end">
                                    <strong>Subtotal for {{ $cylinder['label'] }}: </strong>
                                    <span id="subtotal-{{ $cylinder['id'] }}">0</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="row">
                        <div class="col-12 text-center">
                            <h4>Total Cylinders to be Distributed: <span id="grandTotal">0</span></h4>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Distribute</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Success Modal -->
@if(session('success'))
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
     <div class="modal-content">
         <div class="modal-header">
             <h5 class="modal-title" id="successModalLabel">Success</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
             {{ session('success') }}
         </div>
         <div class="modal-footer">
             <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
         </div>
     </div>
  </div>
</div>
<script>
    $(document).ready(function(){
         $('#successModal').modal('show');
    });
</script>
@endif
<!-- Error Modal -->
@if(session('error'))
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
     <div class="modal-content">
         <div class="modal-header">
             <h5 class="modal-title" id="errorModalLabel">Error</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
             {{ session('error') }}
         </div>
         <div class="modal-footer">
             <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
         </div>
     </div>
  </div>
</div>
@endif

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function(){
        @if(session('success'))
            $('#successModal').modal('show');
        @endif

        @if(session('error'))
            $('#errorModal').modal('show');
        @endif

        // Distribution inputs calculation
        $('.distribution-input').on('input', function() {
            let max = parseInt($(this).attr('max'));
            let currentVal = parseInt($(this).val()) || 0;
            if (currentVal > max) {
                $(this).val(max);
                currentVal = max;
            }

            let sizeId = $(this).data('size');
            let subtotal = 0;
            $('input.distribution-input[data-size="' + sizeId + '"]').each(function() {
                let val = parseInt($(this).val()) || 0;
                subtotal += val;
                let original = parseInt($(this).data('warehouse-max'));
                let remaining = Math.max(0, original - val);
                $(this).closest('tr').find('.available-count').text(remaining);
            });
            $('#subtotal-' + sizeId).text(subtotal);

            let grandTotal = 0;
            $('[id^="subtotal-"]').each(function() {
                grandTotal += parseInt($(this).text()) || 0;
            });
            $('#grandTotal').text(grandTotal);
        });
    });
</script>
@endpush
