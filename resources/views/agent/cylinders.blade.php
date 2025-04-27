<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

@if (!Auth::check() || !in_array(Auth::user()->position, ['Employee', 'Manager', 'Agent']))
    <script>
        window.location.href = "{{ route('home') }}";
    </script>
    @php exit; @endphp
@endif

@extends(
    Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' :
    (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' :
    'layouts.agent-dashboard')
)

@section('content')
<div class="container" style="margin-top: -6rem;">
    <div class="row tm-content-row tm-mt-big">
        <div class="bg-white tm-block h-100">
            <div class="row">
                <div class="col-12">
                    <h2 class="tm-block-title text-center">
                        Cylinders Distributed to {{ $agent->first_name }} {{ $agent->last_name }}
                    </h2>
                </div>
            </div>

            {{-- FILTER + SEARCH FORM --}}
            <form id="filterForm"
                  method="GET"
                  action="{{ route('agent.cylinders') }}">
                <div class="row mt-4 mb-2">
                    <div class="col-md-6">
                        <label for="sizeFilter"><strong>Size:</strong></label>
                        <select name="size"
                                id="sizeFilter"
                                class="form-control"
                                style="height: 60px;"
                                onchange="this.form.submit()">
                            <option value="">All Sizes</option>
                            <option value="Small"      {{ request('size') === 'Small'      ? 'selected' : '' }}>Small</option>
                            <option value="Medium"     {{ request('size') === 'Medium'     ? 'selected' : '' }}>Medium</option>
                            <option value="Large"      {{ request('size') === 'Large'      ? 'selected' : '' }}>Large</option>
                            <option value="Extra Large"{{ request('size') === 'Extra Large'? 'selected' : '' }}>Extra Large</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="search"><strong>Search:</strong></label>
                        <input type="text"
                               name="search"
                               id="search"
                               class="form-control"
                               style="height: 60px;"
                               value="{{ request('search') }}"
                               placeholder="Search cylinder #..."
                               onkeydown="if(event.key==='Enter'){this.form.submit();}">
                    </div>
                </div>
            </form>

            {{-- TABLE --}}
            <div class="table-responsive mt-4">
                <table class="table table-hover table-striped tm-table-striped-even">
                    <thead>
                        <tr class="tm-bg-gray">
                            <th>Cylinder #</th>
                            <th>Size</th>
                            <th>Weight</th>
                            <th>Warehouse</th>
                            <th>Date Picked Up</th>
                            @if (Auth::user()->position === 'Agent')
                                <th>Passcode</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($distributed as $item)
                            <tr style="cursor: pointer;" onclick="
                                window.location='{{ route('management.cylinders.show',
                                   str_pad($item->cylinder_id, 9, '0', STR_PAD_LEFT)) }}'">
                                <td>{{ str_pad($item->cylinder_id, 9, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $item->cylinder_size }}</td>
                                <td>{{ $item->cylinder_weight }}</td>
                                <td>{{ $item->warehouse }}</td>
                                <td>
                                    {{ $item->pick_up_date
                                        ? \Carbon\Carbon::parse($item->pick_up_date)->format('d-m-Y')
                                        : 'N/A' }}
                                </td>
                                @if (Auth::user()->position === 'Agent')
                                    <td>{{ $item->passcode }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->position === 'Agent' ? 6 : 5 }}"
                                    class="text-center">
                                    No cylinders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($distributed->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $distributed->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
