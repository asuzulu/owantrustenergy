@extends('layouts.user-dashboard')

@section('content')
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">
                <div class="row">
                    <div class="col-md-8 col-sm-12">
                        <h2 class="tm-block-title d-inline-block">
                            Assigned Cylinders for
                            @auth
                                {{ Auth::user()->first_name . ' ' . Auth::user()->last_name }}
                            @else
                                Guest
                            @endauth
                        </h2>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped tm-table-striped-even mt-3">
                        <thead>
                            <tr class="tm-bg-gray">
                                <th scope="col" style="width: 10%;">Cylinder #</th>
                                <th scope="col" class="text-center">Size</th>
                                <th scope="col" class="text-center" style="width: 30%;">Weight</th>
                                <th scope="col">Date Allocated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cylinders as $cylinder)
                                <tr>
                                    <td class="tm-product-name">{{ $cylinder->id }}</td>
                                    <td class="text-center">{{ $cylinder->size }}</td>
                                    <td class="text-center">
                                        @if ($cylinder->size == 'Small')
                                            5kg
                                        @elseif ($cylinder->size == 'Medium')
                                            12kg
                                        @elseif ($cylinder->size == 'Large')
                                            25kg
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($cylinder->allocated_date)->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No cylinders assigned.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($cylinders->count())
                    <div class="cylinders-list">
                        @foreach ($cylinders as $cylinder)
                            <!-- Display each cylinder -->
                            <p>{{ $cylinder->name }}</p>
                        @endforeach
                    </div>

                    <!-- Pagination links -->
                    {{ $cylinders->links() }}
                @else
                    <p>No cylinders found.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
