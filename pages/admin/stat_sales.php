<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require '../db.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sales Statistics</title>

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!--Stylesheets-->
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/styleaccount.css">

</head>

<body>
    <!-- Navigation -->
    <?php include 'admin_nav.php'; ?>

    <div class="d-flex">
        <!-- Sidebar with links -->
        <?php include 'sidebar.php'; ?>

        <!-- Content Area for Dynamic Content -->
        <div class="content-wrapper">

            <div id="dynamic-content" class="mt-4">
                <div class="mb-4 text-end"><a class="text-primary" href="dashboard.php">Back to Dashboard</a></div>

                <div class="d-flex justify-content-between mb-3">

                    <h4>Sales and Orders Stats Dashboard</h4>
                    <select id="rangeSelect" class="form-select w-25">
                        <option value="7">Last 7 days</option>
                        <option value="30" selected>Last 30 days</option>
                        <option value="90">Last 90 days</option>
                        <option value="365">Last 365 days</option>
                    </select>
                </div>

                <!-- Top Summary Boxes -->


                    <div class="row g-3 mb-3">

                        <div class="col-md-3">
                            <div class="card text-white mb-3" style="max-width: 24rem;  background-color: #6f418e;">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Sales Amount</span>
                                </div>
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div class="d-flex">
                                        <div class="me-4">
                                            <small>GROSS SALES</small><br>
                                            <h4 class="mb-0" id="total">0</h4>
                                        </div>
                                    </div>
                                    <i class="fa-sharp fa-regular fa-money-bill-1 fa-2x opacity-25"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card text-white mb-3" style="max-width: 24rem; background-color: #c7639f;">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Sales Volumes</span>
                                </div>
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div class="d-flex">
                                        <div class="me-4">
                                            <small>ORDERS</small><br>
                                            <h4 class="mb-0" id="orders">0</h4>
                                        </div>
                                        <div>
                                            <small>UNITS</small><br>
                                            <h4 class="mb-0" id="units">0</h4>
                                        </div>
                                    </div>
                                    <i class="fas fa-shopping-cart fa-2x opacity-25"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card text-white mb-3" style="max-width: 24rem; background-color: #f35779;">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Average Order Value </span>
                                </div>
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div class="d-flex">
                                        <div class="me-4">
                                            <small>AOV</small><br>
                                            <h4 class="mb-0" id="aov">0</h4>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-basket-shopping fa-2x opacity-25"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card text-white mb-3" style="max-width: 24rem; background-color: #f9963a;">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Best Sales Period</span>
                                </div>
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div class="d-flex">
                                        <div class="me-4">
                                            <small>Day</small><br>
                                            <h5 class="mb-0" id="day">0</h5>
                                        </div>
                                        <div class="me-4">
                                            <small>Time</small><br>
                                            <h5 class="mb-0" id="time">0</h5>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-clock fa-2x opacity-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Main Dashboard Cards -->
                    <div class="row g-4">

                        <!-- Sales Chart Card -->
                        <div class="col-md-8">
                            <div class="card card-min">
                                <div class="card-body">
                                    <h5 class="card-title">Sales</h5>
                                    <p class="text-muted small">Chart for sales data across <em id="dateRange">0</em>
                                        days</p>
                                    <!-- Placeholder for Chart -->
                                    <canvas id="salesChart" height="150"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card shadow-sm ">
                                <div class="card-body mb-0">
                                    <h5 class="card-title">Sales summary</h5>

                                    <table class="table table-bordered table-sm table-hover mb-0">
                                        <tbody>
                                            <tr class="text-nowrap">
                                                <td>Charges</td>
                                                <td>Count</td>
                                                <td><strong id="chargesCount">0</strong></td>
                                                <td></td>
                                            </tr>
                                            <tr class="text-nowrap">
                                                <td></td>
                                                <td>Gross</td>
                                                <td><br></td>
                                                <td><strong id="chargesGross">0</strong></td>
                                            </tr>
                                            <tr class="text-nowrap">
                                                <td>Refunds</td>
                                                <td>Count</td>
                                                <td><strong id="refundsCount">0</strong></td>
                                                <td></td>
                                            </tr>
                                            <tr class="text-nowrap">
                                                <td></td>
                                                <td>Gross</td>
                                                <td></td>
                                                <td><strong id="refundsGross">0</strong></td>
                                            </tr>
                                            <tr class="text-nowrap">
                                                <td>Balance</td>
                                                <td></td>
                                                <td><strong id="balanceCount">0</strong></td>
                                                <td><strong id="balanceGross">0</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>

                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

                    <script>
                        let salesChartInstance;

                        document.addEventListener('DOMContentLoaded', function () {
                            const rangeSelect = document.getElementById('rangeSelect');

                            // Currency formatter
                            const formatCurrency = amount => {
                                const num = parseFloat(amount);
                                return 'R' + (isNaN(num) ? '0.00' : num.toFixed(2));
                            };

                            function loadOrdersByRange() {
                                const dateRange = rangeSelect.value;
                                console.log("Loading orders for the last " + dateRange + " days");

                                // Fetch sales data
                                fetch(`fetch_salesstats.php?range=${dateRange}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.error) {
                                            console.error("Error from PHP:", data.error);
                                            return;
                                        }

                                        const gross = parseFloat(data.gross);

                                        // Ensure chart has at least two points
                                        if (data.labels.length < 2) {
                                            data.labels.unshift('Previous Day');
                                            data.sales.unshift(0);
                                        }

                                        const ctx = document.getElementById('salesChart').getContext('2d');

                                        // Destroy previous chart
                                        if (salesChartInstance) {
                                            salesChartInstance.destroy();
                                        }

                                        // Create chart
                                        salesChartInstance = new Chart(ctx, {
                                            type: 'line',
                                            data: {
                                                labels: data.labels.map(dateStr => {
                                                    const date = new Date(dateStr);
                                                    return date.toLocaleDateString('en-ZA', { month: 'short', day: '2-digit' });
                                                }),
                                                datasets: [{
                                                    label: 'Sales (R)',
                                                    data: data.sales,
                                                    borderColor: 'rgb(255, 145, 0)',
                                                    backgroundColor: 'rgba(255, 145, 0, 0.4)',
                                                    fill: true,
                                                    tension: 0.3
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                plugins: {
                                                    legend: {
                                                        display: true,
                                                        position: 'top'
                                                    }
                                                },
                                                scales: {
                                                    y: {
                                                        beginAtZero: true
                                                    }
                                                }
                                            }
                                        });

                                        // Update dashboard
                                        document.getElementById('dateRange').textContent = dateRange;
                                        document.getElementById('day').textContent = data.best_day;
                                        document.getElementById('time').textContent = data.best_time;
                                        document.getElementById('orders').textContent = data.orders;
                                        document.getElementById('units').textContent = data.units;
                                        document.getElementById('total').textContent = formatCurrency(data.gross);
                                        document.getElementById('aov').textContent = formatCurrency(data.aov);

                                        document.getElementById('chargesCount').textContent = data.charges.count;
                                        document.getElementById('chargesGross').textContent = formatCurrency(gross);
                                        document.getElementById('refundsCount').textContent = data.refunds.count;
                                        document.getElementById('refundsGross').textContent = 'R' + data.refunds.total.toFixed(2);
                                        document.getElementById('balanceCount').textContent = parseInt(data.charges.count) + parseInt(data.refunds.count);

                                        document.getElementById('balanceGross').textContent = 'R' + data.balance.toFixed(2);

                                    })
                                    .catch(error => {
                                        console.error("Sales fetch failed:", error);
                                    });
                            }

                            loadOrdersByRange();

                            rangeSelect.addEventListener('change', loadOrdersByRange);
                        });
                    </script>


</body>

</html>