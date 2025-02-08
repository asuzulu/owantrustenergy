@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : 'layouts.employee-dashboard')

@section('content')
    <div class="container">
        <div class="row tm-content-row tm-mt-big">
            <div class="tm-col tm-col-big">
                <div class="bg-white tm-block">
                    <div class="row">
                        <div class="col-24">
                            <h2 class="tm-block-title">Account Details</h2>
                            Name: {{ $user->first_name }} {{ $user->last_name }}
                            <br>
                            Email: {{ $user->email }}
                            <br>
                            Gender: {{ $user->gender }}
                            <br>
                            Phone: {{ $user->phone_number }}
                            <br>
                            Street: {{ $user->street }}
                            <br>
                            City: {{ $user->city }}
                            <br>
                            State: {{ $user->state }}
                            <br>
                            Age: {{ $user->dob ? \Carbon\Carbon::parse($user->dob)->age : 'N/A' }}
                            <br>
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary mt-3">Edit Profile</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tm-col tm-col-small">
                <div class="bg-white tm-block">
                    @include('partials.dashboard.profile-image.display')
                    @include('partials.dashboard.profile-image.upload')
                </div>
            </div>
        </div>

        <div class="row tm-content-row tm-mt-small justify-content-center align-items-center"">
            <div class="tm-col tm-col-big">
                <div class="bg-white tm-block text-center">
                    <h2 class="tm-block-title">Assign Cylinder</h2>
                    <p>Total Cylinders in Warehouse: {{ $warehouseCylinders->count() }}</p>
                    <p>
                        Cylinders Assigned to User:
                        {{ \App\Models\Cylinder::where('user_id', $user->id)->count() }}
                    </p>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#assignCylinderModal">Assign Cylinder</button>

                    <!-- Modal -->
                    <div class="modal fade" id="assignCylinderModal" tabindex="-1" role="dialog" aria-labelledby="assignCylinderModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="assignCylinderModalLabel">Assign Cylinder</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('management.assign-cylinder', $user->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="cylinder_id">Select Cylinder</label>
                                            <select name="cylinder_id" id="cylinder_id" class="form-control" required style="height: 4rem;">
                                                <option value="">Select Cylinder</option>
                                                @foreach ($warehouseCylinders as $cylinder)
                                                    <option value="{{ str_pad($cylinder->id, 9, '0', STR_PAD_LEFT) }}">
                                                        {{ str_pad($cylinder->id, 9, '0', STR_PAD_LEFT) }} - {{ $cylinder->size }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Assign</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('partials.dashboard.scripts')
@endsection

@section('styles')
    <style>
        /* Center everything in the Assign Cylinder section */
        .tm-content-row {
            height: auto; /* Reset the height to auto */
            margin-bottom: 50px; /* Optional, for spacing */
        }

        .text-center {
            text-align: center !important; /* Center the content */
        }

        /* Adjust modal dropdown to 4rem */
        .modal-body .form-control {
            height: 4rem !important; /* Make the dropdown 4rem */
            font-size: 1.25rem; /* Adjust font size inside dropdown */
        }
    </style>
@endsection
