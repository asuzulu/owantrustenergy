@if (!Auth::check() || Auth::user()->position === 'Customer')
    <script>
        window.location.href = "{{ route('home') }}";
    </script>
    @php exit; @endphp
@endif

@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : 'layouts.default-dashboard'))

@section('content')
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100 w-100">
                <div class="row">
                    <div class="col-md-8 col-sm-12">
                        <h2 class="tm-block-title d-inline-block">Unassigned Cylinders</h2>
                    </div>
                </div>
                <form action="{{ route('cylinders.assign') }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover table-striped tm-table-striped-even mt-3">
                            <thead>
                                <tr class="tm-bg-gray">
                                    <th scope="col">Cylinder ID</th>
                                    <th scope="col">Size</th>
                                    <th scope="col">Weight</th>
                                    <th scope="col">Warehouse</th>
                                    <th scope="col">Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cylinders as $cylinder)
                                    <tr>
                                        <td>{{ $cylinder->id }}</td>
                                        <td>{{ $cylinder->size }}</td>
                                        <td>
                                            @php
                                                $weights = [
                                                    'Small' => '3',
                                                    'Medium' => '5',
                                                    'Large' => '12',
                                                    'Extra Large' => '25',
                                                ];
                                                $displayWeight = $weights[$cylinder->size] ?? 'N/A';
                                            @endphp
                                            {{ $displayWeight }} kg
                                        </td>
                                                                                <td>
                                            <select name="warehouses[{{ $cylinder->id }}]" class="form-control"
                                                style="height: 65px;">
                                                @foreach ($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->name }}"
                                                        {{ $cylinder->location === $warehouse->name ? 'selected' : '' }}>
                                                        {{ $warehouse->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>{{ $cylinder->updated_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No unassigned cylinders found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $cylinders->links() }}
                        </div>
                        <div class="d-flex justify-content-center mt-4 gap-3">
                            <a href="{{ route('management.cylinders') }}" class="btn btn-secondary">Cylinders List</a>
                            <button type="submit" class="btn btn-primary">Assign</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ session('success') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ session('error') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if (session('success'))
        <script>
            $(document).ready(function() {
                $('#successModal').modal('show');
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            $(document).ready(function() {
                $('#errorModal').modal('show');
            });
        </script>
    @endif

    @if (session('success') || session('error'))
        <script>
            setTimeout(function() {
                $('#successModal').modal('hide');
                $('#errorModal').modal('hide');
            }, 4000); // Close after 4 seconds
        </script>
    @endif
@endpush
