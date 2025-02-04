<h2 class="tm-block-title">Profile Image</h2>

<!-- Check if the user is a Manager -->
@if(Auth::check() && Auth::user()->position === 'Manager')
    <!-- If the user is a manager, display the most recent user's profile image -->
    @php
        // Sort users by the most recent profile image (assuming 'profile_image_updated_at' exists in the database)
        $mostRecentUser = $users->sortByDesc('profile_image_updated_at')->first();
    @endphp

    <div>
        <img src="{{ $mostRecentUser->profile_image ? Storage::url('user_images/' . $mostRecentUser->profile_image) : asset('img/profile-image.png') }}"
             alt="Profile Image of {{ $mostRecentUser->name }}" class="img-fluid">
        <p>{{ $mostRecentUser->name }}</p>
    </div>

@else
    <!-- If not a manager, show only the authenticated user's profile image -->
    <img src="{{ Auth::user()->profile_image ? Storage::url('user_images/' . Auth::user()->profile_image) : asset('img/profile-image.png') }}"
         alt="Profile Image" class="img-fluid">
@endif
