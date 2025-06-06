<?php
if (!isset($_SESSION['sellerID'])) {
    header('Location: ../login.php');
    exit;
}
?>

<div class="d-flex justify-content-end mb-3">
    <select id="rangeSelect" class="form-select w-50 w-md-25">
        <option value="7">Last 7 days</option>
        <option value="30" selected>Last 30 days</option>
        <option value="90">Last 90 days</option>
        <option value="365">Last 365 days</option>
    </select>
</div>

<!-- Top Summary Boxes -->
<div class="row g-3 mb-4 ">

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card text-white mb-3" style=" background-color: #6f418e;">
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

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card text-white mb-3" style=" background-color: #c7639f;">
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

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card text-white mb-3" style=" background-color: #f35779;">
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

    <div class="col-12 col-sm-6 col-md-3">
        <div class="card text-white mb-3" style="background-color: #f9963a;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Best Sales Period</span>
            </div>
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex">
                    <div class="me-4">
                        <small>Day</small><br>
                        <h4 class="mb-0" id="day">0</h4>
                    </div>
                    <div class="me-4">
                        <small>Time</small><br>
                        <h4 class="mb-0" id="time">0</h4>
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
    <div class="col-12 col-md-8 mb-4 mb-md-0">
        <div class="card card-min">
            <div class="card-body">
                <h5 class="card-title">Sales</h5>
                <p class="text-muted small">Chart for sales data across <em id="dateRange">0</em> days</p>
                <!-- Placeholder for Chart -->
                <canvas id="salesChart" height="150"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card shadow-sm ">
            <div class="card-body mb-0">
                <h5 class="card-title">Sales summary</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover mb-0">
                        <tbody>
                            <tr class="text-nowrap">
                                <td>Charges</td>
                                <td>Count</td>
                                <td><span id="chargesCount">0</span></td>
                                <td></td>
                            </tr>
                            <tr class="text-nowrap">
                                <td></td>
                                <td>Gross</td>
                                <td><br></td>
                                <td><span id="chargesGross">0</span></td>
                            </tr>
                            <tr class="text-nowrap">
                                <td>Refunds</td>
                                <td>Count</td>
                                <td><span id="refundsCount">0</span></td>
                                <td></td>
                            </tr>
                            <tr class="text-nowrap">
                                <td></td>
                                <td>Gross</td>
                                <td></td>
                                <td><span id="refundsGross">0</span></td>
                            </tr>
                            <tr class="text-nowrap">
                                <td>Balance</td>
                                <td></td>
                                <td><span id="balanceCount">0</span></td>
                                <td><span id="balanceGross">0</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>


<div class="mt-5">
    <h4>Sales</h4>
    <div class="row mb-3">
        <div class="col-md-2">
            <select id="dateFilter" class="form-select">
                <option value="all">All Dates</option>
                <option value="today">Today</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="custom">Custom Range</option>
            </select>
        </div>
        <div class="col-md-3" id="customDateRange" style="display: none;">
            <input type="date" id="startDate" class="form-control mb-1">
            <input type="date" id="endDate" class="form-control">
        </div>
        <div class="col-md-2">
            <select id="statusFilter" class="form-select">
                <option value="">All Statuses</option>
                <option value="paid">Paid</option>
                <option value="refunded">Refunded</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div class="col-md-3 d-flex justify-content-between align-items-top">
            <label class="me-2">Sort:</label>
            <select id="sortSales" class="form-select">
                <option value="latest">Latest</option>
                <option value="amount_desc">Amount ↓</option>
                <option value="amount_asc">Amount ↑</option>
                <option value="units_desc">Units ↓</option>
                <option value="units_asc">Units ↑</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table id="sales-table" class="table table-light table-bordered table-hover">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Units</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sales data rows go here -->
            </tbody>
            <tfoot>
                <tr>
                <td colspan="4" class="text-end fw-bold">Total</td>
                <td id="total-units" class="fw-bold">0</td>
                <td id="total-amount" class="fw-bold">R0.00</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <button id="downloadCSV" class="btn btn-sm btn-outline-secondary mb-2 ">Download CSV</button>


</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Sales Chart & Dashboard Stats -->
<script>
    let salesChartInstance;

    document.addEventListener('DOMContentLoaded', () => {
        const rangeSelect = document.getElementById('rangeSelect');

        const formatCurrency = amount => {
            const num = parseFloat(amount);
            return `R${isNaN(num) ? '0.00' : num.toFixed(2)}`;
        };

        const loadOrdersByRange = () => {
            const range = rangeSelect.value;

            // Fetch sales chart & metrics
            fetch(`sellerdash/fetch_sales.php?range=${range}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) return console.error("PHP Error:", data.error);

                    const gross = parseFloat(data.gross);
                    const labels = data.labels.length >= 2 ? data.labels : ['Previous Day', ...data.labels];
                    const sales = data.labels.length >= 2 ? data.sales : [0, ...data.sales];

                    // Draw Chart
                    const ctx = document.getElementById('salesChart').getContext('2d');
                    if (salesChartInstance) salesChartInstance.destroy();

                    salesChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels.map(d => new Date(d).toLocaleDateString('en-ZA', { month: 'short', day: '2-digit' })),
                            datasets: [{
                                label: 'Sales (R)',
                                data: sales,
                                borderColor: 'rgb(255, 145, 0)',
                                backgroundColor: 'rgba(255, 145, 0, 0.4)',
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: true, position: 'top' }
                            },
                            scales: { y: { beginAtZero: true } }
                        }
                    });

                    // Update stat widgets
                    document.getElementById('dateRange').textContent = range;
                    document.getElementById('day').textContent = data.best_day;
                    document.getElementById('time').textContent = data.best_time;
                    document.getElementById('total').textContent = formatCurrency(gross);

                    document.getElementById('chargesCount').textContent = data.charges.count;
                    document.getElementById('chargesGross').textContent = formatCurrency(data.gross);
                    document.getElementById('refundsCount').textContent = data.refunds.count;
                    document.getElementById('refundsGross').textContent = formatCurrency(data.refunds.total);
                    document.getElementById('balanceCount').textContent = data.charges.count + data.refunds.count;
                    document.getElementById('balanceGross').textContent = formatCurrency(data.balance);
                })
                .catch(err => console.error("Sales fetch failed:", err));

            // Fetch order summary
            fetch(`sellerdash/fetch_orders.php?range=${range}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('orders').textContent = data.OrdersCount;
                    document.getElementById('units').textContent = data.UnitsCount;
                    document.getElementById('aov').textContent = formatCurrency(data.aov);
                })
                .catch(err => console.error("Orders fetch failed:", err));
        };

        // Initial and on-change load
        rangeSelect.addEventListener('change', loadOrdersByRange);
        loadOrdersByRange();
    });
