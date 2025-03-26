@extends(
    Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' :
    (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' :
    (Auth::user()->position === 'Agent' ? 'layouts.agent-dashboard' : 'layouts.user-dashboard'))
)
?>
@section('content')
    <div class="container">
        <div class="row tm-content-row tm-mt-big">
            <div class="tm-col tm-col-big">
                <div class="bg-white tm-block">
                    <div class="row">
                        <div class="col-24">
                            <h2 class="tm-block-title">
                                @if ($user->position === 'Customer')
                                    Customer Account Details
                                @elseif ($user->position === 'Employee')
                                    Employee Account Details
                                @elseif ($user->position === 'Agent')
                                    Agent Account Details
                                @elseif ($user->position === 'Driver')
                                    Driver Account Details
                                @else
                                    Account Details
                                @endif
                            </h2>
                            Name: {{ $user->first_name }} {{ $user->last_name }}<br>
                            Email: {{ $user->email }}<br>
                            Gender: {{ $user->gender }}<br>
                            Phone: {{ $user->phone_number }}<br>
                            Street: {{ $user->street }}<br>
                            City: {{ $user->city }}<br>
                            State: {{ $user->state }}<br>
                            Age: {{ $user->dob ? \Carbon\Carbon::parse($user->dob)->age : 'N/A' }}<br>
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary mt-3">Edit Profile</a>
                            @if (Auth::user()->position === 'Manager' || !$user->photo_id)
                                <button type="button" class="btn btn-secondary mt-3" data-bs-toggle="modal"
                                    data-bs-target="#uploadNinModal">Upload NIN</button>
                            @endif
                            @if ($user->position === 'Agent')
                                <button type="button" class="btn btn-info mt-3" data-bs-toggle="modal"
                                    data-bs-target="#distributeCylindersModal">Distribute Cylinders</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="tm-col tm-col-small">
                <div class="bg-white tm-block">
                    @include('partials.dashboard.profile-image.display')
                </div>
            </div>
        </div>

        <!--div class="row tm-content-row tm-mt-small justify-content-center align-items-center">
                <div class="tm-col tm-col-big">
                    <div class="bg-white tm-block text-center">
                        <h2 class="tm-block-title">Assigned Cylinders</h2>
                        <p>Total Cylinders in Warehouse: {{ $warehouseCylinders->count() }}</p>
                        <p>Cylinders Assigned to User: {{ \App\Models\Cylinder::where('user_id', $user->id)->count() }}</p>
                    </div>
                </div>
            </!--div-->
    </div>

    @include('partials.dashboard.nin-modal')

    @if ($user->position === 'Agent')
        <!-- Distribute Cylinders Modal -->
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
                                            $totalForSize = \App\Models\Cylinder::where('size', $cylinder['size'])
                                                ->whereIn('location', $warehouses->pluck('name'))
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
                                                        $warehouseTotal = \App\Models\Cylinder::where(
                                                            'size',
                                                            $cylinder['size'],
                                                        )
                                                            ->where('location', $warehouse->name)
                                                            ->count();
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $warehouse->name }}</td>
                                                        <td>{{ $warehouseTotal }}</td>
                                                        <td>
                                                            <input type="number" min="0"
                                                                max="{{ $warehouseTotal }}"
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
    @endif
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#nin_image, #update_nin_image").on("change", function() {
                let fileName = $(this).val().split("\\").pop();
                $(this).next("p").text(fileName);
            });

            function handleNinUpload(form, update = false) {
                let formData = new FormData(form);
                let actionUrl = $(form).attr("action");

                $.ajax({
                    url: actionUrl,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $("#ninImagePreview").attr("src", response.preview_url + "?t=" + new Date()
                                .getTime());
                            $("#updateNinBtn").show();
                            if (update) {
                                $("#updateNinModal").modal("hide");
                            } else {
                                $("#uploadNinModal").modal("hide");
                            }
                        }
                    },
                    error: function() {
                        alert("Upload failed. Please try again.");
                    }
                });
            }

            $("#ninUploadForm").on("submit", function(e) {
                e.preventDefault();
                handleNinUpload(this);
            });

            $("#updateNinForm").on("submit", function(e) {
                e.preventDefault();
                handleNinUpload(this, true);
            });

            // Distribution inputs calculation
            $('.distribution-input').on('input', function() {
                let sizeId = $(this).data('size');
                let subtotal = 0;
                $('input.distribution-input[data-size="' + sizeId + '"]').each(function() {
                    let val = parseInt($(this).val());
                    if (!isNaN(val)) {
                        subtotal += val;
                    }
                });
                $('#subtotal-' + sizeId).text(subtotal);
                // Calculate grand total
                let grandTotal = 0;
                $('[id^="subtotal-"]').each(function() {
                    grandTotal += parseInt($(this).text()) || 0;
                });
                $('#grandTotal').text(grandTotal);
            });
        });
    </script>
@endsection
