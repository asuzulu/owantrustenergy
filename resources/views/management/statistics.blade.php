@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : 'layouts.employee-dashboard')

@section('content')
<div class="container-fluid">
    <!-- Statistics Boxes -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="bg-white tm-block text-center p-4">
                <h2 class="tm-block-title">Cylinders Assigned</h2>
                <p class="tm-value display-4">{{ $cylinders_assigned }}</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="bg-white tm-block text-center p-4">
                <h2 class="tm-block-title">Cylinders in Warehouses</h2>
                <p class="tm-value display-4">{{ $cylinders_in_warehouses }}</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="bg-white tm-block text-center p-4">
                <h2 class="tm-block-title">Total Cylinders</h2>
                <p class="tm-value display-4">{{ $total_cylinders }}</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="bg-white tm-block text-center p-4">
                <h2 class="tm-block-title">Total Customers</h2>
                <p class="tm-value display-4">{{ $total_customers }}</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="bg-white tm-block text-center p-4">
                <h2 class="tm-block-title">New Customers Last Month</h2>
                <p class="tm-value display-4">{{ $customers_last_month }}</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="bg-white tm-block text-center p-4">
                <h2 class="tm-block-title">New Customers Last Week</h2>
                <p class="tm-value display-4">{{ $customers_last_week }}</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="bg-white tm-block text-center p-4">
                <h2 class="tm-block-title">New Customers Last Year</h2>
                <p class="tm-value display-4">{{ $customers_last_year }}</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="bg-white tm-block text-center p-4">
                <h2 class="tm-block-title">Total Employees</h2>
                <p class="tm-value display-4">{{ $total_employees }}</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="bg-white tm-block text-center p-4">
                <h2 class="tm-block-title">Total Warehouses</h2>
                <p class="tm-value display-4">{{ $total_warehouses }}</p>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="bg-white tm-block p-4">
                <h2 class="tm-block-title">Cylinders Assigned Over Time</h2>
                <canvas id="cylinderChart" class="chart-canvas"></canvas>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="bg-white tm-block p-4">
                <h2 class="tm-block-title">Customer Registrations Over Time</h2>
                <canvas id="customerChart" class="chart-canvas"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart Data -->
<script>
    let cylinderData = {!! json_encode($cylinders_assigned_chart) !!};
    let customerData = {!! json_encode($customer_registration_chart) !!};
</script>

<!-- Chart Styling -->
<style>
    .tm-block {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    .tm-value {
        font-weight: bold;
        color: #333;
    }
    .chart-canvas {
        width: 100%;
        height: 350px;
    }
</style>
@endsection
