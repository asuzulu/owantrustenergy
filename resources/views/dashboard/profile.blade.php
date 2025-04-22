@extends('layouts.user-dashboard')

@section('content')
    <div class="container" style="margin-top: -90px">
        <div class="row tm-content-row tm-mt-big">
            <div class="tm-col tm-col-big">
                <div class="bg-white tm-block">
                    <div class="row">
                        <div class="col-24">
                            <h2 class="tm-block-title">My Account Details</h2>
                            <p><strong>Name:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Gender:</strong> {{ ucfirst($user->gender) }}</p>
                            <p><strong>Phone:</strong> {{ $user->phone_number }}</p>
                            <p><strong>Street:</strong> {{ $user->street }}</p>
                            <p><strong>City:</strong> {{ $user->city }}</p>
                            <p><strong>State:</strong> {{ $user->state }}</p>
                            <p><strong>Age:</strong> {{ $user->dob ? \Carbon\Carbon::parse($user->dob)->age : 'N/A' }}</p>
                            @if (!$user->photo_id || !Storage::disk('public')->exists('nin-images/' . $user->photo_id))
                                <button type="button" class="btn btn-secondary mt-3" data-bs-toggle="modal"
                                    data-bs-target="#uploadNinModal">Upload NIN</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="tm-col tm-col-small">
                <div class="bg-white tm-block"
                    style="display: flex; flex-wrap: wrap; justify-content: center; gap: 0 10px !important; padding-left: 5px !important; padding-right: 5px !important;">
                    <div style="flex-shrink: 1 !important; max-width: fit-content !important;">
                        @include('partials.dashboard.profile-image.display')
                    </div>
                    <div style="flex-shrink: 1 !important; max-width: fit-content !important;">
                        @include('partials.dashboard.profile-image.upload')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="column">
        <a href="{{ route('dashboard.cylinder', ['userId' => auth()->id()]) }}" class="text-decoration-none text-dark">
            <div class="bg-white tm-block" style="margin-top: -10px; cursor: pointer;">
                <h3 class="tm-block-title">Cylinders Assigned:</h3>
                <p>You have been assigned a total of {{ $totalCylinders }} cylinder(s).</p>
            </div>
        </a>

        <!-- Add spacing here -->
        <div class="row"></div>

        <a href="{{ route('dashboard.cylinder', ['userId' => auth()->id()]) }}" class="text-decoration-none text-dark">
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
    </div>

    @include('partials.dashboard.nin-modal')
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
