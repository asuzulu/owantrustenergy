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
                <div class="bg-white tm-block">
                    @include('partials.dashboard.profile-image.display')
                    @include('partials.dashboard.profile-image.upload')
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
                    <h3 class="tm-block-title">Cylinder Ordered:</h3>
                    @if ($orders->isEmpty())
                        <p>No cylinders have been ordered yet.</p>
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Cylinder Size</th>
                                    <th>Weight</th>
                                    <th>Order Type</th>
                                    <th>Ordered At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->cylinder_size }}</td>
                                        <td>{{ $order->weight }}</td>
                                        <td>{{ ucfirst($order->order_type) }}</td>
                                        <td>{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
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
    <script>
        $(document).ready(function() {
            $("#nin_image, #update_nin_image").on("change", function() {
                let fileName = $(this).val().split("\\").pop();
                $(this).next("p").text(fileName || "No file selected");
            });

            function handleNinUpload(form, update = false) {
                let formData = new FormData(form);
                let actionUrl = $(form).attr("action");

                $.ajax({
                    url: actionUrl,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $("#ninImagePreview").attr("src", response.preview_url + "?t=" + new Date()
                                .getTime());
                            $("#updateNinBtn").show();

                            if (update) {
                                $("#updateNinModal").modal("hide");
                            } else {
                                $("#uploadNinModal").modal("hide");
                            }
                        }
                    },
                    error: function(xhr) {
                        alert("Upload failed. Please try again.");
                    }
                });
            }

            $("#ninUploadForm").on("submit", function(e) {
                e.preventDefault();
                handleNinUpload(this);
            });

            $("#updateNinForm").on("submit", function(e) {
                e.preventDefault();
                handleNinUpload(this, true);
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
