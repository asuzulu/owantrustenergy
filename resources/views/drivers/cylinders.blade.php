@extends('layouts.drivers-dashboard')

@section('content')
@if(Auth::user()->position !== 'Driver')
    <script>
        window.location.href = "{{ route('drivers') }}";
    </script>
    @php exit; @endphp
@endif

<div class="container" style="margin-top: -6rem;">
    <div class="row tm-content-row tm-mt-big">
        <div class="bg-white tm-block h-100">
            <div class="row">
                <div class="col-md-8 col-sm-12">
                    <h2 class="tm-block-title d-inline-block">Cylinders for Delivery</h2>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped tm-table-striped-even mt-3">
                    <thead>
                        <tr class="tm-bg-gray">
                            <th scope="col">Cylinder #</th>
                            <th scope="col">Size</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Delivery Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($deliveries as $delivery)
                            <tr onclick="window.location='{{ route('cylinders.show', $delivery->cylinder) }}'">
                                <td>{{ str_pad($delivery->cylinder, 9, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $delivery->size }}</td>
                                <td>{{ $delivery->customer }}</td>
                                <td>{{ \Carbon\Carbon::parse($delivery->delivery_date)->format('d-m-Y') ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No cylinders assigned for delivery.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination Links --}}
        @if ($deliveries->hasPages())
            <div style="text-align: center; margin-top: 20px;">
                {{ $deliveries->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection
