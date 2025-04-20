@extends('layouts.user-dashboard')

@section('content')
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">
                <div class="row">
                    <div class="col">
                        <h3 class="tm-block-title text-center" style="font-weight: 1000;">
                            {{ $user->first_name . ' ' . $user->last_name }}
                        </h3>
                        <h2 class="tm-block-title">Assigned Cylinders:</h2>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped tm-table-striped-even mt-3 table-fixed"
                        style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr class="tm-bg-gray">
                                <th scope="col" style="width: 25%;">Cylinder #</th>
                                <th scope="col" class="text-center" style="width: 25%;">Size</th>
                                <th scope="col" class="text-center" style="width: 25%;">Weight</th>
                                <th scope="col" style="width: 25%;">Date Allocated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cylinders as $cylinder)
                                <tr>
                                    <td class="tm-product-name">{{ str_pad($cylinder->id, 9, '0', STR_PAD_LEFT) }}</td>
                                    <td class="text-center">{{ $cylinder->size }}</td>
                                    <td class="text-center">
                                        @switch($cylinder->size)
                                            @case('Small') 3kg @break
                                            @case('Medium') 5kg @break
                                            @case('Large') 12kg @break
                                            @case('Extra Large') 25kg @break
                                            @default N/A
                                        @endswitch
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($cylinder->allocated_date)->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No cylinders assigned.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Ordered Cylinders Section -->
                <div class="orders-section" style="margin-top: 2rem;">
                    <h2 class="tm-block-title">Ordered Cylinders:</h2>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped tm-table-striped-even mt-3 table-fixed"
                            style="table-layout: fixed; width: 100%;">
                            <thead>
                                <tr class="tm-bg-gray">
                                    <th scope="col" style="width: 10%;">Select</th>
                                    <th scope="col" style="width: 15%;">Order ID</th>
                                    <th scope="col" class="text-center" style="width: 20%;">Size</th>
                                    <th scope="col" class="text-center" style="width: 20%;">Weight</th>
                                    <th scope="col" class="text-center" style="width: 20%;">Order Type</th>
                                    <th scope="col" style="width: 15%;">Order Date</th>
                                    <th scope="col" style="width: 20%;">Retrieval</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="order-checkbox" value="{{ $order->id }}">
                                        </td>
                                        <td class="tm-product-name">{{ $order->id }}</td>
                                        <td class="text-center">{{ $order->cylinder_size }}</td>
                                        <td class="text-center">{{ $order->weight }}</td>
                                        <td class="text-center">{{ ucfirst($order->order_type) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d') }}</td>
                                        <td>{{ $order->retrieval }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No orders found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-danger mt-3" id="delete-order-btn" disabled data-bs-toggle="modal"
                        data-bs-target="#deleteOrderModal">Delete Order</button>
                </div>
                <!-- End Orders Section -->
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteOrderModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the selected orders?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Deletion script (same as before) -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let checkboxes = document.querySelectorAll(".order-checkbox");
            let deleteBtn = document.getElementById("delete-order-btn");
            let confirmDeleteBtn = document.getElementById("confirm-delete");

            checkboxes.forEach(cb => cb.addEventListener("change", () => {
                deleteBtn.disabled = !document.querySelector(".order-checkbox:checked");
            }));

            confirmDeleteBtn.addEventListener("click", () => {
                const ids = Array.from(document.querySelectorAll(".order-checkbox:checked"))
                                 .map(cb => cb.value);

                if (!ids.length) return alert("Please select at least one order.");

                fetch('/orders', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ order_ids: ids })
                })
                .then(r => r.json())
                .then(data => location.reload())
                .catch(() => alert("Error deleting orders."));
            });
        });
    </script>
@endsection
