document.addEventListener("DOMContentLoaded", function () {
  const reviewsSection = document.getElementById("reviewsSection");

  function renderReviewSection() {
    reviewsSection.innerHTML = `
    <div class="card shadow-sm mt-4 mb-5" style="background-color: #F1F1F1;">
                <div class="card-body mb-0">
      <h3 class="mb-4 mt-2">Customer Reviews</h3>
      <hr>
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <select id="reviewSort" class="form-select ">
            <option value="latest" selected>Sort by: Latest</option>
            <option value="rating">Sort by: Rating</option>
            <option value="product">Sort by: Product</option>
          </select>
        </div>
        <div class="col-md-3">
          <select id="filterRating" class="form-select">
            <option value="">All Ratings</option>
            <option value="5">★★★★★</option>
            <option value="4">★★★★☆ & up</option>
            <option value="3">★★★☆☆ & up</option>
            <option value="2">★★☆☆☆ & up</option>
            <option value="1">★☆☆☆☆ & up</option>
          </select>
        </div>
        <div class="col-md-3">
          <select id="filterProduct" class="form-select"></select>
        </div>
        <div class="col-md-3">
           <input type="date" id="filterDate" class="form-control" />
        </div>
        
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center" id="reviewsTable">
          <thead class="table-light">
            <tr>
              <th>Image</th>
              <th>Reviewer</th>
              <th>Product</th>
              <th>Rating</th>
              <th>Comment</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody id="reviewRow"></tbody>
        </table>
      </div>
      </div>
      </div>
    `;

    // Re-bind event listeners to new elements
    document.getElementById("reviewSort").addEventListener("change", () =>
      loadSellerReviews(document.getElementById("reviewSort").value)
    );
    document.getElementById("filterRating").addEventListener("change", () =>
      loadSellerReviews(document.getElementById("reviewSort").value)
    );
    document.getElementById("filterProduct").addEventListener("change", () =>
      loadSellerReviews(document.getElementById("reviewSort").value)
    );
    document.getElementById("filterDate").addEventListener("change", () =>
      loadSellerReviews(document.getElementById("reviewSort").value)
    );


  }

  function loadSellerReviews(sortBy = "latest") {
    const rating = document.getElementById("filterRating")?.value || "";
    const product = document.getElementById("filterProduct")?.value || "";
    const date = document.getElementById("filterDate")?.value || "";

const url = new URL("/pages/sellerdash/fetch_seller_reviews.php", window.location.origin);

   console.log("Fetching reviews from:", url.toString());

    url.searchParams.set("sort", sortBy);
    url.searchParams.set("filterRating", rating);
    url.searchParams.set("filterProduct", product);
    url.searchParams.set("filterDate", date);

    fetch("/pages/sellerdash/fetch_seller_reviews.php")
  .then(r => console.log(r.status))
  .catch(e => console.error(e));
  fetch("/sellerdash/fetch_seller_reviews.php").then(r => console.log(r.status));
fetch("/fetch_seller_reviews.php").then(r => console.log(r.status));


    fetch(url)
      .then((response) => response.json())
      .then((data) => {
        const productSelect = document.getElementById("filterProduct");
        const dateSelect = document.getElementById("filterDate");
        const reviewRow = document.getElementById("reviewRow");

        // Update product and date filter options
        const products = [...new Set(data.map((r) => r.pName))];
        productSelect.innerHTML = `<option value="">All Products</option>` + products.map(p => `<option value="${p}">${p}</option>`).join("");

        reviewRow.innerHTML = "";

        if (!data.length) {
          reviewRow.innerHTML = `<tr><td colspan="6" class="text-muted">No customer reviews yet.</td></tr>`;
          return;
        }

        data.forEach((review) => {
          const ratingStars = [...Array(5)]
            .map((_, i) =>
              i < review.rating
                ? '<span class="text-warning">&#9733;</span>'
                : '<span class="text-muted">&#9733;</span>'
            )
            .join("");

          const row = document.createElement("tr");
          row.innerHTML = `
            <td><img src="uploads/${review.imagePath}" alt="${review.pName}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;"></td>
            <td>${review.uFirst} ${review.uLast}</td>
            <td>${review.pName}</td>
            <td>${ratingStars}</td>
            <td>${review.rComment}</td>
            <td>${review.reviewDate}</td>
          `;
          reviewRow.appendChild(row);
        });
      })
      .catch((error) => {
        document.getElementById("reviewRow").innerHTML = `<tr><td colspan="6" class="text-danger">Failed to load reviews.</td></tr>`;
        console.error("Error loading reviews:", error);
      });
  }

  // Sidebar loader
  document.querySelectorAll(".sidebar a[data-section]").forEach((link) => {
    link.addEventListener("click", function () {
      const section = this.getAttribute("data-section");
      document.querySelectorAll(".main-content > div > div").forEach((sec) => {
        sec.classList.add("d-none");
      });
      const selectedSection = document.getElementById(section + "Section");
      selectedSection.classList.remove("d-none");

      if (section === "reviews") {
        renderReviewSection();
        loadSellerReviews();
      }
    });
  });

  // Optional: Load reviews immediately if reviewsSection is active on page load
  if (reviewsSection?.classList.contains("active")) {
    renderReviewSection();
    loadSellerReviews();
  }
});
