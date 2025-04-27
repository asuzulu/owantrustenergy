<h2 class="tm-block-title">Profile Image of {{ $user->name }}</h2>

<div class="text-center">
    @if(!empty($user->profile_image) && file_exists(public_path('storage/profile-images/' . $user->profile_image)))
        <div class="profile-image-container mx-auto">
            <img
              src="{{ asset('storage/profile-images/' . $user->profile_image) }}"
              alt="Profile Image of {{ $user->name }}"
              class="profile-image"
            >
        </div>
    @else
        <p>No profile image available.</p>
    @endif

    <p class="mt-2"><strong>{{ $user->name }}</strong></p>
</div>
