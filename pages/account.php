<?php
$pageName = 'buy';
require_once "db.php";
session_start();

// Get buyerID
$isBuyer = false;

if (isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];

    $checkBuyer = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
    $checkBuyer->bind_param("i", $_SESSION['userID']);
    $checkBuyer->execute();
    $buyerResult = $checkBuyer->get_result();
    $isBuyer = $buyerResult->num_rows > 0;

    $checkSeller = $conn->prepare("SELECT sellerID FROM sellers WHERE userID = ?");
    $checkSeller->bind_param("i", $_SESSION['userID']);
    $checkSeller->execute();
    $sellerResult = $checkSeller->get_result();
    $isSeller = $sellerResult->num_rows > 0;

    $page = isset($_GET['page']) ? $_GET['page'] : 'options';

    if ($page == 'buyer' && $isBuyer) {
        header("Location: account.php?page=orders");
        exit;
    }

} elseif (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}
?>


<!------------------------------------------------------------------------------------------------------------------------------>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Account</title>

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">

    <!--Stylesheets-->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/styleaccount.css">

    <style>

    body {
        background-image: url('assets/images/orangepinkbackground.jpg');
        background-repeat: repeat;
        background-size: cover; 
        background-color: #FFFCFA;
        background-attachment: fixed;
    }

    </style>

</head>

<body>
    <!-- Navigation -->
    <?php include 'nav.php'; ?> 
  

<!-- Sidebar Menu Toggle Button (only visible on mobile) -->
        <button class="btn btn-primary d-md-none m-2 mt-4" id="toggleSidebar">
            ☰ Menu
        </button>

   
    <div class="cbody d-flex">
        <!-- Sidebar -->
        <div class="sidebar d-none d-md-block" id="sidebarMenu">
            <?php if (!$isBuyer || !$isSeller): ?>
                <h5 class="sidebar-head">🧩 Setup</i></h5>
                <?php if (!$isBuyer): ?>
                    <a href="javascript:void(0)" class="account-link" onclick="loadContent('buyer')">Become a Buyer</a>
                <?php endif;
                ?>
                <?php if (!$isSeller): ?>
                    <a href="javascript:void(0)" class="account-link" onclick="loadContent('seller')">Become a Seller</a>
                <?php endif; ?>


            <?php endif; ?>

            <br>
            <h5 class="sidebar-head">👤 Profile</i></h5>
            <a href="javascript:void(0)" class="account-link" onclick="loadContent('profile')">Personal Details</a>
            <a href="javascript:void(0)" class="account-link" onclick="loadContent('security')">Security Settings</a>


            <?php if ($isBuyer): ?>
                <a href="javascript:void(0)" class="account-link" onclick="loadContent('address')">Address Details</a>
                <br>
                <h5 class="sidebar-head">📊 Orders</h5>
                <a href="javascript:void(0)" class="account-link" onclick="loadContent('orders')">Orders</a>
                <a href="javascript:void(0)" class="account-link" onclick="loadContent('reviews')">Product Reviews</a>
            <?php endif; ?>

            <br>
            <h5 class="sidebar-head">❓ Support</i></h5>
            <a href="javascript:void(0)" class="account-link" onclick="loadContent('feedback')">Submit Feedback</a>
        </div>

        <!-- Content Area for Dynamic Content -->
        <div class="content-wrapper ">
            <div id="dynamic-content" class="mt-3">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
   


    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function loadContent(page) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'account/load_account.php?page=' + page, true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById('dynamic-content').innerHTML = xhr.responseText;

                    // Optional: Auto-close sidebar on mobile
                    if (window.innerWidth < 768) {
                        const sidebar = document.getElementById('sidebarMenu');
                        sidebar.classList.add('d-none');
                        sidebar.classList.remove('d-block');
                    }
                }
            };
            xhr.send();
        }

    </script>

    <script>
        // Load the requested content from the URL on page load
        window.addEventListener('DOMContentLoaded', function () {
            const page = '<?= $page ?>';
            loadContent(page);
        });
    </script>


            <?php if (isset($_GET['status']) && $_GET['status'] === 'success' && isset($_GET['message'])): ?>
                <div id="successPopup" style="
                    display: none;
                    position: fixed;
                    top: 20%;
                    left: 50%;
                    transform: translateX(-50%);
                    background-color:rgb(213, 90, 90);
                    color: white;
                    padding: 20px 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.2);
                    z-index: 1000;
                ">
                    <span id="popupMessage"><?php echo htmlspecialchars($_GET['message']); ?></span>
                </div>

        <script>
            // Show popup if message is present
            const params = new URLSearchParams(window.location.search);
            const status = params.get('status');
            const message = params.get('message');

            if (status === 'success' && message) {
                const popup = document.getElementById('successPopup');
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