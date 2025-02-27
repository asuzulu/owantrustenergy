@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : 'layouts.drivers-dashboard')

@section('content')
    <div class="container">
        <div class="row tm-content-row tm-mt-big">
            <div class="tm-col tm-col-big">
                <div class="bg-white tm-block">
                    <div class="row">
                        <div class="col-24">
                            <h2 class="tm-block-title">Driver Account Details</h2>
                            <!-- Display user's details -->
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

        <!-- Display the cylinders assigned to the driver or the logged-in user -->
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block">
                <h3 class="tm-block-title" style="text-align: center">Cylinders Assigned</h3>
                @if($deliveries->isNotEmpty())
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cylinder #</th>
                                <th>Size</th>
                                <th>Customer</th>
                                <th>Delivery Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deliveries as $delivery)
                                <tr onclick="window.location='{{ route('cylinders.show', ['cylinder' => $delivery->cylinder]) }}';" style="cursor: pointer;">
                                    <td>{{ str_pad($delivery->cylinder, 9, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $delivery->size }}</td>
                                    <td>{{ $delivery->customer }}</td>
                                    <td>{{ \Carbon\Carbon::parse($delivery->delivery_date)->format('d-m-Y') ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $deliveries->links('pagination::bootstrap-4') }}
                @else
                    <p>No cylinders assigned to this driver.</p>
                @endif
                    <p style="text-align: center">You have been assigned a total of <strong>{{ $totalCylinders }}</strong> cylinder(s).</p>
                </div>
            </div>
        </div>
    </div>
@endsection
