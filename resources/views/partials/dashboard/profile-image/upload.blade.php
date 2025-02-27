<div class="custom-file mt-3 mb-3">
    <input id="fileInput" name="profile_image" type="file" accept="image/*" style="display:none;">
    <input type="button" class="btn btn-primary d-block mx-xl-auto" value="Upload JPG" onclick="document.getElementById('fileInput').click();">

    <!-- Cropping Area -->
    <div id="image-crop-container" class="mt-3" style="display: none;">
        <div id="croppie"></div>
        <button type="button" class="btn btn-success mt-2" id="cropImageBtn">Crop & Save</button>
    </div>

    <!-- Cropped Image Data -->
    <form id="croppedImageForm" action="{{ route('users.update-profile-image', ['id' => $user->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="cropped_image" id="croppedImage">
        <div class="text-center mt-3">
            <button type="submit" class="btn btn-success">Submit</button>
        </div>
    </form>
</div>
