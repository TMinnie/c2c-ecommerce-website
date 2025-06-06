console.log("Seller dashboard script loaded");
document.addEventListener("DOMContentLoaded", function () {
  const newOrdersCol = document.getElementById("newOrders");
  const acceptedOrdersCol = document.getElementById("acceptedOrders");
  const shippedOrdersCol = document.getElementById("shippedOrders");
  const completedOrdersCol = document.getElementById("completedOrders");

  // Inject Order Modal into the DOM
  const modalHTML = `
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content shadow-sm">
      <div class="modal-header">
        <h3 class="modal-title" id="orderModalLabel">Order Details</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="card mb-3" style="background-color: #F1F1F1;">
          <div class="card-body" id="orderModalBody">
            <!-- Dynamic content will load here -->
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button id="acceptOrderBtn" class="btn btn-success d-none">Accept Order</button>
        <button id="declineOrderBtn" class="btn btn-danger d-none">Decline Order</button>
        <button id="shipOrderBtn" class="btn btn-success d-none">Dispatch</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
`;

  document.body.insertAdjacentHTML("beforeend", modalHTML);

  // Load Orders
  function loadSellerOrders() {
    fetch("sellerdash/fetch_orders.php")
      .then((response) => response.json())
      .then((data) => {
        const orders = data.orders;

        newOrdersCol.innerHTML = generateOrderHTML(
          orders.new, "New"
        );
        acceptedOrdersCol.innerHTML = generateOrderHTML(
          orders.accepted,
          "Accepted"
        );
        shippedOrdersCol.innerHTML = generateOrderHTML(
          orders.shipped,
          "Shipped"
        );
        completedOrdersCol.innerHTML = generateOrderHTML(
          orders.completed,
          "Completed"
        );
      })
      .catch((err) => {
        newOrdersCol.innerHTML =
          acceptedOrdersCol.innerHTML =
          shippedOrdersCol.innerHTML =
          completedOrdersCol.innerHTML =
            "<p class='text-danger'>Failed to load orders.</p>";
        console.error(err);
      });
  }

  function generateOrderHTML(orders, title) {
    if (!orders.length)
      return `<h4>${title}</h4><br><p style="color: #fc8c06">No orders found.</p>`;
    let html = `<h4>${title}</h4> <br>`;
    orders.forEach((order) => {
      html += `
            <div class="card mb-2 p-3 view-details" data-orderid="${order.orderID}" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#orderModal">
                <p><strong>Order #:</strong> ${order.orderID}</p>
                <p><strong>Date:</strong> ${order.orderDate}</p>
            </div>`;
    });
    return html;
  }

  // Hook into menu nav click if needed
  document.querySelectorAll(".sidebar a[data-section]").forEach((link) => {
    link.addEventListener("click", function () {
      const section = this.getAttribute("data-section");

      if (section === "orders") {
        loadSellerOrders();
      }
    });
  });

  // Show modal when a "View Details" button is clicked
  document.addEventListener("click", function (e) {
    const viewCard = e.target.closest(".view-details");
    if (viewCard) {
      const orderID = viewCard.getAttribute("data-orderid");

      console.log("Fetching details for orderID:", orderID); // Debugging line

      fetch(`sellerdash/get_order_details.php?orderID=${orderID}`)
        .then((res) => res.json())
        .then((data) => {
          console.log("Order details data:", data); // Log response to check if it's correct

          if (data.error) {
            console.error("Error fetching order details:", data.error);
            alert(data.error || "Failed to load order details.");
            return;
          }

          const modalBody = document.getElementById("orderModalBody");
          const acceptBtn = document.getElementById("acceptOrderBtn");
          const shipBtn = document.getElementById("shipOrderBtn");
          const declineBtn = document.getElementById("declineOrderBtn");

          // Build item rows
          let itemRows = "";
          data.items.forEach((item) => {
            itemRows += `
                            <tr>
                                <td>${item.productID}</td>
                                <td>${item.pName}</td>
                                <td>${item.quantity}</td>
                                <td>R${parseFloat(item.price).toFixed(2)}</td>
                                <td>R${parseFloat(item.subtotal).toFixed(
                                  2
                                )}</td>
                            </tr>`;
          });

          modalBody.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-bordered mt-3">
                        <tbody>
                            <tr><th>Order #</th><td>${data.orderID}</td></tr>
                            <tr><th>Buyer</th><td>${data.buyerName}</td></tr>
                            <tr><th>Shipping</th><td>${data.shippingAddress.replace(
                            /\n/g,
                            "<br>"
                            )}</td></tr>
                            <tr><th>Status</th><td>${data.orderStatus}</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover mt-3">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemRows}
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <tr><th>Total Amount</th><td>R${parseFloat(
                            data.totalAmount
                            ).toFixed(2)}</td></tr>
                            <tr><th>Delivery Fee</th><td>R${parseFloat(
                            data.deliveryFee
                            ).toFixed(2)}</td></tr>
                            <tr><th>Grand Total</th><td><strong>R${parseFloat(
                            data.grandTotal
                            ).toFixed(2)}</strong></td></tr>
                        </tbody>
                    </table>
                </div>
            `;

          // Show or hide the accept/ decline button based on the order status
          if (data.orderStatus.toLowerCase() === "paid") {
            acceptBtn.classList.remove("d-none");
            acceptBtn.onclick = function () {
              fetch("sellerdash/accept_order.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ orderID: data.orderID }),
              })
                .then((res) => res.json())
                .then((result) => {
                  if (result.success) {
                    const modal = bootstrap.Modal.getInstance(
                      document.getElementById("orderModal")
                    );
                    modal.hide();
                    loadSellerOrders(); // Reload orders
                  }
                });
            };

            declineBtn.classList.remove("d-none");
            declineBtn.onclick = function () {
              if (confirm("Are you sure you want to decline this order?")) {
                const formData = new FormData();
                formData.append("orderID", data.orderID);

                fetch("assets/functions/cancel_order.php", {
                  method: "POST",
                  body: formData,
                })
                  .then((res) => {
                    if (res.redirected) {
                      // PHP redirects upon success
                      const modal = bootstrap.Modal.getInstance(
                        document.getElementById("orderModal")
                      );
                      modal.hide();
                      loadSellerOrders(); // Reload orders
                    } else {
                      alert("Failed to decline the order.");
                    }
                  })
                  .catch((err) => {
                    console.error("Decline error:", err);
                    alert("An error occurred while declining the order.");
                  });
              }
            };
          } else {
            acceptBtn.classList.add("d-none");
            declineBtn.classList.add("d-none");
          }

          // Show "Mark as Shipped" button for orders that are accepted (or in progress)
          if (data.orderStatus.toLowerCase() === "accepted") {
            shipBtn.classList.remove("d-none");
            shipBtn.onclick = function () {
              fetch("sellerdash/dispatch_order.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ orderID: data.orderID }),
              })
                .then((res) => res.json())
                .then((result) => {
                  if (result.success) {
                    const modal = bootstrap.Modal.getInstance(
                      document.getElementById("orderModal")
                    );
                    modal.hide();
                    loadSellerOrders(); // Reload orders
                  } else {
                    alert("Failed to mark order as shipped.");
                  }
                })
                .catch((err) =>
                  console.error("Error marking as shipped:", err)
                );
            };
          } else {
            shipBtn.classList.add("d-none");
          }
        })
        .catch((err) => {
          console.error("Failed to fetch order details", err);
          alert("Failed to load order details.");
        });
    }
  });
});
