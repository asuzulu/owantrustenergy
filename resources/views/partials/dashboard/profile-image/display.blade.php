<div class="text-center">
    <h2 class="tm-block-title">{{ $user->first_name }} {{ $user->last_name }}</h2>

    @if(!empty($user->profile_image) && file_exists(public_path('storage/profile-images/' . $user->profile_image)))
        <img src="{{ asset('storage/profile-images/' . $user->profile_image) }}"
             alt="Profile Image of {{ $user->name }}"
             class="img-fluid rounded-circle"
             style="width: 200px; height: 200px; object-fit: cover;">
    @else
        <p>No profile image available.</p>
    @endif
</div>
