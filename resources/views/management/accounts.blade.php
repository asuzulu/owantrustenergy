@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : 'layouts.employee-dashboard')


@section('content')
    <div class="container">
        <div class="row tm-content-row tm-mt-big justify-content-center">
            <div class="col-12 col-lg-10"> <!-- Increased column width -->
                <div class="bg-white tm-block">
                    <div class="row">
                        <div class="col-12">
                            <h2 class="tm-block-title d-inline-block">Customer Accounts</h2>
                        </div>
                    </div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Email</th>
                                <th>Position</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Age</th> <!-- Age Column -->
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr onclick="window.location.href='{{ route('users.profile', $user->id) }}'" style="cursor: pointer;">
                                    <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                    <td>{{ $user->phone_number }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->position }}</td>
                                    <td>{{ $user->city }}</td>
                                    <td>{{ $user->state }}</td>
                                    <td>{{ $user->dob ? \Carbon\Carbon::parse($user->dob)->age : 'N/A' }}</td> <!-- Display Age -->
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('partials.dashboard.scripts')
@endsection
