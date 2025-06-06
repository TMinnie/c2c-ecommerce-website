<?php
$pageName = 'sell';

session_start();
include 'db.php';

$isSeller = false;

// Ensure user is logged in
if (!isset($_SESSION['userID'])) {
  header("Location: login.php");
  exit;
}

$userID = $_SESSION['userID'];

// Check if the user's seller status is 'approved'
$checkSeller = $conn->prepare("SELECT * FROM sellers WHERE userID = ? ");
$checkSeller->bind_param("i", $userID);
$checkSeller->execute();
$sellerResult = $checkSeller->get_result();
$isSeller = $sellerResult->num_rows > 0;

if ($isSeller) {

  $sellerData = $sellerResult->fetch_assoc();

  // Check if the seller's account is approved
  if ($sellerData['status'] == 'pending') {
    echo "<link rel='stylesheet' href='assets/css/theme.css' />";
    echo "<div class='center-message'>";
    echo "<h3 style='padding-bottom: 32px;'>Your seller account is awaiting approval by an admin.</h3>";
    echo "<button class='btn-get-started' onclick=\"window.location.href='buyerdash.php'\">Go to Buyer Dashboard</button>";
    echo "</div>";
    exit;
  } elseif ($sellerData['status'] == 'rejected') {
    echo "<link rel='stylesheet' href='assets/css/theme.css' />";
    echo "<div class='center-message'>";
    echo "<h3 style='padding-bottom: 32px;'>Your seller account has been rejected.</h3>";
    echo "<button class='btn-get-started' onclick=\"window.location.href='account.php?page=support'\">Learn More</button>";
    echo "<button class='btn-get-started' onclick=\"window.location.href='resubmit_seller.php'\">Update & Resubmit</button>";
    echo "<button class='btn-get-started' onclick=\"window.location.href='buyerdash.php'\">Go to Buyer Dashboard</button>";
    echo "</div>";
    exit;
  }

  $checkSeller->close();

  $businessName = $sellerData['businessName'];
  $rating = $sellerData['rating'];

  // Set session for sellerID if not already set
  if (!isset($_SESSION['sellerID'])) {
    $_SESSION['sellerID'] = $sellerData['sellerID'];
  }

  $sellerID = $_SESSION['sellerID'];


} else {
  $isSeller = false;
}
?>

<!-------------------------------------------------------------------------------------------------------------------------->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Seller Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/theme.css" />
  <link rel="stylesheet" href="assets/css/stylesellerdash.css" />
  <link rel="stylesheet" href="assets/css/styleaccount.css" />

  <style>
    body {
      background-image: url('assets/images/bg.jpg');
      background-repeat: no-repeat;
      background-size: cover;
      background-attachment: fixed;

    }
  </style>
</head>

