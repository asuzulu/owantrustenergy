@extends('layouts.management-dashboard')

@section('content')
    <div class="container">
        <div class="row tm-content-row tm-mt-big justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="bg-white tm-block">
                    <h2 class="tm-block-title">Edit User Profile</h2>
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="{{ $user->first_name }}" required>
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="{{ $user->last_name }}" required>
                        </div>

                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ $user->phone_number }}" required>
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select class="form-control" id="gender" name="gender" required style="height: 60px;">
                                <option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ $user->gender == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="street">Street</label>
                            <input type="text" class="form-control" id="street" name="street" value="{{ $user->street }}">
                        </div>

                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="{{ $user->city }}">
                        </div>

                        <div class="form-group">
                            <label for="state">State</label>
                            <select class="form-control" id="state" name="state" required style="height: calc(4rem);">
                                @foreach($states as $state)
                                    <option value="{{ $state->name }}" {{ $user->state == $state->name ? 'selected' : '' }}>
                                        {{ $state->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>                                                                      

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                        </div>

                        <pre id="debug-output"></pre>


<script>
    document.querySelector('form').addEventListener('submit', function(event) {
        event.preventDefault(); // Stop actual submission for debugging
        let formData = new FormData(this);
        let debugOutput = '';

        formData.forEach((value, key) => {
            debugOutput += key + ': ' + value + '\n';
        });

        document.getElementById('debug-output').innerText = debugOutput;
        console.log(debugOutput);
    });
</script>


                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
