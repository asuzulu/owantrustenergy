@extends('layouts.app')

@section('title', 'Pick Up Orders')

@section('content')
<div class="container mt-5">
    <h2>Pick Up Orders</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Location</th>
                <th>Cylinder</th>
                <th>Size</th>
                <th>Date Assigned</th>
                <th>Pick Up Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pickups as $pickup)
                <tr>
                    <td>{{ $pickup->customer }}</td>
                    <td>{{ $pickup->location }}</td>
                    <td>{{ $pickup->cylinder }}</td>
                    <td>{{ $pickup->size }}</td>
                    <td>{{ $pickup->date_assigned }}</td>
                    <td>{{ $pickup->pick_up_date }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No pick-up orders available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
