<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin | User Stats</title>

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

            <div class="d-flex justify-content-between">
                <h4>User Stats Dashboard</h4>

                <div class="d-flex justify-content-end mb-3">
                    <select id="rangeSelect" class="form-select w-100">
                        <option value="7">Last 7 days</option>
                        <option value="30" selected>Last 30 days</option>
                        <option value="90">Last 90 days</option>
                        <option value="365">Last 365 days</option>
                    </select>
                </div>
            </div>

                <!-- Top Summary Boxes -->
                <div class="row g-3 mb-4 ">

                    <div class="col-md-3">
                        <div class="card text-white mb-3" style="max-width: 24rem;  background-color: #6f418e;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Total Users</span>
                            </div>
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="d-flex">
                                    <div class="me-4">
                                        <small>ACTIVE</small><br>
                                        <h4 class="mb-0" id="total">0</h4>
                                    </div>
                                </div>
                                <i class="fa-solid fa-users fa-2x opacity-25"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-white mb-3" style="max-width: 24rem; background-color: #c7639f;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Role Ratios</span>
                            </div>
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="d-flex">
                                    <div class="me-4">
                                        <small>BUYERS</small><br>
                                        <h4 class="mb-0" id="orders">0</h4>
                                    </div>
                                    <div>
                                        <small>SELLERS</small><br>
                                        <h4 class="mb-0" id="units">0</h4>
                                    </div>
                                </div>
                                <i class="fa-solid fa-scale-balanced fa-2x opacity-25"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-white mb-3" style="max-width: 24rem; background-color: #f35779;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Rejected Sellers </span>
                            </div>
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="d-flex">
                                    <div class="me-4">
                                        <small></small><br>
                                        <h4 class="mb-0" id="aov">0</h4>
                                    </div>
                                </div>
                                <i class="fa-regular fa-eye-slash fa-2x opacity-25"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-white mb-3" style="max-width: 24rem; background-color: #f9963a;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Inactive Buyers</span>
                            </div>
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="d-flex">
                                    <div class="me-4">
                                        <small></small><br>
                                        <h4 class="mb-0" id="inactive">0</h4>
                                    </div>
                                </div>
                                <i class="fa-solid fa-user-minus fa-2x opacity-25"></i>
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
                                <h5 class="card-title">Users</h5>
                                <p class="text-muted small">Chart for user data across <em id="dateRange">0</em> days
                                </p>
                                <!-- Placeholder for Chart -->
                                <canvas id="usersChart" height="200px"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm ">
                            <div class="card-body mb-0">
                                <h5 class="card-title">User summary</h5>

                                <table class="table table-bordered table-sm table-hover mb-0">
                                    <tbody>
                                        <tr class="text-nowrap">
                                            <td>Buyers</td>
                                            <td>Total</td>
                                            <td><strong id="tBuyers">0</strong></td>
                                        </tr>
                                        <tr class="text-nowrap">
                                            <td></td>
                                            <td>Active</td>
                                            <td><strong id="activeBuyers">0</strong></td>
                                        </tr>
                                        <tr class="text-nowrap">
                                            <td></td>
                                            <td>Inactive</td>
                                            <td><strong id="inactiveBuyers">0</strong></td>
                                        </tr>
                                        
                                        <tr class="text-nowrap">
                                            <td>Sellers</td>
                                            <td>Total</td>
                                            <td><strong id="tSellers">0</strong></td>
                                        </tr>
                                        <tr class="text-nowrap">
                                            <td></td>
                                            <td>Active</td>
                                            <td><strong id="activeSellers">0</strong></td>
                                        </tr>
                                        <tr class="text-nowrap">
                                            <td></td>
                                            <td>Inactive</td>
                                            <td><strong id="inactiveSellers">0</strong></td>
                                        </tr>
                                        <tr class="text-nowrap">
                                            <td></td>
                                            <td>Rejected</td>
                                            <td><strong id="rejectedSellers">0</strong></td>
                                        </tr>
                                         <tr class="text-nowrap">
                                            <td></td>
                                            <td>Pending</td>
                                            <td><strong id="pendingSellers">0</strong></td>
                                        </tr>
                                        <tr class="text-nowrap">
                                            <td>Total</td>
                                            <td></td>
                                            <td><strong id="totalUsers">0</strong></td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-5 mb-5">
                    
                <div class="col-md-4">
                    <div class="card shadow-sm ">
                            <div class="card-body mb-4">
                    <canvas id="userTypePie"></canvas>
                </div></div></div>
                <div class="col-md-4">
                    <div class="card shadow-sm ">
                            <div class="card-body mb-4">
                    <canvas id="sellerStatusBar" height="300px"></canvas>
                </div></div></div>
                <div class="col-md-4">
                    <div class="card shadow-sm ">
                            <div class="card-body mb-4">
                    <canvas id="buyerStatusBar" height="300px"></canvas>
                </div></div></div>
                </div>


            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const ctx = document.getElementById('usersChart').getContext('2d');
        let usersChart;

        // Initial load
        document.addEventListener("DOMContentLoaded", () => {
            loadChart(parseInt(document.getElementById("rangeSelect").value));
        });

        // On dropdown change
        document.getElementById("rangeSelect").addEventListener("change", function () {
            const days = parseInt(this.value);
            loadChart(days);
        });

        // Fetch data and update chart
        function loadChart(days) {
            fetch(`fetch_userstats.php?range=${days}`)
                .then(response => response.json())
                .then(data => {
                    updateChart(data.labels, data.users, data.buyers, data.sellers, days);
                });
        }
        

        function updateChart(labels, users, buyers, sellers, days) {
            document.getElementById("dateRange").textContent = days;

            const data = {
                labels: labels,
                datasets: [
                    {
                        label: 'Users',
                        data: users,
                        borderColor: '#6f418e',
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: 'Buyers',
                        data: buyers,
                        borderColor: '#f35779',
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: 'Sellers',
                        data: sellers,
                        borderColor: '#f9963a',
                        fill: false,
                        tension: 0.3
                    }
                ]
            };

            const config = {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    scales: {
                        x: { title: { display: true, text: 'Date' } },
                        y: { title: { display: true, text: 'Registrations' }, beginAtZero: true }
                    }
                }
            };

            // Destroy existing chart before creating new
            if (usersChart) {
                usersChart.destroy();
            }

            usersChart = new Chart(ctx, config);
        }

        function loadSummaryStats() {
        fetch('fetch_userstats.php')
            .then(res => res.json())
            .then(data => {
                document.getElementById("total").textContent = data.total_users;
                document.getElementById("orders").textContent = data.total_buyers +'%';
                document.getElementById("units").textContent = data.total_sellers +'%';
                document.getElementById("aov").textContent = data.rejected_sellers;
                document.getElementById("inactive").textContent = data.inactive_buyers;
                document.getElementById('tBuyers').textContent = data.stats.t_buyers ;
                document.getElementById('activeBuyers').textContent = data.stats.t_buyers- data.inactive_buyers;
                document.getElementById('tSellers').textContent = data.stats.t_sellers;
                document.getElementById('inactiveBuyers').textContent = data.inactive_buyers;
                document.getElementById('activeSellers').textContent = data.stats.active_sellers;
                document.getElementById('inactiveSellers').textContent = data.stats.inactive_sellers;
                document.getElementById('rejectedSellers').textContent = data.rejected_sellers;
                document.getElementById('pendingSellers').textContent = data.stats.pending_sellers;
                document.getElementById('totalUsers').textContent = data.stats.t_users;


                // PIE CHART: Buyer vs Seller Ratio
new Chart(document.getElementById('userTypePie'), {
    type: 'doughnut',
    data: {
        labels: ['Buyers', 'Sellers'],
        datasets: [{
            data: [data.total_buyers, data.total_sellers],
            backgroundColor: ['#36A2EB', '#FF6384']
        }]
    },
    options: {
        plugins: {
            title: {
                display: true,
                text: 'User Distribution (Buyers vs Sellers)'
            }
        }
    }
});

// BAR CHART: Seller Status
new Chart(document.getElementById('sellerStatusBar'), {
    type: 'bar',
    data: {
        labels: ['Active', 'Inactive', 'Rejected', 'Pending'],
        datasets: [{
            label: 'Sellers',
            data: [
                data.stats.active_sellers,
                data.stats.inactive_sellers,
                data.rejected_sellers,
                data.stats.pending_sellers
            ],
            backgroundColor: ['#4CAF50', '#FFC107', '#F44336', '#03A9F4']
        }]
    },
    options: {
        plugins: {
            title: {
                display: true,
                text: 'Seller Status Breakdown'
            }
        },
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// BAR CHART: Buyer Status
new Chart(document.getElementById('buyerStatusBar'), {
    type: 'bar',
    data: {
        labels: ['Active Buyers', 'Inactive Buyers'],
        datasets: [{
            label: 'Buyers',
            data: [
                data.stats.t_buyers- data.inactive_buyers,
                data.inactive_buyers
            ],
            backgroundColor: ['#4CAF50', '#FFC107']
        }]
    },
    options: {
        plugins: {
            title: {
                display: true,
                text: 'Buyer Status Breakdown'
            }
            
    

        },
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

            })
            .catch(err => console.error("Failed to load summary stats:", err));
    }

    window.addEventListener('DOMContentLoaded', loadSummaryStats);



    
    </script>

</body>

</html>