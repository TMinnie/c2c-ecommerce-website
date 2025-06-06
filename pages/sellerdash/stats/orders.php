<?php
if (!isset($_SESSION['sellerID'])) {
    header('Location: ../login.php');
    exit;
}
?>

<div class="d-flex justify-content-end mb-3">
    <select id="rangeSelect2" class="form-select w-50 w-md-25">
        <option value="7">Last 7 days</option>
        <option value="30" selected>Last 30 days</option>
        <option value="90">Last 90 days</option>
        <option value="365">Last 365 days</option>
    </select>
</div>
<!-- Top Summary Boxes -->
<div class="row g-3 mb-4 ">

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card text-white mb-3" style="max-width: 24rem; background-color: #c7639f;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Order Volumes</span>
            </div>
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex">
                    <div class="me-4">
                        <small>ORDERS</small><br>
                        <h4 class="mb-0" id="order">0</h4>
                    </div>
                    <div>
                        <small>UNITS</small><br>
                        <h4 class="mb-0" id="unit">0</h4>
                    </div>
                </div>
                <i class="fas fa-shopping-cart fa-2x opacity-25"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card text-white mb-3" style="max-width: 24rem;  background-color: #6f418e;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Average Order Value </span>
                <i class="fas fa-ellipsis-v"></i>
            </div>
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex">
                    <div class="me-4">
                        <small>AOV</small><br>
                        <h4 class="mb-0" id="aov2">0</h4>
                    </div>
                </div>
                <i class="fa-solid fa-basket-shopping fa-2x opacity-25"></i>
            </div>
        </div>
    </div>


    <div class="col-12 col-sm-6 col-md-3">
        <div class="card text-white mb-3" style="max-width: 24rem; background-color: #f35779;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Total Customers</span>
            </div>
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex">
                    <div class="me-4">
                        <small>UNIQUE</small><br>
                        <h4 class="mb-0" id="unique">0</h4>
                    </div>
                    <div>
                        <small>REPEAT</small><br>
                        <h4 class="mb-0" id="repeat">0%</h4>
                    </div>
                </div>
                <i class="fa-solid fa-user-plus fa-2x opacity-25"></i>
            </div>

        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card text-white mb-3" style="max-width: 24rem; background-color: #f9963a;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Avg Fulfillment Time</span>
            </div>
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex">
                    <div class="me-4">
                        <small>DISPATCH</small><br>
                        <h5 class="mb-0" id="fulfillmentTime">0 hrs</h5>
                    </div>
                    <div class="me-4">
                        <small>DELIVER</small><br>
                        <h5 class="mb-0" id="deliverHours">0 hrs</h5>
                    </div>
                </div>
                <i class="fa-solid fa-truck-fast fa-2x opacity-25"></i>
            </div>
        </div>
    </div>


</div>

<div class="row g-4 mb-4 ">

    <!-- Orders Card -->
    <div class="col-md-6">
        <div class="card card-min">
            <div class="card-body">
                <h5 class="card-title">Orders Per Day</h5>
                <div class="mt-4">

                    <canvas id="ordersPerDayChart" height="200"></canvas>
                </div>


            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-min h-100">
            <div class="card-body">
                <h5 class="card-title mb-5">Order Status Breakdown</h5>

                    <!-- Chart Column -->
                        <div class="position-relative" style="width: 100%; height: auto;">
                            <canvas id="orderStatusChart" class="w-100 h-100"></canvas>
                        </div>
            </div> 
        </div> 
    </div> 

</div>

