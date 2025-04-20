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
                             <input type="file" name="nin_image" id="nin_image" accept="image/*"
                                 class="form-control">
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
                        $("#ninImagePreview").attr("src", response.preview_url + "?t=" + new Date().getTime());
                        $("#updateNinBtn").show();

                        if (update) {
                            $("#updateNinModal").modal("hide");
                        } else {
                            $("#uploadNinModal").modal("hide");
                        }

                        // Refreshes page after successful upload
                        location.reload();
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
