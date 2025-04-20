@extends('layouts.user-dashboard')

@section('content')
    @if (Auth::user()->position !== 'Customer')
        <script>
            window.location.href = "/";
        </script>
    @endif

    <div class="container">
        <div class="row tm-content-row">
            <div class="bg-white col-24 text-center"
                style="padding-left: 10px; padding-right: 10px; padding-top: 20px; margin-bottom: 15px; margin-top: -25px;">
                <h2 class="tm-block-title">Place an Order / Request a Refill</h2>
            </div>
        </div>
        <div class="row tm-content-row">
            <div class="bg-white tm-block p-4">
                <form id="orderForm" action="{{ route('order.place') }}" method="POST">
                    @csrf
                    <div class="form-group mt-4">
                        <label>
                            <h4>Select Cylinder Size and Type:</h4>
                        </label>
                        <div class="row text-center">
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
                            @endphp

                            @foreach ($cylinderSizes as $cylinder)
                                <div class="col-md-3">
                                    <div class="cylinder-option">
                                        <label for="{{ $cylinder['id'] }}">
                                            <div class="mt-2">
                                                <h5>{{ $cylinder['label'] }}</h5>
                                            </div>
                                            <div class="cylinder-image-placeholder">
                                                <img src="{{ asset($cylinder['image']) }}" alt="{{ $cylinder['label'] }}"
                                                    class="img-fluid" style="width: 50%; height: auto;">
                                            </div>
                                        </label>
                                        <div class="mt-2">
                                            <input type="radio" class="order-radio"
                                                name="order_type[{{ $cylinder['size'] }}]" value="new"
                                                data-size="{{ $cylinder['size'] }}" data-weight="{{ $cylinder['weight'] }}">
                                            New
                                            &nbsp; &nbsp;
                                            <input type="radio" class="order-radio"
                                                name="order_type[{{ $cylinder['size'] }}]" value="refill"
                                                data-size="{{ $cylinder['size'] }}"
                                                data-weight="{{ $cylinder['weight'] }}">
                                            Refill
                                        </div>
                                        <!-- Retrieval Dropdown for this cylinder -->
                                        <div class="mt-2">
                                            <select name="retrieval[{{ $cylinder['size'] }}]"
                                                class="retrieval-dropdown form-control" style="display: none; height: 4rem;"
                                                disabled>
                                                <option value="">Select Retrieval Option</option>
                                                <option value="delivery">Delivery</option>
                                                <option value="pick up">Pick up</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group mt-4 text-center">
                        <button type="button" class="btn btn-primary" onclick="validateAndConfirm()">Submit Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmOrderModal" tabindex="-1" role="dialog" aria-labelledby="confirmOrderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmOrderModalLabel">Confirm Order</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to place this order?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitOrder()">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@section('scripts')
    <script>
        $(document).ready(function() {
            $('input.order-radio').on('change', function() {
                // Uncheck all other radios
                $('input.order-radio').not(this).prop('checked', false);

                // Hide and reset all dropdowns
                $('.retrieval-dropdown').hide().val('').prop('disabled', true);

                // Show and enable the related dropdown
                var size = $(this).data('size');
                $('select[name="retrieval[' + size + ']"]').show().prop('disabled', false);
            });

            $('.close, .btn-secondary').on('click', function() {
                $('#confirmOrderModal').modal('hide');
            });
        });

        function validateAndConfirm() {
            let selected = document.querySelector('input.order-radio:checked');
            if (!selected) {
                alert("Please select an order type (New or Refill) for a cylinder.");
                return;
            }

            let size = selected.getAttribute('data-size');
            let retrieval = document.querySelector('select[name="retrieval[' + size + ']"]').value;
            if (!retrieval) {
                alert("Please select a retrieval option (Delivery or Pick up) for the selected cylinder.");
                return;
            }

            $('#confirmOrderModal').modal('show');
        }

        function submitOrder() {
            let firstName = "{{ Auth::user()->first_name }}";
            let lastName = "{{ Auth::user()->last_name }}";
            let selected = document.querySelector('input.order-radio:checked');
            let size = selected.getAttribute('data-size');
            let weight = selected.getAttribute('data-weight');
            let orderType = selected.value;
            let retrieval = document.querySelector('select[name="retrieval[' + size + ']"]').value;

            let postData = {
                first_name: firstName,
                last_name: lastName,
                cylinder_size: size,
                weight: weight,
                order_type: orderType,
                retrieval: retrieval,
                _token: $('input[name="_token"]').val()
            };

            console.log(postData);

            $.post("{{ route('order.place') }}", postData, function(response) {
                console.log(response);
                alert("Order placed successfully!");
                location.reload();
            }).fail(function(xhr, status, error) {
                console.error('Error:', error);
                alert("There was an error placing your order. Please try again.");
            });
        }
    </script>
@endsection