<body>
  <!-- Navigation -->
  <?php include 'nav.php'; ?>


  <?php if ($isSeller && $sellerData['status'] !== 'resubmit'): ?>

    <!-- Sidebar Menu Toggle Button (only visible on mobile) -->
    <button class="btn btn-primary d-md-none m-2 mt-4" id="toggleSidebar">
      ☰ Menu
    </button>

    <div class="cbody d-flex">
      <!-- Sidebar -->
      <div class="sidebar d-none d-md-block" id="sidebarMenu">
        <a href="#" class="account-link active" data-section="dashboard">📊 Dashboard</a>
        <a href="#" class="account-link" data-section="orders">📦 Orders</a>
        <a href="#" class="account-link" data-section="products">🛍️ My Products</a>
        <a href="#" class="account-link" data-section="reviews">✍️ Reviews</a>
        <a href="#" class="account-link" data-section="settings">⚙️ Store Settings</a>
        <a href="logout.php">🔓 Logout</a>
      </div>


      <!-- Main Content -->
      <div class="content-wrapper">
        <div id="dashboardContent">

          <div id="dashboardSection" class="mt-4">

            <h1 style="color:white">Welcome back, <?php echo $businessName ?>!</h1>

            <br>
            <div class="row g-3 mb-4 d-flex align-items-stretch">
              <!-- Card 1: Low Stock -->
              <div class="col-12 col-sm-6 col-md-3 d-flex">
                <div class="card shadow  w-100 h-100" style="background-color: white;">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Low stock warning</span>
                  </div>
                  <div class="card-body d-flex flex-column">
                    <h4 class="mb-2" id="lowStockCount"></h4>
                    <ul id="lowStockList" class="mb-0 small" style="max-height: 100px; overflow-y: auto; list-style-type: none; padding-left: 0;"></ul>
                    <div class="mt-auto align-self-end" style="font-size: 30px;">🔔</div>
                  </div>
                </div>
              </div>

              <!-- Card 2: Out of Stock -->
              <div class="col-12 col-sm-6 col-md-3 d-flex">
                <div class="card shadow  w-100 h-100" style="background-color: white;">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Out of Stock</span>
                  </div>
                  <div class="card-body d-flex flex-column">
                    <h4 class="mb-2" id="outOfStockCount"></h4>
                    <ul id="outOfStockList" class="mb-0 small" style="max-height: 100px; overflow-y: auto; list-style-type: none; padding-left: 0;"></ul>
                    <div class="mt-auto align-self-end" style="font-size: 30px;">❗</div>
                  </div>
                </div>
              </div>


              <!-- Card 3 -->
              <div class="col-12 col-sm-6 col-md-3 d-flex">
                <div class="card shadow  w-100 h-100" style="background-color: white;">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <span>New Orders Total</span>
                  </div>
                  <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="d-flex">
                      <div class="me-4">
                        <small></small><br>
                        <h4 class="mb-0" id="orders">0</h4>
                      </div>
                    </div>
                    <!--<i class="fa-solid fa-box fa-2x opacity-50 mt-auto align-self-end" style="color: brown;"></i>-->
                    <div class="mt-auto align-self-end" style="font-size: 30px;">📦</div>
                  </div>
                </div>
              </div>

              <!-- Card 4 -->
              <div class="col-12 col-sm-6 col-md-3 d-flex">
                <a class="text-white" href="statshub.php?name=<?php echo urlencode($businessName); ?>">
                  <div class="card shadow  w-100 h-100" style="background-color: white;">
                    <div class="card-body d-flex justify-content-between align-items-center">
                      <div class="d-flex">
                        <div class="me-4">
                          <small></small><br>
                          <h5 class="mb-2 text-center">
                            See More Seller Stats
                          </h5>
                        </div>
                      </div>
                      <i class="fa-regular fa-circle-right fa-2x opacity-25"></i>
                    </div>
                  </div>
              </div>
              </a>
            </div>
          </div>

          <div id="productsSection" class="d-none"></div>

          <div id="ordersSection" class="d-none">
            <div class="card shadow-sm p-4 mt-4 mb-5" style="background-color: #F1F1F1;">
              <div class="row">
                <div class="col-md-3 mb-4" id="newOrders"></div>
                <div class="col-md-3 mb-4" id="acceptedOrders"></div>
                <div class="col-md-3 mb-4" id="shippedOrders"></div>
                <div class="col-md-3 mb-4" id="completedOrders"></div>
              </div>
            </div>
          </div>

          <div id="reviewsSection" class="d-none"></div>
          <div id="settingsSection" class="d-none"></div>
        </div>

        <!-- Image Zoom Modal -->
        <div id="imageZoomModal" class="modal"
          style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background: rgba(0,0,0,0.8); justify-content:center; align-items:center; z-index:1000;">
          <span id="closeZoom"
            style="position:absolute; top:20px; right:30px; font-size:30px; color:white; cursor:pointer;">&times;</span>
          <img id="zoomedImage" src="" alt="Zoomed Product"
            style="max-width:90vw; max-height:90vh; border-radius:10px; box-shadow:0 0 20px rgba(255,255,255,0.7);" />
        </div>

      </div>
    </div>



    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
      crossorigin="anonymous"></script>

    <!-- Sidebar Navigation Script -->
    <script>
      document.querySelectorAll('.sidebar a[data-section]').forEach(link => {
        link.addEventListener('click', function (e) {
          e.preventDefault();

          // Toggle 'active' class
          document.querySelectorAll('.sidebar a').forEach(l => l.classList.remove('active'));
          this.classList.add('active');

          const section = this.getAttribute('data-section');

          document.querySelectorAll('#dashboardContent > div').forEach(div => {
            div.classList.add('d-none');
          });

          const selectedSection = document.getElementById(section + 'Section');
          if (selectedSection) {
            selectedSection.classList.remove('d-none');

          }
        });
      });

    </script>

    <!-- Seller Dashboard Scripts -->
    <script src="sellerdash/js/fetch_products.js"></script>
    <script src="sellerdash/js/add_products.js"></script>
    <script src="sellerdash/js/fetch_orders.js"></script>
    <script src="sellerdash/js/fetch_seller_transactions.js"></script>
    <script src="sellerdash/js/fetch_seller_reviews.js"></script>
    <script src="sellerdash/js/fetch_store_info.js"></script>


  <?php else: ?>


    <!-- Seller Registration Form -->
    <div class="container-fluid px-3">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 mt-4 mb-5">
          <div class="card shadow-sm" style="background-color: #F1F1F1;">
            <div class="card-body">

              <div id="sellerSignupSection">
                <form class="modal-content" action="process_seller_signup.php" method="POST"
                  enctype="multipart/form-data">
                  <div class="modal-header text-center">
                    <h3 class="modal-title  mt-2 ">Become a Seller on TukoCart</h3>
                  </div>
                  <hr>

                  <div class="card p-3 mb-4 shadow-sm border-0 bg-light rounded-4">
                    <div class="card-body p-2">
                      <p class="mb-3">
                        <strong>Turn your passion into profit</strong> by joining <strong>TukoCart</strong> as a trusted
                        seller.
                      </p>
                      <ul class="list-unstyled mb-3">
                        <li class="mb-3"><i class="fa-regular fa-square-check"></i> <strong>Easily list</strong> and
                          manage your products</li>
                        <li class="mb-3"><i class="fa-regular fa-square-check"></i> <strong>Track & fulfill</strong>
                          orders with powerful tools</li>
                        <li class="mb-3"><i class="fa-regular fa-square-check"></i> <strong>Grow your brand</strong> with
                          our full support</li>
                      </ul>
                      <p class="mb-0">
                        <strong style="color:#f9963a">Ready to start selling?</strong><br> Fill out the registration form
                        below and join a marketplace that champions local entrepreneurs.
                      </p>
                    </div>
                  </div>

                  <!-- Form Fields -->
                  <div class="modal-body px-1">

                    <!-- Business Info -->
                    <div class="row">
                      <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Business Name</label>
                        <input type="text" class="form-control" name="businessName"
                          value="<?php echo htmlspecialchars($sellerData['businessName'] ?? ''); ?>" required>
                      </div>
                      <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Upload Business Image <span class="text-muted fst-italic">-
                            Optional</span></label>
                        <input type="file" class="form-control" name="imageFile" accept=".jpeg, .jpg, .png">
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Business Description <span class="text-muted fst-italic">-
                          Optional</span></label>
                      <textarea class="form-control" name="businessDescript" rows="3"
                        maxlength="244"><?php echo htmlspecialchars($sellerData['businessDescript'] ?? ''); ?></textarea>
                    </div>

                    <!-- Payment Info -->
                    <div class="mb-3">
                      <label class="form-label">Bank Account Number</label>
                      <input type="text" class="form-control" name="payDetails"
                        value="<?php echo htmlspecialchars($sellerData['paymentDetails'] ?? ''); ?>" required>
                    </div>

                    <!-- Address Info -->
                    <div class="row">
                      <div class="col-12 col-md-6 mb-3">
                        <label class="form-label">Pickup Address</label>
                        <input type="text" class="form-control" name="pickupAddress"
                          value="<?php echo htmlspecialchars($sellerData['pickupAddress'] ?? ''); ?>" required>
                      </div>
                      <div class="col-6 col-md-4 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="city"
                          value="<?php echo htmlspecialchars($sellerData['city'] ?? ''); ?>" required>
                      </div>
                      <div class="col-6 col-md-2 mb-3">
                        <label class="form-label">Postal Code</label>
                        <input type="text" class="form-control" name="postalCode"
                          value="<?php echo htmlspecialchars($sellerData['postalCode'] ?? ''); ?>" required>
                      </div>
                    </div>

                    <!-- Upload Documents -->
                    <div class="mb-3">
                      <label class="form-label">Government Issued Identification</label>
                      <p class="text-muted small">(Clear photo/scan of Passport, National ID, or Driver's License)</p>
                      <input type="file" class="form-control" name="registrationDoc[]" accept=".pdf,.jpg,.jpeg,.png">
                    </div>

                    <div class="mb-4">
                      <label class="form-label">Proof of Address</label>
                      <p class="text-muted small">(Utility bill, bank statement, or lease agreement; dated within 3
                        months)</p>
                      <input type="file" class="form-control" name="registrationDoc[]" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                  </div>

                  <!-- Submit -->
                  <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-primary w-100">Submit Application for Approval</button>
                  </div>
                  <br>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error Popup -->
      <?php if (isset($_GET['status']) && $_GET['status'] === 'error' && isset($_GET['message'])): ?>
        <div id="errorPopup" style="
    display: none;
    position: fixed;
    top: 20%;
    left: 50%;
    transform: translateX(-50%);
    background-color: rgb(219, 93, 93);
    color: white;
    padding: 20px 30px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
    z-index: 1000;">
          <span id="popupMessage"><?php echo htmlspecialchars($_GET['message']); ?></span>
        </div>
      <?php endif; ?>
    </div>


    <script>
      // Show popup if message is present
      const params = new URLSearchParams(window.location.search);
      const status = params.get('status');
      const message = params.get('message');

      if (status === 'error' && message) {
        const popup = document.getElementById('errorPopup');
        const popupMessage = document.getElementById('popupMessage');
        popupMessage.textContent = decodeURIComponent(message);
        popup.style.display = 'block';

        // Hide popup after 3 seconds
        setTimeout(() => {
          popup.style.display = 'none';
        }, 3000);

        // Remove status and message from URL
        const url = new URL(window.location);
        url.searchParams.delete('status');
        url.searchParams.delete('message');
        history.replaceState({}, document.title, url.pathname + url.search);
      }
    </script>

  <?php endif; ?>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      fetch("sellerdash/dash_stats.php")
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            console.error(data.error);
            return;
          }

          // Low stock product list
          const lowList = document.getElementById("lowStockList");
          lowList.innerHTML = "";
          if (data.lowStockProducts.length === 0) {
            lowList.innerHTML = "<li class='text-white-50 fst-italic'>No low stock items</li>";
          } else {
            data.lowStockProducts.forEach(p => {
              const li = document.createElement("li");
              li.textContent = p;
              lowList.appendChild(li);
            });
          }

          // Out of stock product list
          const outList = document.getElementById("outOfStockList");
          outList.innerHTML = "";
          if (data.outOfStockProducts.length === 0) {
            outList.innerHTML = "<li class='text-white-50 fst-italic'>No out of stock items</li>";
          } else {
            data.outOfStockProducts.forEach(p => {
              const li = document.createElement("li");
              li.textContent = p;
              outList.appendChild(li);
            });
          }

          // Optional: still show new orders
          const ordersEl = document.getElementById("orders");
          if (ordersEl) ordersEl.textContent = data.newOrders;

        })
        .catch(error => console.error("Dashboard stats error:", error));
    });
  </script>


  <script>
    document.getElementById('toggleSidebar').addEventListener('click', function () {
      const sidebar = document.getElementById('sidebarMenu');
      sidebar.classList.toggle('d-block');
      sidebar.classList.toggle('d-none');
    });

    // Optional: Hide sidebar when clicking a sidebar link (on mobile)
    document.querySelectorAll('.account-link').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth < 768) {
          const sidebar = document.getElementById('sidebarMenu');
          sidebar.classList.remove('d-block');
          sidebar.classList.add('d-none');
        }
      });
    });

  </script>

</body>

</html>