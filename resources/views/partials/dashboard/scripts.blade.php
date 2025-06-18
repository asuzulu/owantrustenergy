<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}"><script src="{{ asset('dashboard/js/moment.min.js') }}"></script>
<script src="{{ asset('dashboard/js/utils.js') }}"></script>
<script src="{{ asset('dashboard/js/Chart.min.js') }}"></script>
<script src="{{ asset('dashboard/js/fullcalendar.min.js') }}"></script>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.min.js"></script>

@if(isset($cylindersAssignedChart) && isset($customerRegistrationsChart))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let cylinderData = @json($cylindersAssignedChart);
            let customerData = @json($customerRegistrationsChart);

            let cylinderLabels = cylinderData.map(item => item.month || "Unknown");
            let cylinderCounts = cylinderData.map(item => item.count || 0);

            let customerLabels = customerData.map(item => item.month || "Unknown");
            let customerCounts = customerData.map(item => item.count || 0);

            if (cylinderData.length > 0) {
                drawLineChart("cylinders-chart", cylinderCounts, cylinderLabels, "Cylinders Assigned");
            }

            if (customerData.length > 0) {
                drawBarChart("customers-chart", customerCounts, customerLabels, "New Customers");
            }
        });

        function drawLineChart(canvasId, data, labels) {
            let canvas = document.getElementById(canvasId);
            if (!canvas) return console.error("Canvas element not found:", canvasId);

            new Chart(canvas.getContext("2d"), {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Cylinders Assigned",
                        data: data,
                        borderColor: "rgba(75, 192, 192, 1)",
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function drawBarChart(canvasId, data, labels) {
            let canvas = document.getElementById(canvasId);
            if (!canvas) return console.error("Canvas element not found:", canvasId);

            new Chart(canvas.getContext("2d"), {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "New Customers",
                        data: data,
                        backgroundColor: "rgba(54, 162, 235, 0.6)",
                        borderColor: "rgba(54, 162, 235, 1)",
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    </script>

    <script>
        $(document).ready(function () {
            let croppieInstance;

            $("#fileInput").on("change", function (event) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    $("#image-crop-container").show();
                    if (croppieInstance) {
                        croppieInstance.destroy();
                    }
                    croppieInstance = new Croppie(document.getElementById("croppie"), {
                        viewport: { width: 200, height: 200, type: "circle" },
                        boundary: { width: 300, height: 300 },
                        showZoomer: true
                    });
                    croppieInstance.bind({ url: e.target.result });
                };
                reader.readAsDataURL(event.target.files[0]);
            });

            $("#cropImageBtn").on("click", function () {
                croppieInstance.result({ type: "base64", size: "viewport" }).then(function (base64) {
                    $("#croppedImage").val(base64);
                    $("#submitCroppedImage").show();
                });
            });
        });
    </script>

@endif
