<?php
if (!isset($_SESSION['sellerID'])) {
    header('Location: ../login.php');
    exit;
}
?>

<div class="row g-3 mb-4 d-flex align-items-stretch">
    <div class="col-12 col-sm-6 col-md-3 d-flex">
        <div class="card text-white mb-3 w-100 h-100" style="max-width: 24rem; background-color: #c7639f;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Total Products</span>
            </div>
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex">
                    <div class="me-4">
                        <small></small><br>
                        <h4 class="mb-0" id="tProducts">0</h4>
                    </div>
                </div>
                <i class="fas fa-shopping-cart fa-2x opacity-25"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3 d-flex">
        <div class="card text-white mb-3 w-100 h-100" style="max-width: 24rem;  background-color: #6f418e;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Total Reviews</span>
            </div>
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex">
                    <div class="me-4">
                        <small></small><br>
                        <h4 class="mb-0" id="tReviews">0</h4>
                    </div>
                </div>
                <i class="fa-solid fa-thumbs-up fa-2x opacity-25"></i>
            </div>

        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3 d-flex">
        <div class="card text-white mb-3 w-100 h-100" style="max-width: 24rem; background-color: #f35779;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Review Sentiment </span>
            </div>
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex">
                    <div class="me-4">
                        <small></small><br>
                        <h6 class="mb-0" id="sentiment">0</h6>
                    </div>
                </div>
                <i class="fas fa-shopping-cart fa-2x opacity-25"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3 d-flex">
        <div class="card text-white mb-3 w-100 h-100" style="max-width: 24rem; background-color: #f9963a;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Store Rating</span>
            </div>
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex">
                    <div class="me-4">
                        <small>STARS OF 5</small><br>
                        <h4 class="mb-0" id="sRating">0</h4>
                    </div>
                </div>
                <i class="fa-solid fa-star fa-2x opacity-25"></i>
            </div>
        </div>
    </div>

</div>