</script>

<!-- Sales Table + CSV Export -->
<script type="module">
    import { downloadCSV } from './sellerdash/js/downloadCSV.js';

    document.addEventListener('DOMContentLoaded', () => {
        const salesTableBody = document.querySelector("#sales-table tbody");
        let latestSalesData = [];

        const formatCurrency = amount => `R${parseFloat(amount).toFixed(2)}`;

        const fetchSales = () => {
            const getVal = id => document.getElementById(id)?.value || '';
            const date = getVal('dateFilter') || 'all';
            const params = new URLSearchParams({
                date,
                status: getVal('statusFilter'),
                product: getVal('productSearch'),
                buyer: getVal('buyerSearch'),
                sort: getVal('sortSales')
            });

            if (date === 'custom') {
                const start = getVal('startDate');
                const end = getVal('endDate');
                if (start && end) {
                    params.append('start', start);
                    params.append('end', end);
                }
            }

            fetch(`sellerdash/fetch_seller_transactions.php?${params}`)
                .then(res => res.json())
                .then(data => {
                    latestSalesData = Array.isArray(data) ? data : [];
                    salesTableBody.innerHTML = "";

                    if (!latestSalesData.length) {
                        salesTableBody.innerHTML = `<tr><td colspan="6">No transactions found.</td></tr>`;
                        return;
                    }

                    let totalUnits = 0, totalAmount = 0;

                    latestSalesData.forEach(tx => {
                        const units = parseInt(tx.unitCount);
                        const amount = parseFloat(tx.paymentAmount);
                        totalUnits += units;
                        totalAmount += amount;

                        const row = document.createElement("tr");
                        row.innerHTML = `
                            <td>${tx.paymentID}</td>
                            <td>${tx.orderID}</td>
                            <td>${tx.paymentDate}</td>
                            <td>${tx.paymentStatus}</td>
                            <td>${units}</td>
                            <td>${formatCurrency(amount)}</td>
                        `;
                        salesTableBody.appendChild(row);
                    });

                    document.getElementById('total-units').textContent = totalUnits;
                    document.getElementById('total-amount').textContent = formatCurrency(totalAmount);
                })
                .catch(err => {
                    console.error("Fetch error:", err);
                    salesTableBody.innerHTML = `<tr><td colspan="6" class="text-danger">Failed to load transactions.</td></tr>`;
                });
        };

        document.getElementById('downloadCSV').addEventListener('click', () => {
            if (!latestSalesData.length) {
                alert("No sales data to download.");
                return;
            }

            const headers = [
                { label: "Payment ID", key: "paymentID" },
                { label: "Order ID", key: "orderID" },
                { label: "Payment Date", key: "paymentDate" },
                { label: "Payment Status", key: "paymentStatus" },
                { label: "Units", key: "unitCount" },
                { label: "Amount", key: "paymentAmount" }
            ];

            const csvData = latestSalesData.map(row => {
                return headers.reduce((acc, h) => {
                    acc[h.label] = h.key === "paymentAmount" ? formatCurrency(row[h.key]) : row[h.key];
                    return acc;
                }, {});
            });

            const totalUnits = latestSalesData.reduce((sum, tx) => sum + parseInt(tx.unitCount), 0);
            const totalAmount = latestSalesData.reduce((sum, tx) => sum + parseFloat(tx.paymentAmount), 0);

            csvData.push({
                "Payment ID": "TOTALS",
                "Order ID": "",
                "Payment Date": "",
                "Payment Status": "",
                "Units": totalUnits,
                "Amount": formatCurrency(totalAmount)
            });

            downloadCSV(csvData, headers.map(h => h.label), "sales_data.csv", { delimiter: ";" });
        });

        ['dateFilter', 'statusFilter', 'productSearch', 'buyerSearch', 'sortSales', 'startDate', 'endDate'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', fetchSales);
        });

        const customRangeDiv = document.getElementById('customDateRange');
        document.getElementById('dateFilter')?.addEventListener('change', function () {
            if (customRangeDiv) customRangeDiv.style.display = this.value === 'custom' ? 'block' : 'none';
        });

        fetchSales();
    });
</script>