<div class="mt-5">
  <h4>Recent Orders</h4>

  <div class="table-responsive">
        <table id="orders-table" class="table table-light table-bordered table-hover mt-2">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Buyer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Units</th>
                </tr>
            </thead>
            <tbody>
                <!-- Populate dynamically -->
            </tbody>
        </table>
    </div>
    <button id="downloadOrdersCSV" class="btn btn-sm btn-outline-secondary mb-2 ">Download CSV</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script type="module">

    import { downloadCSV } from './sellerdash/js/downloadCSV.js';

    document.addEventListener('DOMContentLoaded', function () {

        //Download CSV functionality
        document.getElementById("downloadOrdersCSV").addEventListener("click", () => {
        const rows = document.querySelectorAll("#orders-table tbody tr");
        const data = [];

        rows.forEach(row => {
            const cols = row.querySelectorAll("td");
            if (cols.length === 6) {
            data.push({
                "Order ID": cols[0].textContent.trim(),
                "Buyer": cols[1].textContent.trim(),
                "Date": cols[2].textContent.trim(),
                "Total": cols[3].textContent.trim(), 
                "Status": cols[4].textContent.trim(),
                "Units": cols[5].textContent.trim()
            });
            }
        });

        const headers = ["Order ID", "Buyer", "Date", "Total", "Status", "Units"];
        downloadCSV(data, headers, "orders_export.csv");
        });

        // Currency formatter
        const formatCurrency = amount => {
            const num = parseFloat(amount);
            return 'R' + (isNaN(num) ? '0.00' : num.toFixed(2));
        };

        const rangeSelect = document.getElementById('rangeSelect2');
        if (!rangeSelect) {
            console.error("Element with id 'rangeSelect2' not found.");
            return;
        }

        function formatHoursToDays(hours) {
            const h = Math.floor(hours);
            const d = Math.floor(h / 24);
            const remainingH = h % 24;
            return (d > 0 ? `${d}d ` : '') + `${remainingH}h`;
        }

        function renderOrderStatusChart(statusCounts) {
            const ctx = document.getElementById('orderStatusChart').getContext('2d');

            // Destroy previous chart instance if it exists
            if (window.orderStatusChartInstance) {
                window.orderStatusChartInstance.destroy();
            }

            const labels = ['Pending', 'Paid', 'Accepted', 'Dispatched', 'Completed', 'Refunded', 'Cancelled'];
            const dataValues = [
                statusCounts.pending || 0,
                statusCounts.paid || 0,
                statusCounts.accepted || 0,
                statusCounts.dispatched || 0,
                statusCounts.completed || 0,
                statusCounts.refunded || 0,
                statusCounts.cancelled || 0,
            ];
            const backgroundColors = [
                '#6F418E',  // Pending 
                '#975592',  // Paid
                '#DB5D85',  // Accepted 
                '#F4626F',  // Dispatched 
                '#F5715E',  // Completed 
                '#F9963A',  // Refunded 
                '#FFFF11'   // Cancelled 
            ];

            window.orderStatusChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Order Status',
                        data: dataValues,
                        backgroundColor: backgroundColors,
                        borderWidth: 1,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                generateLabels: chart => {
                                    const { data } = chart;
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        return {
                                            text: `${label}: ${value}`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: '#fff',
                                            lineWidth: 1,
                                            hidden: chart.getDatasetMeta(0).data[i].hidden,
                                            index: i
                                        };
                                    });
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: context => `${context.label}: ${context.parsed} orders`
                            }
                        }
                    }
                }
            });
        }

        function renderOrdersPerDayChart(data) {
            const ctx = document.getElementById("ordersPerDayChart").getContext("2d");

            const labels = data.map(item => item.date);
            const values = data.map(item => item.count);

            // Destroy existing chart if it exists
            if (window.ordersPerDayChartInstance) {
                window.ordersPerDayChartInstance.destroy();
            }

            window.ordersPerDayChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels.map(dateStr => {
                        const date = new Date(dateStr);
                        return date.toLocaleDateString('en-ZA', { month: 'short', day: '2-digit' });
                    }),
                    datasets: [{
                        label: 'Orders',
                        data: values,
                        borderColor: 'rgb(255, 145, 0)',
                        backgroundColor: 'rgba(255, 145, 0, 0.4)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Orders'
                            }
                        }
                    }
                }
            });
        }

        function displayOrdersTable(ordersByStatus) {
            let allOrders = [];
            for (let status in ordersByStatus) {
                allOrders = allOrders.concat(ordersByStatus[status]);
            }

            const tbody = $('#orders-table tbody');
            tbody.empty();

            allOrders.forEach(order => {
                const fullName = `${order.uFirst} ${order.uLast}`;

                // Main order row with toggle
                const mainRow = `
                    <tr class="order-row" data-order-id="${order.orderID}">
                        <td>${order.orderID}</td>
                        <td>${fullName}</td>
                        <td>${order.orderDate}</td>
                        <td>R${parseFloat(order.totalAmount).toFixed(2)}</td>
                        <td>${order.orderStatus}</td>
                        <td>${order.unitCount}</td>
                    </tr>
                    <tr class="order-items-row" style="display: none;" id="items-${order.orderID}">
                        <td colspan="6">
                            <div><strong>Items:</strong></div>
                            <ul class="order-items-list" id="list-${order.orderID}">
                                ${order.items.map(item => `
                                    <li>${item.pName} (${item.size}) - Qty: ${item.quantity} @ R${parseFloat(item.price).toFixed(2)}</li>
                                `).join('')}
                            </ul>
                        </td>
                    </tr>
                `;
                tbody.append(mainRow);
            });

            // Toggle visibility of order items on clicking the order row
            $('.order-row').off('click').on('click', function () {
                const orderID = $(this).data('order-id');
                $(`#items-${orderID}`).toggle();
            });
            
        }

        function loadOrdersByRange() {
            const dateRange = rangeSelect.value;

            fetch(`sellerdash/fetch_orders.php?range=${encodeURIComponent(dateRange)}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Dashboard data:", data);

                    document.getElementById('order').textContent = data.OrdersCount || 0;
                    document.getElementById('unit').textContent = data.UnitsCount || 0;
                    document.getElementById('aov2').textContent = formatCurrency(data.aov || 0);
                    document.getElementById('fulfillmentTime').textContent = formatHoursToDays(data.avgFulfillmentHours || 0);
                    document.getElementById('deliverHours').textContent = formatHoursToDays(data.avgDeliverHours || 0);
                    document.getElementById('unique').textContent = data.uniqueCustomers || 0;
                    document.getElementById('repeat').textContent = (data.repeatPercentage !== undefined) ? data.repeatPercentage + '%' : '0%';

                    renderOrderStatusChart(data.counts || {});
                    renderOrdersPerDayChart(data.ordersPerDay || []);
                    displayOrdersTable(data.orders || {});
                })
                .catch(error => {
                    console.error("Fetch failed:", error);
                });
        }

        // Initial load
        loadOrdersByRange();

        // Update data when dropdown changes
        rangeSelect.addEventListener('change', loadOrdersByRange);
    });
</script>


