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

        <div class="row tm-content-row tm-mt-big">
            <div class="tm-col tm-col-big">
                <div class="bg-white tm-block">
                    <h2 class="tm-block-title">Assign Cylinder</h2>
                    <p>Total Cylinders in Warehouse: {{ $warehouseCylinders->count() }}</p>

                    <p>
                        Cylinders Assigned to User:
                        {{ \App\Models\Cylinder::where('user_id', $user->id)->count() }}
                    </p>

                    @if (in_array(Auth::user()->position, ['Manager', 'Employee']) && $warehouseCylinders->count() > 0)
                        <form action="{{ route('management.assign-cylinder', $user->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="cylinder_id">Select Cylinder</label>
                                <select name="cylinder_id" id="cylinder_id" class="form-control" required>
                                    <option value="">Select Cylinder</option>
                                    @foreach ($warehouseCylinders as $cylinder)
                                        <option value="{{ str_pad($cylinder->id, 9, '0', STR_PAD_LEFT) }}">
                                            {{ str_pad($cylinder->id, 9, '0', STR_PAD_LEFT) }} - {{ $cylinder->size }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Assign</button>
                        </form>
                    @else
                        <p>No available cylinders to assign.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('partials.dashboard.scripts')

    <!-- Add Input Mask Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.7-beta.27/inputmask.min.js"></script>
    <script>
        $(document).ready(function() {
            // Apply input mask to cylinder_id select option
            $('#cylinder_id').on('change', function() {
                var selectedValue = $(this).val();
                if (selectedValue) {
                    var formattedId = selectedValue.padStart(9, '0');
                    $(this).val(formattedId);
                }
            });
        });
    </script>
@endsection

@section('styles')
    <style>
        /* Make select dropdown larger */
        #cylinder_id {
            height: 50px !important;   /* Make the select box taller */
            font-size: 16px !important; /* Increase the font size */
            padding: 10px !important;   /* Add padding */
            line-height: 1.5 !important; /* Increase line height for text spacing */
        }

        /* Style the options */
        #cylinder_id option {
            height: 50px;   /* Increase height of each option */
            padding: 10px 20px; /* Add padding inside each option */
        }
    </style>
@endsection
