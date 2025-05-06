@if (Auth::user()->position === 'Customer')
    <script>
        window.location.href = "{{ route('dashboard.profile') }}";
    </script>
@else
    @extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : (Auth::user()->position === 'Agent' ? 'layouts.agent-dashboard' : 'layouts.user-dashboard')))
@endif

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
                            @if (!(Auth::user()->position === 'Employee' && $user->position === 'Employee'))
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary mt-3">Edit Profile</a>
                            @endif
                            @if (Auth::user()->position === 'Manager' || !$user->photo_id)
                                <button type="button" class="btn btn-secondary mt-3" data-bs-toggle="modal"
                                    data-bs-target="#uploadNinModal">Upload NIN</button>
                            @endif
                            {{-- Distribute Cylinders button is only visible to Manager and Employee if viewed user is Agent --}}
                            @if ($user->position === 'Agent' && in_array(Auth::user()->position, ['Manager', 'Employee']))
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
    </div>

    @include('partials.dashboard.nin-modal')

    <!-- Show Cylinders Assigned and ordered only if user has 'Customer' position -->
    <div class="container" style="margin-top: -4rem;">
        <div class="row tm-content-row tm-mt-big">
            @if ($user->position === 'Customer')
                <!-- Check if the user has 'Customer' position -->
                <a href="{{ route('users.cylinders', ['id' => $user->id]) }}" class="text-decoration-none text-dark">
                    <div class="bg-white tm-block" style="cursor: pointer;">
                        <h3 class="tm-block-title">Cylinders Assigned:</h3>
                        <p>You have been assigned a total of {{ $totalCylinders }} cylinder(s).</p>
                    </div>
                </a>
            @endsection
            @section('content2')
                <div class="row"></div>
                <a href="{{ route('users.cylinders', ['id' => $user->id]) }}" class="text-decoration-none text-dark">
                    <div class="row tm-content-row tm-mt-big">
                        <div class="bg-white tm-block" style="margin-top: -30px;">
                            <h3 class="tm-block-title">Cylinders Ordered (Summary):</h3>

                            @php
                                $sizeTotals = $orders->groupBy('cylinder_size')->map->count();
                            @endphp

                            @if ($sizeTotals->isEmpty())
                                <p>No cylinders have been ordered yet.</p>
                            @else
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Cylinder Size</th>
                                            <th>Total Ordered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sizeTotals as $size => $count)
                                            <tr>
                                                <td>{{ $size }}</td>
                                                <td>{{ $count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </a>
            @endif
            <!-- End the check for 'Customer' position -->
        </div>

        {{-- Show Distribute Cylinders modal only if user is not Customer (for Employee and Manager) --}}
        @if (in_array(Auth::user()->position, ['Employee', 'Manager']))
            @include('partials.dashboard.distribute-cylinders-modal', ['user' => $user])
        @endif

        {{-- Cylinders Distributed section shows for Employee, Manager, and Agent (not Customer) --}}
        @if (in_array(Auth::user()->position, ['Employee', 'Manager', 'Agent']))
            <div class="row tm-content-row tm-mt-big">
                <div class="bg-white tm-block">
                    <h3 class="tm-block-title text-center">Cylinders Distributed to Agent</h3>

                    @php
                        $distributed = \Illuminate\Support\Facades\DB::table('agent_cylinders_distribution')
                            ->where('agent_id', $user->id)
                            ->orderBy('created_at', 'desc')
                            ->get();
                    @endphp

                    @if ($distributed->isNotEmpty())
                        <form action="{{ route('agent.updatePickUpDate', $agent->id) }}" method="POST">
                            @csrf
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select_all"> Select All</th>
                                        <th>Cylinder #</th>
                                        <th>Size</th>
                                        <th>Weight</th>
                                        <th>Warehouse</th>
                                        <th>Date Picked Up</th>
                                        @if (Auth::user()->position === 'Agent')
                                            <th>Passcode</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($distributed as $item)
                                        @if ($item->pick_up_date === null)
                                            <tr style="cursor: pointer;"
                                                onclick="window.location='{{ route('management.cylinders.show', str_pad($item->cylinder_id, 9, '0', STR_PAD_LEFT)) }}'">
                                                <td>
                                                    <input type="checkbox" name="selected_cylinders[]"
                                                        value="{{ $item->cylinder_id }}">
                                                </td>
                                                <td>{{ str_pad($item->cylinder_id, 9, '0', STR_PAD_LEFT) }}</td>
                                                <td>{{ $item->cylinder_size }}</td>
                                                <td>{{ $item->cylinder_weight }}</td>
                                                <td>{{ $item->warehouse }}</td>
                                                <td>
                                                    {{ $item->pick_up_date ? \Carbon\Carbon::parse($item->pick_up_date)->format('d-m-Y') : 'N/A' }}
                                                </td>
                                                @if (Auth::user()->position === 'Agent')
                                                    <td>{{ $item->passcode }}</td>
                                                @endif
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>

                            @if (!in_array(Auth::user()->position, ['Agent', 'Customer']))
                                <div class="form-group row passcode-form">
                                    <label for="passcode" class="col-sm-3 col-form-label text-sm-right text-center">Enter
                                        Passcode:</label>
                                    <div class="col-sm-6 col-md-4 d-flex passcode-controls">
                                        <input type="text" name="passcode" id="passcode"
                                            class="form-control mr-2 passcode-input" required>
                                        <button type="submit" class="btn btn-primary">
                                            Update Pick-Up Date
                                        </button>
                                    </div>
                                </div>

                                <style>
                                    /* default (>1080px): left-align & baseline-align items */
                                    .passcode-form {
                                        justify-content: flex-start !important;
                                    }

                                    .passcode-form .passcode-controls {
                                        display: flex;
                                        flex-direction: row;
                                        align-items: baseline;
                                        /* align button to input’s text baseline */
                                        justify-content: flex-start;
                                    }

                                    .passcode-form .passcode-controls button {
                                        margin-top: 2px;
                                        /* nudge it up slightly */
                                    }

                                    .passcode-input {
                                        width: 250px;
                                    }

                                    /* ≤1080px: stack & center */
                                    @media (max-width: 1080px) {
                                        .passcode-form {
                                            justify-content: center !important;
                                        }

                                        .passcode-form .passcode-controls {
                                            flex-direction: column !important;
                                            align-items: center !important;
                                            justify-content: center !important;
                                        }

                                        .passcode-input {
                                            width: 100% !important;
                                            margin-bottom: 0.5rem;
                                            margin-right: 0 !important;
                                        }

                                        .passcode-form .passcode-controls button {
                                            margin-top: 0;
                                            /* reset for stacked view */
                                        }
                                    }
                                </style>
                            @endif
                        </form>
                    @else
                        <p class="text-center">No cylinders distributed to this agent.</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
