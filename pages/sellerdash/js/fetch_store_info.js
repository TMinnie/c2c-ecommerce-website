document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".sidebar a[data-section]").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const section = this.getAttribute("data-section");

      // Hide all sections
      document.querySelectorAll("#dashboardContent > div").forEach((div) => {
        div.classList.add("d-none");
      });

      // Show the selected section
      const selectedSection = document.getElementById(section + "Section");
      if (selectedSection) {
        selectedSection.classList.remove("d-none");

        if (section === "settings") {
          fetch("sellerdash/fetch_store_info.php")
            .then((res) => res.json())
            .then((data) => {
              if (data.error) {
                selectedSection.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
              }

              let html = `
                <div class="card shadow mb-5 mt-4 p-3">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">My Store</h3>

                    <div class="d-flex justify-content-between align-items-center">

                    <button class="btn btn-outline-secondary me-2" id="updateStoreBtn">
                         Update Info
                      </button>
                      
                    </div>
                  </div>

                  <hr>

                  <div class="row g-4 align-items-start mt-2" style="">
                    <div class="col-md-3 text-center">
                      <img src="uploads/${data.imagePath}" alt="${data.businessName}"
                          class="img-fluid rounded-circle border border-3 mx-auto my-auto d-block"
                          style="width: 180px; height: 180px; min-width: 180px; object-fit: cover;">
                    </div>

                    <div class="col-md-9" style="">
                      <h4 class="fw-semibold mb-2">${data.businessName}</h4>
                      
                      <ul class="list-unstyled">
                      <li class="mb-2">
                          <span class="text-muted">${data.businessDescript}</span>
                        </li>
                        <li class="mb-2">
                          <strong>Store Address:</strong>
                        </li>
                        <li class="mb-1">
                          ${data.pickupAddress}
                        </li>
                        <li class="mb-2">
                          ${data.city}
                        </li>
                        <li class="mb-2">
                          <strong>Account Number:</strong> ${data.payDetails}
                        </li>
                        
                      </ul>
                    </div>
                  </div>
                </div>
                </div>
 
              `;

              selectedSection.innerHTML = html;

              document
                .getElementById("updateStoreBtn")
                .addEventListener("click", () => {
                  const formHTML = `
          <div class="card shadow mb-5 mt-4 p-3">
            <h3 class="mb-2 mt-3">Update Store</h3>
            <hr>
            <form id="updateStoreForm" enctype="multipart/form-data">
              <div class="mb-3">
                <label for="businessName" class="form-label">Business Name</label>
                <input type="text" class="form-control" id="businessName" name="businessName" value="${data.businessName}" required>
              </div>
              <div class="mb-3">
                <label for="pickupAddress" class="form-label">Pick-up Address</label>
                <input type="text" class="form-control" id="pickupAddress" name="pickupAddress" value="${data.pickupAddress}" required>
              </div>
              <div class="mb-3">
                <label for="city" class="form-label">City</label>
                <input type="text" class="form-control" id="city" name="city" value="${data.city}">
              </div>
              <div class="mb-3">
                <label for="payDetails" class="form-label">Account number</label>
                <input type="text" class="form-control" id="payDetails" name="payDetails" value="${data.payDetails}" required>
              </div>
              <div class="mb-3">
                <label for="businessDescript" class="form-label">Description</label>
                <textarea class="form-control" id="businessDescript" name="businessDescript" required>${data.businessDescript}</textarea>
              </div>
              <div class="mb-3">
                <label for="imageFile" class="form-label">Store Image</label>
                <input type="file" name="imageFile" class="form-control" id="imageFile">
                <small class="form-text text-muted">Leave empty to keep current image.</small>
              </div>
              <div class="mb-3">
                <button type="submit" class="btn btn-success w-100">Save Changes</button>
              </div>
            </form>
          </div>
        `;
                  selectedSection.innerHTML = formHTML;

                  document
                    .getElementById("updateStoreForm")
                    .addEventListener("submit", function (e) {
                      e.preventDefault();
                      const formData = new FormData(this);

                      fetch("sellerdash/update_store_info.php", {
                        method: "POST",
                        body: formData,
                      })
                        .then((res) => res.json())
                        .then((response) => {
                          if (response.success) {
                            alert("Store updated successfully!");
                            document
                              .querySelector(
                                `.sidebar a[data-section="settings"]`
                              )
                              .click(); // Refresh view
                          } else {
                            alert("Error: " + response.error);
                          }
                        })
                        .catch((err) => {
                          console.error("Error updating store:", err);
                          alert("An error occurred.");
                        });
                    });
                });
            })
            .catch((err) => {
              console.error("Error fetching store data:", err);
              selectedSection.innerHTML = `<div class="alert alert-danger">Failed to load store data.</div>`;
            });
        }
      }
    });
  });
});