<!-- Main Dashboard Cards -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card card-min">
            <div class="card-body">
                <h5 class="card-title mb-3">Products Summary</h5>

                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search Products" value="">
                    </div>
                    <div class="col-md-5">
                        <select id="sort" class="form-select">
                            <option value="soldDesc">Total Sold: High to Low</option>
                            <option value="soldAsc">Total Sold: Low to High</option>
                            <option value="ratingDesc">Rating: High to Low</option>
                            <option value="ratingAsc">Rating: Low to High</option>
                        </select>


                    </div>
                </div>

                <div class="table-responsive">
                <table id="product-summary-table" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Total Sold</th>
                            <th>Average Rating</th>
                        </tr>
                    </thead>

                    <tbody>

                    </tbody>
                </table>
                </div>

                <button id="exportProductsBtn" class="btn btn-sm btn-outline-secondary mb-2 ">Download CSV</button>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="row">
            <!-- Selling Products Card -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-graph-up-arrow text-muted"></i> Product Performance
                        </h5>

                        <div class="mb-4">
                            <h6 class="text-success fw-bold">Top Selling</h6>
                            <ul id="top-products" class="list-group small product-list">
                            </ul>
                        </div>

                        <hr>

                        <div>
                            <h6 class="text-danger fw-bold">Least Sold</h6>
                            <ul id="least-selling" class="list-group small product-list">
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rating Products Card -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-star-fill text-warning"></i> Ratings Overview
                        </h5>

                        <div class="mb-4">
                            <h6 class="text-success fw-bold">Top Rated</h6>
                            <ul id="top-rated" class="list-group small product-list">
                            </ul>
                        </div>

                        <hr>

                        <div>
                            <h6 class="text-danger fw-bold">Lowest Rated</h6>
                            <ul id="lowest-rated" class="list-group small product-list">
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script type="module">
    
    import { downloadCSV } from "./sellerdash/js/downloadCSV.js";

    document.addEventListener("DOMContentLoaded", loadDashboardStats);

    //Export Products to CSV    
    document.getElementById("exportProductsBtn").addEventListener("click", () => {
    const rows = document.querySelectorAll("#product-summary-table tbody tr");
    const data = [];

    rows.forEach(row => {
        const cols = row.querySelectorAll("td");
        if (cols.length === 4) {
        data.push({
            "Product ID": cols[0].textContent.trim(),
            "Name": cols[1].textContent.trim(),
            "Total Sold": cols[2].textContent.trim(),
            "Average Rating": cols[3].textContent.trim().replace(" ★", ""), 
        });
        }
    });

    const headers = ["Product ID", "Name", "Total Sold", "Average Rating"];
    downloadCSV(data, headers, "products_export.csv");
    });
    
    //Search and Sort Functionality
    document.getElementById("searchInput").addEventListener("keyup", function () {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll("#product-summary-table tbody tr");

        tableRows.forEach(row => {
            const productName = row.children[0].textContent.toLowerCase();
            row.style.display = productName.includes(searchValue) ? "" : "none";
        });
    });

    document.getElementById("sort").addEventListener("change", function () {
        const selected = this.value;
        const searchValue = document.getElementById("searchInput").value.toLowerCase();

        // Re-fetch the original data to avoid sorting a filtered list
        fetch("sellerdash/fetch_products_stats.php")
            .then(res => res.json())
            .then(data => {
                let products = data.summaryProducts ?? [];

                // Apply search filter first
                if (searchValue) {
                    products = products.filter(p =>
                        (p.pName || "").toLowerCase().includes(searchValue)
                    );
                }

                // Apply sorting
                if (selected === "soldDesc") {
                    products.sort((a, b) => (b.totalQuantitySold ?? 0) - (a.totalQuantitySold ?? 0));
                } else if (selected === "soldAsc") {
                    products.sort((a, b) => (a.totalQuantitySold ?? 0) - (b.totalQuantitySold ?? 0));
                } else if (selected === "ratingDesc") {
                    products.sort((a, b) => (b.avgRating ?? 0) - (a.avgRating ?? 0));
                } else if (selected === "ratingAsc") {
                    products.sort((a, b) => (a.avgRating ?? 0) - (b.avgRating ?? 0));
                }

                renderProductSummaryTable(products, "product-summary-table");
            });
    });

    function loadDashboardStats() {
        fetch("sellerdash/fetch_products_stats.php")
            .then(res => res.json())
            .then(data => {
                console.log("Received stats:", data); // Debug log

                document.getElementById("tProducts").textContent = data.totalProducts ?? 0;
                document.getElementById("tReviews").textContent = data.totalReviews ?? 0;
                document.getElementById("sRating").textContent = data.storeRating
                    ? parseFloat(data.storeRating).toFixed(1)
                    : "N/A";
                document.getElementById("sentiment").textContent =
                    data.totalReviews == 0 ? "None" : data.sentimentLabel ?? "Unknown";

                // Load product lists (with correct types)
                renderProductList(data.topSelling ?? [], "#top-products", "sales");
                renderProductList(data.leastSelling ?? [], "#least-selling", "sales");
                renderProductList(data.topRated ?? [], "#top-rated", "rating");
                renderProductList(data.lowestRated ?? [], "#lowest-rated", "rating");

                // Load summary table
                renderProductSummaryTable(data.summaryProducts ?? [], "product-summary-table");
            })
            .catch(error => console.error("Failed to load dashboard stats:", error));
    }

    function renderProductList(products, containerSelector, type = "sales") {
        const container = document.querySelector(containerSelector);
        container.innerHTML = "";

        const list = document.createElement("ul");
        list.className = "list-group small";

        if (!Array.isArray(products) || products.length === 0) {
            const li = document.createElement("li");
            li.className = "list-group-item text-muted";
            li.textContent = "No products found.";
            list.appendChild(li);
        } else {
            products.forEach(p => {
                const name = p.pName || "Unnamed";

                const value = (type === "rating" && p.avgRating !== undefined)
                    ? `${parseFloat(p.avgRating).toFixed(1)} ★`
                    : (type === "sales" && p.totalQuantitySold !== undefined)
                        ? `${p.totalQuantitySold} sold`
                        : "N/A";

                const badgeClass = (type === "rating" && p.avgRating !== undefined)
                    ? (p.avgRating >= 4 ? "bg-success" : p.avgRating >= 2 ? "bg-warning" : "bg-danger")
                    : (type === "sales" && p.totalQuantitySold !== undefined)
                        ? (p.totalQuantitySold >= 50 ? "bg-success" : p.totalQuantitySold >= 10 ? "bg-warning" : "bg-secondary")
                        : "bg-secondary";

                const li = document.createElement("li");
                li.className = "list-group-item d-flex justify-content-between align-items-center";

                const nameSpan = document.createElement("span");
                nameSpan.textContent = name;

                const badge = document.createElement("span");
                badge.className = `badge ${badgeClass} rounded-pill`;
                badge.textContent = value;

                li.appendChild(nameSpan);
                li.appendChild(badge);
                list.appendChild(li);
            });
        }

        container.appendChild(list);

        
    }

    function renderProductSummaryTable(products, tableId) {

        const table = document.getElementById(tableId);
        if (!table) return;

        const tbody = table.querySelector("tbody");
        tbody.innerHTML = '';

        if (!Array.isArray(products) || products.length === 0) {
            const row = document.createElement("tr");
            row.innerHTML = `<td colspan="3" class="text-muted text-center">No summary data available.</td>`;
            tbody.appendChild(row);
            return;
        }

        products.forEach(product => {
            const id = product.productID || '—';
            const name = product.pName || product.name || "Unnamed";
            const totalSold = (product.totalQuantitySold !== undefined) ? product.totalQuantitySold : '—';
            const avgRating = (product.avgRating !== undefined && product.avgRating !== null)
                ? `${parseFloat(product.avgRating).toFixed(1)} ★`
                : "Not rated";

            const row = document.createElement("tr");

            const idCell = document.createElement("td");
            idCell.textContent = id;

            const nameCell = document.createElement("td");
            nameCell.textContent = name;

            const soldCell = document.createElement("td");
            soldCell.textContent = totalSold;

            const ratingCell = document.createElement("td");
            ratingCell.textContent = avgRating;

            row.appendChild(idCell);
            row.appendChild(nameCell);
            row.appendChild(soldCell);
            row.appendChild(ratingCell);

            tbody.appendChild(row);
        });


    }
</script>