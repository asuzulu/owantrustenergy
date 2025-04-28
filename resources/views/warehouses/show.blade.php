@extends(auth()->check() && auth()->user()->position === 'Manager' ? 'layouts.management-dashboard' : (auth()->check() && auth()->user()->position === 'Employee' ? 'layouts.employee-dashboard' : (auth()->check() && auth()->user()->position === 'Agent' ? 'layouts.agent-dashboard' : 'layouts.app')))

@php
    if (!auth()->check()) {
        header('Location: ' . url('/'));
        exit();
    }
@endphp

@section('content')
    <!-- Success Alert -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="global-success-alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Error Alert -->
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="global-error-alert">
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">
                <div class="row align-items-center">
                    <div class="col-md-8 col-sm-12">
                        <h2 class="tm-block-title d-inline-block">Warehouse Details</h2>
                    </div>
                    <div class="col-md-4 col-sm-12 text-right">
                        <div class="d-flex flex-column flex-sm-row justify-content-end align-items-center gap-2">
                            <a href="{{ route('warehouses.index') }}" class="btn btn-small btn-secondary">
                                Back to Warehouse List
                            </a>

                            @if (Auth::check() && Auth::user()->position === 'Manager')
                                <button class="btn btn-small btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#editModal">
                                    Edit
                                </button>
                                <button class="btn btn-small btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal">
                                    Delete
                                </button>
                            @endif
                        </div>
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
                                <th scope="row" class="col-md-4">Street</th>
                                <td class="col-md-8">{{ $warehouse->street }}</td>
                            </tr>
                            <tr>
                                <th scope="row" class="col-md-4">City</th>
                                <td class="col-md-8">{{ $warehouse->city }}</td>
                            </tr>
                            <tr>
                                <th scope="row" class="col-md-4">State</th>
                                <td class="col-md-8">{{ $warehouse->state }}</td>
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

    <!-- Edit Warehouse Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" aria-modal="true">
            <div class="modal-content">
                <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST" novalidate>
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
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $warehouse->name) }}" required>
                            <div class="invalid-feedback">Please enter a warehouse name.</div>
                        </div>
                        <div class="form-group">
                            <label for="street">Street</label>
                            <input type="text" class="form-control" id="street" name="street"
                                value="{{ old('street', $warehouse->street) }}" required pattern=".*[a-zA-Z]+.*"
                                title="Street address must contain at least one letter.">
                            <div class="invalid-feedback">Please enter the street address.</div>
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" class="form-control" id="city" name="city"
                                value="{{ old('city', $warehouse->city) }}" required>
                            <div class="invalid-feedback">Please enter the city.</div>
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" class="form-control" id="state" name="state"
                                value="{{ old('state', $warehouse->state) }}" required>
                            <div class="invalid-feedback">Please enter the state.</div>
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number"
                                maxlength="10" value="{{ old('phone_number', $warehouse->phone_number) }}" required
                                pattern="\d{10}">
                            <div class="invalid-feedback">Please enter a valid 10‑digit phone number.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Cancel">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Warehouse Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" aria-modal="true">
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
                        Are you sure you want to delete this warehouse? This action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Cancel">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Agent Cylinders Distribution Section -->
    <div class="row tm-content-row tm-mt-big">
        <div class="col-12">
            @include('partials.dashboard.agent-distribution')
        </div>
    </div>
@endsection

@section('content2')
    <!-- Cylinders Stored at this Warehouse (by size filter) -->
    <div class="row tm-content-row tm-mt-big">
        <div class="col-12">
            @include('partials.dashboard.warehouse-cylinders-table')
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Auto-close global alerts
        setTimeout(function() {
            $('#global-success-alert, #global-error-alert').fadeOut('slow');
        }, 5000);

        // Bootstrap form validation for modals
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByTagName('form');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Real-time phone number validation
        $(document).on('input', '#phone_number', function() {
            var val = $(this).val();
            if (!/^\d{0,10}$/.test(val)) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // AJAX-powered pagination for both tables
        function handlePaginationClicks(tableType) {
            $(document).on('click', `#${tableType}-pagination a`, function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                if (!url) return;

                $.ajax({
                    url: url,
                    data: {
                        table: tableType
                    },
                    success: function(response) {
                        if (tableType === 'agent') {
                            $('#agent-distribution-table-body').parent().html(response);
                        } else {
                            $('#warehouse-cylinders-table-body').parent().html(response.html);
                            $('#warehouse-cylinders-pagination').html($(response.html).find(
                                '#warehouse-cylinders-pagination').html());
                        }
                    },
                    error: function() {
                        alert('Could not fetch new data. Please try again.');
                    }
                });
            });
        }

        $(document).ready(function() {
            handlePaginationClicks('agent');
            handlePaginationClicks('warehouse');
        });
    </script>
@endpush
