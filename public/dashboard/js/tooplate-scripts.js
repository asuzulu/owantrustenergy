document.addEventListener("DOMContentLoaded", function () {
  fetch("/statistics/data")
      .then(response => response.json())
      .then(data => {
          updateStats(data);
          renderCharts(data);
      })
      .catch(error => console.error("Error fetching statistics:", error));
});

function updateStats(data) {
  document.getElementById("cylinders-assigned").textContent = data.cylinders_assigned || "N/A";
  document.getElementById("cylinders-warehouses").textContent = data.cylinders_warehouses || "N/A";
  document.getElementById("total-cylinders").textContent = data.total_cylinders || "N/A";
  document.getElementById("total-customers").textContent = data.total_customers || "N/A";
  document.getElementById("new-customers-month").textContent = data.new_customers_month || "N/A";
  document.getElementById("new-customers-week").textContent = data.new_customers_week || "N/A";
  document.getElementById("new-customers-year").textContent = data.new_customers_year || "N/A";
  document.getElementById("new-customers-all").textContent = data.new_customers_all || "N/A";
  document.getElementById("total-warehouses").textContent = data.total_warehouses || "N/A";
}

let cylinderChart = null;
let customerChart = null;

function renderCharts(data) {
  const cylinderCanvas = document.getElementById("cylinders-chart");
  const customerCanvas = document.getElementById("customers-chart");

  // Set fixed height for chart containers
  cylinderCanvas.parentElement.style.height = "400px";
  customerCanvas.parentElement.style.height = "400px";

  const ctxCylinders = cylinderCanvas.getContext("2d");
  const ctxCustomers = customerCanvas.getContext("2d");

  // Destroy existing charts if they exist
  if (cylinderChart) cylinderChart.destroy();
  if (customerChart) customerChart.destroy();

  cylinderChart = new Chart(ctxCylinders, {
      type: "bar",
      data: {
          labels: data.cylinder_months || [],
          datasets: [{
              label: "Cylinders Assigned",
              data: data.cylinders_per_month || [],
              backgroundColor: "rgba(54, 162, 235, 0.6)",
              borderColor: "rgba(54, 162, 235, 1)",
              borderWidth: 1
          }]
      },
      options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
              y: { beginAtZero: true }
          }
      }
  });

  customerChart = new Chart(ctxCustomers, {
      type: "line",
      data: {
          labels: data.customer_months || [],
          datasets: [{
              label: "Customer Registrations",
              data: data.customers_per_month || [],
              backgroundColor: "rgba(255, 99, 132, 0.6)",
              borderColor: "rgba(255, 99, 132, 1)",
              borderWidth: 1,
              fill: true
          }]
      },
      options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
              y: { beginAtZero: true }
          }
      }
  });
}
