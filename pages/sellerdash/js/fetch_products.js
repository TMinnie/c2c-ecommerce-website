document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".sidebar a[data-section]").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const section = this.getAttribute("data-section");

      // Hide all sections
      document.querySelectorAll("#dashboardContent > div").forEach((div) => {
        div.classList.add("d-none");
      });

      // Show selected section
      const selectedSection = document.getElementById(section + "Section");
      if (selectedSection) {
        selectedSection.classList.remove("d-none");

        if (section === "products") {
          loadProducts(selectedSection);
        }
      }
    });
  });

  // Load the default section (products) on page load
  function loadProducts(selectedSection, filters = {}) {
    const queryParams = new URLSearchParams(filters).toString();

    fetch(`sellerdash/fetch_products.php?${queryParams}`)
      .then((res) => res.json())
      .then((data) => {
        let html = `
        <div class="card shadow-sm mt-4 mb-5" style="background-color: #F1F1F1;">
          <div class="card-body mb-0">
            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
              <h3>Products</h3>
              <button class="btn btn-sm btn-primary" id="addProductBtn">+ Add Product</button>
            </div>
            <hr>

            <!-- Filters and Search -->
            <div class="row g-3 mb-4">
              <div class="col-md-5">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by name or description..." value="${
                  filters.search || ""
                }">
              </div>
              <div class="col-md-3">
                <select id="statusFilter" class="form-select">
                  <option value="">All Statuses</option>
                  <option value="active" ${
                    filters.status === "active" ? "selected" : ""
                  }>Active</option>
                  <option value="inactive" ${
                    filters.status === "inactive" ? "selected" : ""
                  }>Inactive</option>
                </select>
              </div>
              <div class="col-md-3">
                <select id="sortFilter" class="form-select">
                  <option value="">Sort by</option>
                  <option value="price_asc" ${
                    filters.sort === "price_asc" ? "selected" : ""
                  }>Price: Low to High</option>
                  <option value="price_desc" ${
                    filters.sort === "price_desc" ? "selected" : ""
                  }>Price: High to Low</option>
                  <option value="created_asc" ${
                    filters.sort === "created_asc" ? "selected" : ""
                  }>Oldest First</option>
                  <option value="created_desc" ${
                    filters.sort === "created_desc" ? "selected" : ""
                  }>Newest First</option>
                  <option value="stock_asc" ${
                    filters.sort === "stock_asc" ? "selected" : ""
                  }>Stock: Low to High</option>
                  <option value="stock_desc" ${
                    filters.sort === "stock_desc" ? "selected" : ""
                  }>Stock: High to Low</option>
                </select>
              </div>
              <div class="col-md-1 d-grid">
                <button id="clearFilters" class="btn btn-outline-secondary">X</button>
              </div>
            </div>

            <div class="table-responsive mt-3">
              <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-light">
                  <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Size Variants</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
      `;

        if (data.length === 0) {
          html +=
            '<tr><td colspan="6" class="text-muted">No products found.</td></tr>';
        } else {
          data.forEach((p) => {
            const isInactive = p.status === "inactive";
            const toggleBtnText = isInactive ? "Activate" : "Deactivate";
            const toggleBtnClass = isInactive ? "btn-success" : "btn-danger";
            const toggleBtnAction = isInactive ? "activate" : "deactivate";
            const shortDesc =
              p.pDescription.length > 50
                ? p.pDescription.slice(0, 50) + "..."
                : p.pDescription;

            const quantitiesHtml = `
              <div class="variants-container" style="max-height: 80px; overflow: hidden; transition: max-height 0.3s ease;">
                <table class="table table-sm mb-0" style="--bs-table-bg: transparent;">
                  <thead>
                    <tr><th>Size</th><th>QTY</th></tr>
                  </thead>
                  <tbody>
                    ${p.variants
                      .map(
                        (v) => `
                      <tr>
                        <td>${v.size}</td>
                        <td>${v.stockQuantity}</td>
                      </tr>
                    `
                      )
                      .join("")}
                  </tbody>
                </table>
              </div>
              <button class="btn btn-link btn-sm p-0 mt-1 toggle-variants-btn">Show More</button>
            `;

            html += `
    <tr class="${isInactive ? "table-secondary text-muted" : ""}">
      <td>
        <img src="uploads/${p.imagePath}" alt="${
              p.pName
            }" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px; ${
              isInactive ? "filter: grayscale(100%); opacity: 0.6;" : ""
            }">
      </td>
      <td>${p.pName}</td>
      <td>R${p.pPrice}</td>
      <td>${quantitiesHtml}</td>
      <td>${
        isInactive
          ? '<span class="badge bg-secondary">Inactive</span>'
          : '<span class="badge bg-success">Active</span>'
      }</td>
      <td>
        <div class="d-flex justify-content-center gap-2">
          <button class="btn btn-sm btn-warning update-btn" data-id="${
            p.productID
          }">Update</button>
          <button class="btn btn-sm ${toggleBtnClass} toggle-status-btn" data-id="${
              p.productID
            }" data-action="${toggleBtnAction}">${toggleBtnText}</button>
        </div>
      </td>
    </tr>
  `;
          });
        }

        html += `
                </tbody>
              </table>
            </div>
          </div>
        </div>
      `;

        selectedSection.innerHTML = html;

        selectedSection.querySelectorAll(".toggle-variants-btn").forEach((btn) => {
          btn.addEventListener("click", () => {
            const variantsDiv = btn.previousElementSibling;
            if (variantsDiv.style.maxHeight === "none" || variantsDiv.style.maxHeight === "") {
              variantsDiv.style.maxHeight = "80px";
              btn.textContent = "Show More";
            } else {
              variantsDiv.style.maxHeight = "none";
              btn.textContent = "Show Less";
            }
          });
        });

        attachFilterListeners();

        selectedSection.querySelectorAll("td img").forEach((img) => {
          img.style.cursor = "zoom-in"; // hint user it's clickable

          img.addEventListener("click", () => {
            const zoomModal = document.getElementById("imageZoomModal");
            const zoomedImg = document.getElementById("zoomedImage");
            zoomedImg.src = img.src;
            zoomModal.style.display = "flex";
          });
        });

        const addBtn = document.getElementById("addProductBtn");
        if (addBtn) {
          addBtn.addEventListener("click", () => {
            showAddProductForm(selectedSection);
          });
        }
      });


  }

  // Utility to collect filters and reload products
  function applyFilters() {
    const search = document.getElementById("searchInput").value.trim();
    const status = document.getElementById("statusFilter").value;
    const sort = document.getElementById("sortFilter").value;

    const filters = {
      search,
      status,
      sort,
    };

    console.log("Filters sent:", filters);

    const selectedSection = document.getElementById("productsSection");
    loadProducts(selectedSection, filters);
  }

  function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
  }

  // Filter listeners attached after the filters are rendered
  function attachFilterListeners() {
    ["searchInput", "statusFilter", "sortFilter"].forEach((id) => {
      const el = document.getElementById(id);
      if (!el) return;
      if (id === "searchInput") {
        el.addEventListener("input", debounce(applyFilters, 400)); // debounce search input by 400ms
      } else {
        el.addEventListener("change", applyFilters);
      }
    });

    document.getElementById("clearFilters").addEventListener("click", () => {
      document.getElementById("searchInput").value = "";
      document.getElementById("statusFilter").value = "";
      document.getElementById("sortFilter").value = "";

      // Reset the filters
      applyFilters();
    });
  }

  // Initial load
  const defaultSection = document.getElementById("productsSection");
  if (defaultSection) {
    loadProducts(defaultSection);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Function to show the Add Product form
  function showAddProductForm(selectedSection) {
    const formHTML = `
      <div class="card shadow mb-5 mt-4 p-3" style="background-color: #F1F1F1;" >

      <h3 class=" mt-2">Add Product</h3>
   <hr>
        <form id="productForm" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="pName" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="pName" name="pName" required>
          </div>
          <div class="mb-3">
            <label for="pDescription" class="form-label">Product Description</label>
            <textarea class="form-control" id="pDescription" name="pDescription" required ></textarea>
          </div>
          <div class="mb-3">
            <label for="pPrice" class="form-label">Price</label>
            <input type="number" class="form-control" id="pPrice" name="pPrice" min="0" step="0.01" required>
          </div>

        <!-- Variants Section -->
        <div id="variantSection">
          <label>Size Variants</label>
          <div id="variantRowsWrapper">
            <div class="variant-row mb-2">
              <input type="text" class="form-control d-inline w-50 w-md-25 me-2" name="sizes[]" value="One Size">
              <input type="number" class="form-control d-inline w-25 " name="quantities[]" placeholder="QTY">
            </div>
          </div>
          <button type="button" class="btn btn-sm btn-outline-secondary mt-2 mb-2" onclick="addVariantRow()">Add Another Size</button>
          <button type="button" class="btn btn-sm btn-outline-secondary mt-2 mb-2" onclick="removeVariantRow()">Remove Size</button>
        </div>

          <div class="mb-3">
            <label for="pCategory" class="form-label">Category</label>
            <select class="form-select" id="pCategory" name="pCategory" required></select>
          </div>
          <div class="mb-3">
            <label for="imagePath" class="form-label">Product Image</label>
            <input type="file" class="form-control" id="imagePath" name="imagePath" required>
          </div>
          <div class="mb-3">
            <button type="submit" class="btn btn-primary w-100">Add Product</button>
          </div>
        </form>
      </div>
    `;

    selectedSection.innerHTML = formHTML;

    updateRemoveButtonVisibility();

    // Fetch categories for Add Product form
    fetch("sellerdash/fetch_categories.php")
      .then((res) => res.json())
      .then((categories) => {
        const select = document.getElementById("pCategory");
        select.innerHTML = '<option value="">Select a category</option>';
        categories.forEach((cat) => {
          select.innerHTML += `<option value="${cat.categoryID}">${cat.name}</option>`;
        });
      });

    document
      .getElementById("productForm")
      .addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch("sellerdash/add_product.php", {
          method: "POST",
          body: formData,
        })
          .then((res) => {
            // Check if the response is valid JSON
            const contentType = res.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
              return res.json();
            } else {
              throw new Error("Invalid response from server (not JSON).");
            }
          })
          .then((data) => {
            if (data.success) {
              alert("Product added successfully!");
              const productLink = document.querySelector(
                `.sidebar a[data-section="products"]`
              );
              if (productLink) {
                productLink.click();
              } else {
                // Fallback: reload or redirect manually
                window.location.href = "seller_products.php";
              }
            } else {
              alert("Error: " + (data.error || "Unknown error"));
            }
          })
          .catch((err) => {
            console.error("Fetch error:", err);
            alert("An error occurred while submitting the form.");
          });
      });
  }

  window.addVariantRow = function () {
    const wrapper = document.getElementById("variantRowsWrapper");
    const row = document.createElement("div");
    row.classList.add("variant-row", "mb-2");
    row.innerHTML = `
    <input type="text" class="form-control d-inline w-50 w-md-25 me-2" name="sizes[]" placeholder="Size (e.g. M)">
    <input type="number" class="form-control d-inline w-25" name="quantities[]" placeholder="Quantity">
  `;
    wrapper.appendChild(row);
    updateRemoveButtonVisibility();
  };

  window.removeVariantRow = function () {
    const wrapper = document.getElementById("variantRowsWrapper");
    const rows = wrapper.querySelectorAll(".variant-row");
    if (rows.length > 1) {
      wrapper.removeChild(rows[rows.length - 1]);
    }
    updateRemoveButtonVisibility();
  };

  function updateRemoveButtonVisibility() {
    const wrapper = document.getElementById("variantRowsWrapper");
    const removeBtn = document.querySelector(
      'button[onclick="removeVariantRow()"]'
    );
    const rowsCount = wrapper.querySelectorAll(".variant-row").length;
    if (rowsCount > 1) {
      removeBtn.style.display = "inline-block";
    } else {
      removeBtn.style.display = "none";
    }
  }

  // Initialize remove button visibility on form load
  document.addEventListener("DOMContentLoaded", () => {
    updateRemoveButtonVisibility();
  });

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Fetch and display product data in update form
  window.fetchProductData = function (productID) {
    fetch("sellerdash/get_product.php?productID=" + productID)
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        return res.json();
      })
      .then((data) => {
        console.log(data);
        if (data.error) {
          alert(data.error);
        } else {
          const updateFormHTML = generateUpdateForm(data);
          const selectedSection = document.getElementById("productsSection");
          selectedSection.innerHTML = updateFormHTML;

          // Fetch categories for Update Product form
          fetch("sellerdash/fetch_categories.php")
            .then((res) => res.json())
            .then((categories) => {
              const select = document.getElementById("pCategory");
              select.innerHTML = '<option value="">Select a category</option>';
              categories.forEach((cat) => {
                const option = document.createElement("option");
                option.value = cat.categoryID;
                option.textContent = cat.name;

                // Pre-select the category for the product being updated
                if (cat.categoryID == data.pCategory) {
                  option.selected = true;
                }

                select.appendChild(option);
              });
            });

          document
            .getElementById("updateProductForm")
            .addEventListener("submit", function (e) {
              e.preventDefault();
              const formData = new FormData(this);

              fetch("sellerdash/update_product.php", {
                method: "POST",
                body: formData,
              })
                .then((res) => res.json())
                .then((data) => {
                  if (data.success) {
                    alert("Product updated successfully!");
                    document
                      .querySelector(`.sidebar a[data-section="products"]`)
                      .click();
                  } else {
                    alert("Error: " + data.error);
                  }
                });
            });
        }
      })
      .catch((err) => {
        console.error("Error fetching product:", err);
      });
  };

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  // Generate update form HTML
  function generateUpdateForm(data) {
    let variantsHTML = "";
    if (data.variants && data.variants.length > 0) {
      variantsHTML += `
          <div class="mb-3">
            <label class="form-label">Product Variants (Size & Stock)</label>
        `;

      data.variants.forEach((variant, index) => {
        variantsHTML += `
            <div class="input-group mb-2">
              <span class="input-group-text">${variant.size}</span>
              <input type="hidden" name="variantIDs[]" value="${variant.variantID}">
              <input type="number" class="form-control" name="stockQuantities[]" value="${variant.stockQuantity}" required>
            </div>
          `;
      });

      variantsHTML += `</div>`;
    }

    return `
      <div class="card shadow mb-5 mt-4 p-3" style="background-color: #F1F1F1;">
        <h4 class="mt-2">Update Product</h4>
        <hr>
        <form id="updateProductForm" enctype="multipart/form-data">
          <input type="hidden" name="productID" value="${data.productID}">
          <div class="mb-3">
            <label for="pName" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="pName" name="pName" value="${data.pName}" required>
          </div>
          <div class="mb-3">
            <label for="pDescription" class="form-label">Product Description</label>
            <textarea class="form-control" id="pDescription" name="pDescription" rows="5" required>${data.pDescription}</textarea>
          </div>
          <div class="mb-3">
            <label for="pPrice" class="form-label">Price</label>
            <input type="number" class="form-control" id="pPrice" name="pPrice" value="${data.pPrice}" required>
          </div>

           ${variantsHTML}

          <!-- Display current category -->
          <div class="mb-3">
            <label for="pCategory" class="form-label">Category</label>
            <select class="form-select" id="pCategory" name="pCategory" value="${data.pCategory}"></select>
          </div>
          
          <!-- Display current image -->
          <div class="mb-3">
            <label for="currentImage" class="form-label">Current Product Image</label>
            <div>
              <img id="currentImage" src="uploads/${data.imagePath}" alt="${data.pName}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px;">
            </div>
          </div>
  
          <!-- File input to upload new image -->
          <div class="mb-3">
            <label for="imagePath" class="form-label">Change Product Image</label>
            <input type="file" class="form-control" id="imagePath" name="imagePath">
            <small class="form-text text-muted">Leave empty to keep the current image.</small>
          </div>
  
          <div class="mb-3">
            <button type="submit" class="btn btn-primary w-100">Update Product</button>
          </div>
        </form>
      </div>
    `;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  // Deactivate product function
  function deactivateProduct(productID) {
    if (!confirm("Are you sure you want to deactivate this product?")) {
      return;
    }

    fetch("sellerdash/deactivate_product.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ productID: productID }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          alert("Product deactivated successfully.");
          document.querySelector(`.sidebar a[data-section="products"]`).click(); // Reloads the product list
        } else {
          alert("Error: " + data.error);
        }
      })
      .catch((err) => {
        console.error("Deactivation failed:", err);
        alert("An error occurred while trying to deactivate the product.");
      });
  }

  //Activate product function
  function activateProduct(productID) {
    if (!confirm("Are you sure you want to activate this product?")) {
      return;
    }

    fetch("sellerdash/activate_product.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ productID: productID }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          alert("Product activated successfully.");
          document.querySelector(`.sidebar a[data-section="products"]`).click(); // Reload the product list
        } else {
          alert("Error: " + data.error);
        }
      })
      .catch((err) => {
        console.error("Activation failed:", err);
        alert("An error occurred while trying to activate the product.");
      });
  }
});

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Function to attach event listeners for buttons
document.addEventListener("click", function (e) {
  if (e.target.classList.contains("update-btn")) {
    const productID = e.target.dataset.id;
    console.log("Update button clicked with ID:", productID);
    fetchProductData(productID);
  }

  if (e.target.classList.contains("toggle-status-btn")) {
    const productID = e.target.dataset.id;
    const action = e.target.dataset.action;
    const confirmMsg =
      action === "deactivate"
        ? "Are you sure you want to deactivate this product?"
        : "Are you sure you want to activate this product?";
    if (!confirm(confirmMsg)) return;

    fetch(`sellerdash/${action}_product.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ productID }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          document.querySelector(`.sidebar a[data-section="products"]`).click(); // reload products
        } else {
          alert("Error: " + data.error);
        }
      });
  }
});

// Close modal logic (add this once outside loadProducts, e.g., after DOMContentLoaded)
          const zoomModal = document.getElementById("imageZoomModal");
          const closeZoom = document.getElementById("closeZoom");

          closeZoom.addEventListener("click", () => {
            zoomModal.style.display = "none";
          });

          zoomModal.addEventListener("click", (e) => {
            if (e.target === zoomModal) {
              zoomModal.style.display = "none";
            }
          });

