@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : 'layouts.employee-dashboard')

@section('content')
<div class="container" style="margin-top: -6rem;">
    <div class="row tm-content-row tm-mt-big">
        <div class="bg-white tm-block h-100">
            <div class="row">
                <div class="col-md-8 col-sm-12">
                    <h2 class="tm-block-title d-inline-block">Cylinder Details</h2>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped tm-table-striped-even mt-3">
                    <thead>
                        <tr class="tm-bg-gray">
                            <th scope="col">Field</th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Cylinder #</strong></td>
                            <td>{{ str_pad($cylinder->id, 9, '0', STR_PAD_LEFT) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Size</strong></td>
                            <td>{{ $cylinder->size }}</td>
                        </tr>
                        <tr>
                            <td><strong>Weight</strong></td>
                            <td>
                                @if($cylinder->size == 'Small')
                                    5 kg
                                @elseif($cylinder->size == 'Medium')
                                    12 kg
                                @elseif($cylinder->size == 'Large')
                                    25 kg
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Location</strong></td>
                            <td>{{ $cylinder->location }}</td>
                        </tr>
                        <tr>
                            <td><strong>Date Allocated</strong></td>
                            <td>{{ $cylinder->allocated_date ? $cylinder->allocated_date->format('Y-m-d') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Assigned User</strong></td>
                            <td>{{ $cylinder->user ? $cylinder->user->first_name . ' ' . $cylinder->user->last_name : 'Not Assigned' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="tm-table-mt tm-table-actions-row">
                <div class="tm-table-actions-col-left">
                    <a href="{{ Auth::user()->position === 'Manager' ? route('management.cylinders') : route('employee.cylinders') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
