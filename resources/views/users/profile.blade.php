@if (Auth::user()->position === 'Customer')
    <script>
        window.location.href = "{{ route('dashboard.profile') }}";
    </script>
@else
    @extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' :
             (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' :
             (Auth::user()->position === 'Agent' ? 'layouts.agent-dashboard' : 'layouts.user-dashboard')))
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
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary mt-3">Edit Profile</a>
                            @if (Auth::user()->position === 'Manager' || !$user->photo_id)
                                <button type="button" class="btn btn-secondary mt-3" data-bs-toggle="modal"
                                    data-bs-target="#uploadNinModal">Upload NIN</button>
                            @endif
                            {{-- Distribute Cylinders button is only for Employee and Manager --}}
                            @if (in_array(Auth::user()->position, ['Employee', 'Manager']))
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

        @include('partials.dashboard.nin-modal')

        {{-- Show Distribute Cylinders modal only if user is not Customer (for Employee and Manager) --}}
        @if (in_array(Auth::user()->position, ['Employee', 'Manager']))
            @include('partials.dashboard.distribute-cylinders-modal', ['user' => $user])
        @endif

        {{-- Cylinders Distributed section shows for Employee, Manager, and Agent (not Customer) --}}
        @if (in_array(Auth::user()->position, ['Employee', 'Manager', 'Agent']))
            <div class="row tm-content-row tm-mt-big">
                <div class="bg-white tm-block">
                    <h3 class="tm-block-title" style="text-align: center">Cylinders Distributed</h3>
                    @php
                        $distributed = \Illuminate\Support\Facades\DB::table('agent_cylinders_distribution')
                            ->where('agent_id', $user->id)
                            ->orderBy('created_at', 'desc')
                            ->get();
                    @endphp
                    @if ($distributed->isNotEmpty())
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cylinder #</th>
                                    <th>Size</th>
                                    <th>Weight</th>
                                    <th>Warehouse</th>
                                    <th>Pick Up Date</th>
                                    @if (Auth::user()->position === 'Agent')
                                        <th>Passcode</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($distributed as $item)
                                    <tr style="cursor: pointer;">
                                        <td>{{ str_pad($item->cylinder_id, 9, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $item->cylinder_size }}</td>
                                        <td>{{ $item->cylinder_weight }}</td>
                                        <td>{{ $item->warehouse }}</td>
                                        <td>{{ $item->pick_up_date ? \Carbon\Carbon::parse($item->pick_up_date)->format('d-m-Y') : 'N/A' }}
                                        </td>
                                        @if (Auth::user()->position === 'Agent')
                                            <td>{{ $item->passcode }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p style="text-align: center">No cylinders distributed to this agent.</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endsection
