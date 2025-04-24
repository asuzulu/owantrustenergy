// resources/views/management/statistics.blade.php
@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : 'layouts.employee-dashboard')

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            @php
                $stats = [
                    ['id' => 'cylinders-assigned', 'title' => 'Cylinders Assigned', 'value' => $cylinders_assigned],
                    [
                        'id' => 'cylinders-warehouses',
                        'title' => 'Cylinders in Warehouses',
                        'value' => $cylinders_in_warehouses,
                    ],
                    ['id' => 'total-cylinders', 'title' => 'Total Cylinders', 'value' => $total_cylinders],
                    ['id' => 'total-customers', 'title' => 'Total Customers', 'value' => $total_customers],
                    [
                        'id' => 'new-customers-this-week',
                        'title' => 'New Customers This Week',
                        'value' => $new_customers_this_week,
                    ],
                    [
                        'id' => 'new-customers-week',
                        'title' => 'New Customers Last Week',
                        'value' => $customers_last_week,
                    ],
                    [
                        'id' => 'new-customers-month',
                        'title' => 'New Customers Last Month',
                        'value' => $customers_last_month,
                    ],
                    [
                        'id' => 'new-customers-year',
                        'title' => 'New Customers Last Year',
                        'value' => $customers_last_year,
                    ],
                    [
                        'id' => 'customers-assigned',
                        'title' => 'Customers Assigned Cylinders',
                        'value' => $customers_assigned,
                    ],
                    [
                        'id' => 'customers-unassigned',
                        'title' => 'Customers Without Cylinders',
                        'value' => $customers_unassigned,
                    ],
                    [
                        'id' => 'order-requests-pending',
                        'title' => 'Customer Order Requests Pending',
                        'value' => $customer_orders_pending,
                    ],
                    [
                        'id' => 'cylinders-distributed-agents',
                        'title' => 'Cylinders Distributed to Agents',
                        'value' => $cylinders_distributed_agents,
                    ],
                    [
                        'id' => 'agents-with-distribution',
                        'title' => 'Agents with Distributed Cylinders',
                        'value' => $agents_with_distribution,
                    ],
                    ['id' => 'deliveries-pending', 'title' => 'Deliveries Pending', 'value' => $deliveries_pending],
                    [
                        'id' => 'pickup-orders-pending',
                        'title' => 'Pickup Orders Pending',
                        'value' => $pickup_orders_pending,
                    ],
                    ['id' => 'total-drivers', 'title' => 'Total Drivers', 'value' => $total_drivers],
                    [
                        'id' => 'drivers-with-deliveries',
                        'title' => 'Drivers with Deliveries',
                        'value' => $drivers_with_deliveries,
                    ],
                    ['id' => 'total-employees', 'title' => 'Total Employees', 'value' => $total_employees],
                    ['id' => 'total-warehouses', 'title' => 'Total Warehouses', 'value' => $total_warehouses],
                ];
            @endphp

            @foreach ($stats as $stat)
                <div class="col-lg-4 col-md-6">
                    <div
                        class="bg-white tm-block text-center p-4 d-flex flex-column justify-content-between shadow-sm rounded">
                        <h4 class="tm-block-title">{{ $stat['title'] }}</h4>
                        <p id="{{ $stat['id'] }}" class="tm-value display-5 text-primary fw-bold">{{ $stat['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row mt-4">
            <div class="col-lg-6">
                <div class="bg-white tm-block p-4 shadow-sm rounded">
                    <h4 class="tm-block-title">Cylinders Assigned Over Time</h4>
                    <canvas id="cylinders-chart" class="chart-canvas"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bg-white tm-block p-4 shadow-sm rounded">
                    <h4 class="tm-block-title">Customer Registrations Over Time</h4>
                    <canvas id="customers-chart" class="chart-canvas"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function drawLineChart(chartId, data, labels, label) {
            var ctx = document.getElementById(chartId).getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        borderColor: 'blue',
                        backgroundColor: 'rgba(0, 0, 255, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function drawBarChart(chartId, data, labels, label) {
            var ctx = document.getElementById(chartId).getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        borderColor: 'blue',
                        backgroundColor: 'rgba(0, 0, 255, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            let cylinderData = {!! json_encode($cylindersAssignedChart) !!};
            let customerData = {!! json_encode($customerRegistrationsChart) !!};

            let cylinderLabels = cylinderData.map(item => item.month || "Unknown");
            let cylinderCounts = cylinderData.map(item => item.count || 0);

            let customerLabels = customerData.map(item => item.month || "Unknown");
            let customerCounts = customerData.map(item => item.count || 0);

            drawLineChart("cylinders-chart", cylinderCounts, cylinderLabels, "Cylinders Assigned");
            drawBarChart("customers-chart", customerCounts, customerLabels, "Customer Registrations");
        });
    </script>

    <style>
        .tm-block {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }

        .tm-value {
            font-weight: bold;
            color: #333;
            font-size: 30px;
        }

        .chart-canvas {
            width: 100%;
            max-height: 350px;
        }
    </style>
@endsection
