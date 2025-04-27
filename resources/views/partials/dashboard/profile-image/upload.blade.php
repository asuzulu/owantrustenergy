<div class="custom-file mt-3 mb-3">
    <form id="profileImageForm" action="{{ route('users.update-profile-image', ['id' => $user->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <!-- Hidden field for existing image -->
        <input type="hidden" name="current_profile_image" value="{{ $user->profile_image }}">

        <!-- Hidden file input -->
        <input id="fileInput" name="profile_image" type="file" accept="image/*" style="display:none;" onchange="previewSelectedImage(event)" />

        <!-- Button to trigger file input -->
        <input type="button" class="btn btn-primary d-block mx-xl-auto" value="Select JPG" onclick="document.getElementById('fileInput').click();" />
    </form>
</div>

<!-- Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="previewModalLabel">Preview Selected Image</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body text-center">
        <img id="imagePreview" src="" alt="Selected Image" style="max-width: 100%; border-radius: 10px;" />
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="submitProfileImage()">Save</button>
      </div>

    </div>
  </div>
</div>

<!-- Corrected Script -->
<script>
let previewModal = null;

document.addEventListener('DOMContentLoaded', function () {
    previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
});

function previewSelectedImage(event) {
    const file = event.target.files[0];
    const previewImage = document.getElementById('imagePreview');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewModal.show(); // Only show the existing modal instance
        }
        reader.readAsDataURL(file);
    }
}

// Submit form when Save is clicked
function submitProfileImage() {
    document.getElementById('profileImageForm').submit();
}
</script>
