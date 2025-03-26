<div class="custom-file mt-3 mb-3">
    <form action="{{ route('users.update-profile-image', ['id' => $user->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input id="fileInput" name="profile_image" type="file" style="display:none;" />
        <input type="button" class="btn btn-primary d-block mx-xl-auto" value="Upload JPG" onclick="document.getElementById('fileInput').click();" />

        <!-- Add text-center class to center the submit button -->
        <div class="text-center mt-3">
            <button type="submit" class="btn btn-success">Submit</button>
        </div>
    </form>
</div>
