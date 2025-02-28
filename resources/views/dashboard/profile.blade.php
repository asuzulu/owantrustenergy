@extends('layouts.user-dashboard')

@section('content')
    <div class="container">
        <div class="row tm-content-row tm-mt-big">
            <div class="tm-col tm-col-big">
                <div class="bg-white tm-block">
                    <div class="row">
                        <div class="col-24">
                            <h2 class="tm-block-title">My Account Details</h2>
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

    <div class="row tm-content-row tm-mt-big">
        <div class="bg-white tm-block">
            <h3 class="tm-block-title">Cylinders Assigned</h3>
            <p>You have been assigned a total of {{ $totalCylinders }} cylinder(s).</p>
        </div>
    </div>

    <!-- Upload NIN Modal -->
    <div class="modal fade" id="uploadNinModal" tabindex="-1" role="dialog" aria-labelledby="uploadNinModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadNinModalLabel">Upload NIN</h5>
                </div>
                <div class="modal-body text-center">
                    <!-- NIN Preview -->
                    <div id="ninPreviewContainer">
                        @if ($user->photo_id && Storage::disk('public')->exists('nin-images/' . $user->photo_id))
                            <img id="ninImagePreview" class="img-fluid rounded mb-3"
                                src="{{ asset('storage/nin-images/' . $user->photo_id) }}" alt="NIN Image">
                            <br>
                            <button id="updateNinBtn" class="btn btn-success mt-3" data-bs-toggle="modal"
                                data-bs-target="#updateNinModal">Change Image</button>
                        @else
                            <p class="text-danger">No NIN image available.</p>
                            <form id="ninUploadForm" action="{{ route('upload.nin', ['id' => $user->id]) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="nin_image" id="nin_image" accept="image/*" class="form-control">
                                <button type="submit" class="btn btn-primary mt-3">Upload</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Update NIN Modal -->
    <div class="modal fade" id="updateNinModal" tabindex="-1" role="dialog" aria-labelledby="updateNinModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateNinModalLabel">Update NIN Image</h5>
                </div>
                <div class="modal-body text-center">
                    <form id="updateNinForm" action="{{ route('upload.nin', ['id' => $user->id]) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="nin_image" id="update_nin_image" accept="image/*" class="form-control">
                        <p id="update-file-chosen">No file selected</p>
                        <button type="submit" class="btn btn-success mt-3">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle file name display for both upload and update inputs
            $("#nin_image, #update_nin_image").on("change", function() {
                let fileName = $(this).val().split("\\").pop();
                $(this).next("p").text(fileName);
            });

            // Function to handle AJAX upload
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
                        console.log("Upload success:", response);
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
                        console.error("Upload failed:", xhr.responseText);
                        alert("Upload failed. Please try again.");
                    }
                });
            }
            // Handle NIN image upload
            $("#ninUploadForm").on("submit", function(e) {
                e.preventDefault();
                handleNinUpload(this);
            });

            // Handle NIN image update
            $("#updateNinForm").on("submit", function(e) {
                e.preventDefault();
                handleNinUpload(this, true);
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
    @endsection
