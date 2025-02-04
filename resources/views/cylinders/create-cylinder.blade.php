@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : 'layouts.employee-dashboard')

@section('content')
<div class="container" style="margin-top: -6rem;">
    <div class="row tm-content-row tm-mt-big">
        <div class="bg-white tm-block h-100">
            <h2 class="tm-block-title d-inline-block">Add New Cylinder</h2>

            <form action="{{ route('cylinders.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="size">Cylinder Size</label>
                    <select id="size" name="size" class="form-control" required>
                        <option value="Small">5kg (small)</option>
                        <option value="Medium">12kg (medium)</option>
                        <option value="Large">25kg (large)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <select id="location" name="location" class="form-control" required>
                        @foreach ($locations as $location)
                            <option value="{{ $location->name }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('cylinders.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
