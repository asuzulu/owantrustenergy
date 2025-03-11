@extends('layouts.user-dashboard')

@section('content')
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">
                <div class="row">
                    <div class="col">
                        <h3 class="tm-block-title text-center" style="font-weight: 1000;">
                            @auth
                                {{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}
                            @else
                                Guest
                            @endauth
                        </h3>
                        <h2 class="tm-block-title">Assigned Cylinders:</h2>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped tm-table-striped-even mt-3 table-fixed" style="table-layout: fixed; width: 100%;">
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
                                    <td class="tm-product-name">{{ $cylinder->id }}</td>
                                    <td class="text-center">{{ $cylinder->size }}</td>
                                    <td class="text-center">
                                        @if ($cylinder->size == 'Small')
                                            3kg
                                        @elseif ($cylinder->size == 'Medium')
                                            5kg
                                        @elseif ($cylinder->size == 'Large')
                                            12kg
                                        @elseif ($cylinder->size == 'XL')
                                            25kg
                                        @else
                                            N/A
                                        @endif
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
                        <table class="table table-hover table-striped tm-table-striped-even mt-3 table-fixed" style="table-layout: fixed; width: 100%;">
                            <thead>
                                <tr class="tm-bg-gray">
                                    <th scope="col" style="width: 15%;">Select</th>
                                    <th scope="col" style="width: 20%;">Order ID</th>
                                    <th scope="col" class="text-center" style="width: 20%;">Size</th>
                                    <th scope="col" class="text-center" style="width: 20%;">Weight</th>
                                    <th scope="col" class="text-center" style="width: 20%;">Order Type</th>
                                    <th scope="col" style="width: 25%;">Order Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td class="text-center"><input type="checkbox" class="order-checkbox" value="{{ $order->id }}"></td>
                                        <td class="tm-product-name">{{ $order->id }}</td>
                                        <td class="text-center">{{ $order->cylinder_size }}</td>
                                        <td class="text-center">{{ $order->weight }}</td>
                                        <td class="text-center">{{ ucfirst($order->order_type) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No orders found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-danger mt-3" id="delete-order-btn" disabled data-bs-toggle="modal" data-bs-target="#deleteOrderModal">Delete Order</button>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let checkboxes = document.querySelectorAll(".order-checkbox");
            let deleteBtn = document.getElementById("delete-order-btn");
            let confirmDeleteBtn = document.getElementById("confirm-delete");

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener("change", function() {
                    deleteBtn.disabled = !document.querySelector(".order-checkbox:checked");
                });
            });

            confirmDeleteBtn.addEventListener("click", function() {
                let selectedOrders = Array.from(document.querySelectorAll(".order-checkbox:checked"))
                                        .map(cb => cb.value);

                if (selectedOrders.length === 0) {
                    alert("Please select at least one order to delete.");
                    return;
                }

                fetch("{{ route('orders.delete') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ order_ids: selectedOrders })
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert("We apologize, but some orders have already been assigned.");
                        location.reload();
                    }
                }).catch(error => {
                    console.error("Error:", error);
                    alert("An error occurred while deleting the orders.");
                });
            });
        });
    </script>
@endsection
