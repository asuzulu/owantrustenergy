@if (Auth::user()->position === 'Customer')
    <script>
        window.location.href = "{{ route('dashboard') }}"; // Redirect to a safe page
    </script>
    @php exit; @endphp
@endif

@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : (Auth::user()->position === 'Agent' ? 'layouts.agent-dashboard' : 'layouts.default-dashboard')))

@section('content')
<div class="container">
    <h1>Global Settings</h1>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <form action="{{ route('manager.settings.update') }}" method="POST">
        @csrf
        <div class="form-group">
            @foreach($settings as $setting)
                <div class="form-check">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        id="{{ $setting->key }}"
                        name="{{ $setting->key }}"
                        {{ $setting->value ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $setting->key }}">
                        {{ $setting->label }}
                    </label>
                </div>
            @endforeach
        </div>
        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>
@endsection
