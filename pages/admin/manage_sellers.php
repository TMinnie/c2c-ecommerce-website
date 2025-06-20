<?php
session_start();
include '../db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
// Handle seller update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sellerID'])) {
    $sellerID = $_POST['sellerID'];
    $businessName = $_POST['businessName'];
    $pickupAddress = $_POST['pickupAddress'];
    $city = $_POST['city'];
    $postalcode = $_POST['postalcode'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE sellers SET businessName=?, pickupAddress=?, city=?, postalcode=?, status=? WHERE sellerID=?");
    $stmt->bind_param("sssssi", $businessName, $pickupAddress, $city, $postalcode, $status, $sellerID);

    if ($stmt->execute()) {
        echo "<script>alert('Seller updated successfully');</script>";
    } else {
        echo "<script>alert('Failed to update seller');</script>";
    }
}


// Handle accept or reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sellerID'], $_POST['action'])) {

    $sellerID = (int) $_POST['sellerID'];
    $action = $_POST['action'] === 'accept' ? 'accepted' : 'rejected';
    $productStatus = ($action === 'accepted') ? 'active' : 'inactive';

    $stmt = $conn->prepare("UPDATE sellers SET status = ? WHERE sellerID = ?");
    $stmt->bind_param("si", $action, $sellerID);
    $stmt->execute();

     // Update products under this seller
    $stmt2 = $conn->prepare("UPDATE products SET status = ? WHERE sellerID = ?");
    $stmt2->bind_param("si", $productStatus, $sellerID);
    $stmt2->execute();

    // Get userID associated with this seller (for both actions)
    $stmt = $conn->prepare("SELECT userID FROM sellers WHERE sellerID = ?");
    $stmt->bind_param("i", $sellerID);
    $stmt->execute();
    $stmt->bind_result($userID);
    $stmt->fetch();
    $stmt->close();

    if (!empty($userID)) {
        $newRole = ($action === 'accepted') ? 'seller' : 'buyer';
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE userID = ?");
        $stmt->bind_param("si", $newRole, $userID);
        $stmt->execute();
        $stmt->close();
    }
    $_SESSION['message'] = "Seller has been " . $action . ".";

  
    // Send seller notification email that profile is accepted or rejected
    $query = "SELECT u.email, u.uFirst, u.uLast 
            FROM users u
            INNER JOIN sellers s ON u.userID = s.userID
            WHERE s.sellerID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sellerID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $toEmail = $row['email'];
        $toName = $row['uFirst'] . ' ' . $row['uLast'];
    } else {
        echo "User not found.";
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'tukocart@gmail.com';       
        $mail->Password = 'zhoq uxvr hptz ojzk';           
        $mail->SMTPSecure = 'tls';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];
        $mail->Port = 587;

        // Sender & recipient
        $mail->setFrom('tukocart@gmail.com', 'TukoCart Admin');
        $mail->addAddress($toEmail, $toName);

        // Content
        if ($action === 'accepted') {
            $mail->isHTML(true);
            $mail->Subject = 'Your Seller Profile is Activated';
            $mail->Body    = "
                <h3>Hi $toName,</h3>
                <p>Your seller profile has been <strong>activated</strong>.</p>
                <p>You can now log in and start selling.</p>
                <br><p>Best regards,<br>TukoCart Team</p>
        ";
        } else {
            $mail->isHTML(true);
            $mail->Subject = 'Your Seller Profile is Rejected';
            $mail->Body    = "
                <h3>Hi $toName,</h3>
                <p>Your seller profile has been <strong>rejected</strong> or <strong>deactivated</strong>.</p>
                <p>If you have any questions, please contact us.</p>
                <br><p>Best regards,<br>TukoCart Team</p>
            ";
        }

        $mail->send();
        $_SESSION['message'] = "Confirmation email has been sent to $toName ($toEmail)";
         
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
      

    // Redirect back 
    header("Location: manage_sellers.php");
    exit();
}
//---------------------------------------------------------------------------------------------------------------------------------------------->
// Function to render the sellers table based on status
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

function renderSellersTable($conn, $status, $search = '')
{
    if (isset($_GET['userID'])) {
        $userID = intval($_GET['userID']);
        $stmt = $conn->prepare("SELECT * FROM sellers WHERE userID = ?");
        $stmt->bind_param("i", $userID);


    } elseif (isset($_GET['sellerID'])) {
        $sellerID = intval($_GET['sellerID']);
        $stmt = $conn->prepare("SELECT * FROM sellers WHERE sellerID = ?");
        $stmt->bind_param("i", $sellerID);


    } elseif (!empty($search)) {
        if ($status === 'all') {
            $search = "%{$conn->real_escape_string($search)}%";
        $stmt = $conn->prepare("
            SELECT * FROM sellers 
            WHERE (
                businessName LIKE ? OR
                pickupAddress LIKE ? OR
                city LIKE ? OR
                CAST(postalcode AS CHAR) LIKE ? OR
                CAST(sellerID AS CHAR) LIKE ? OR
                CAST(userID AS CHAR) LIKE ?
            )
        ");
        $stmt->bind_param("ssssss", $search, $search, $search, $search, $search, $search);
        $stmt->execute();
         
        } else{
        $search = "%{$conn->real_escape_string($search)}%";
        $stmt = $conn->prepare("
            SELECT * FROM sellers 
            WHERE status = ? 
            AND (
                businessName LIKE ? OR
                pickupAddress LIKE ? OR
                city LIKE ? OR
                CAST(postalcode AS CHAR) LIKE ? OR
                CAST(sellerID AS CHAR) LIKE ? OR
                CAST(userID AS CHAR) LIKE ?
            )
        ");
        $stmt->bind_param("sssssss", $status, $search, $search, $search, $search, $search, $search);

    }

    } else {
        if ($status === 'all') {
            $stmt = $conn->prepare("SELECT * FROM sellers ORDER BY createdAt DESC");

        }
        else{
            $stmt = $conn->prepare("SELECT * FROM sellers WHERE status = ? ORDER BY createdAt DESC");
            $stmt->bind_param("s", $status);

        }
    }

        $stmt->execute();
        $result = $stmt->get_result();


    $html = '';
    if ($result->num_rows === 0) {
        $html .= '<tr><td colspan="8" class="text-center">No sellers found for this status.</td></tr>';
    } else {
        while ($row = $result->fetch_assoc()) {


            $html .= '<tr>
            <td>' . $row['sellerID'] . '</td>
            <td class="userID">
                <a href="manage_users.php?userID=' . $row['userID'] . '">
                    ' . $row['userID'] . '
                </a>
            </td>

            <!-- Image link with modal trigger -->
            
            <td>
                <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal' . $row['sellerID'] . '">
                    View Image
                </a>

                <!-- Modal -->
                <div class="modal fade" id="imageModal' . $row['sellerID'] . '" tabindex="-1" aria-labelledby="imageModalLabel' . $row['sellerID'] . '" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="imageModalLabel' . $row['sellerID'] . '">Seller Logo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="../uploads/' . htmlspecialchars($row['imagePath']) . '" alt="Seller Logo" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            </td>

            <td>' . htmlspecialchars($row['businessName']) . '</td>

            <!-- Address with spacing -->
            <td>' . htmlspecialchars($row['pickupAddress']) . ', ' . htmlspecialchars($row['city']) . ', ' . htmlspecialchars((string) $row['postalcode']) . '</td>
            
            <!-- POA and GIID links -->
            <td><a href="../uploads/' . htmlspecialchars($row['poa']) . '" target="_blank">View Document</a></td>
            <td><a href="../uploads/' . htmlspecialchars($row['giid']) . '" target="_blank">View Document</a></td>
            
            <td>' . htmlspecialchars($row['createdAt']) . '</td>
            <td>';


            if ($row['status'] === 'pending') {
                $html .= '
                <form method="POST" action="manage_sellers.php" style="display:inline;">
                    <input type="hidden" name="sellerID" value="' . $row['sellerID'] . '">
                    <button class="btn btn-success btn-sm" name="action" value="accept"
                    onclick="return confirm(\"Are you sure you want to acccpt this seller application?\");">Accept</button>
                </form>
                <form method="POST" action="manage_sellers.php" style="display:inline;">
                    <input type="hidden" name="sellerID" value="' . $row['sellerID'] . '">
                    <button class="btn btn-danger btn-sm" name="action" value="reject"
                    onclick="return confirm(\"Are you sure you want to reject this seller application?\");">Reject</button>
                </form>';
            } elseif ($row['status'] === 'accepted') {
                $html .= '
                <form method="POST" action="manage_sellers.php" style="display:inline;">
                    <input type="hidden" name="sellerID" value="' . $row['sellerID'] . '">
                    <button class="btn btn-danger btn-sm" name="action" value="reject"
                    onclick="return confirm(\"Are you sure you want to deactivate this seller?\");">Deactivate</button>
                </form>';
            } elseif ($row['status'] === 'rejected') {
                $html .= '
                <form method="POST" action="manage_sellers.php" style="display:inline;">
                    <input type="hidden" name="sellerID" value="' . $row['sellerID'] . '">
                    <button class="btn btn-success btn-sm" name="action" value="accept"
                    onclick="return confirm(\"Are you sure you want to activate this seller?\");">Activate</button>
                </form>';
            }

            

            $html .= '
            
            <button class="btn btn-warning btn-sm editSellerBtn mt-1"
                data-seller-id="' . $row['sellerID'] . '"
                data-business-name="' . htmlspecialchars($row['businessName']) . '"
                data-pickup-address="' . htmlspecialchars($row['pickupAddress']) . '"
                data-city="' . htmlspecialchars($row['city']) . '"
                data-postal-code="' . htmlspecialchars($row['postalcode']) . '"
                data-status="' . $row['status'] . '"
            >
                Edit
            </button>
            </td></tr>';
        }
        return $html;
    }
}
?>

<!-------------------------------------------------------------------------------------------------------------------------->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">

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
                <div class="container mt-4">
                    <h3>Manage Sellers</h3>
                    <hr>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>

                    <!-- Filter and Search Controls -->
                    <div class="card p-3 mb-4 shadow-sm rounded-3">
                        <form method="GET" class="row g-3 align-items-center">
                            <!-- Filter Dropdown -->
                              <div class="d-flex">
                                <label for="status" class="col-form-label fw-semibold col-md-3">Filter by Status:</label>
                                <select id="status" name="status" class="form-select"
                                    onchange="this.form.submit()">
                                    <option value="all" <?= (($_GET['status'] ?? '') === 'all') ? 'selected' : '' ?>>All</option>
                                    <option value="pending" <?= (($_GET['status'] ?? '') === 'pending') ? 'selected' : '' ?>>
                                        Pending</option>
                                    <option value="accepted" <?= (($_GET['status'] ?? '') === 'accepted') ? 'selected' : '' ?>>
                                        Accepted</option>
                                    <option value="rejected" <?= (($_GET['status'] ?? '') === 'rejected') ? 'selected' : '' ?>>
                                        Rejected</option>
                                </select>
                            </div>
                            <div class="d-flex">
                                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control me-2" placeholder="Search by name, address, ID...">
                                <button type="submit" class="btn btn-outline-secondary">Search</button>
                            </div>
                        </form>
                    </div>


                <table class="table table-bordered table-hover mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>Seller ID</th>
                            <th>User ID</th>
                            <th>Logo</th>
                            <th>Business Name</th>
                            <th>Address</th>
                            <th>Proof of Address</th>
                            <th>Gov Issued ID</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                       <?= renderSellersTable($conn, $status, $search); ?>
                    </tbody>
                </table>

                </div>
            </div>
        </div>
    </div>

    <!-- Edit Seller Modal -->
<div class="modal fade" id="editSellerModal" tabindex="-1" aria-labelledby="editSellerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editSellerForm" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editSellerModalLabel">Edit Seller</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="sellerID" id="editSellerID">
          <div class="mb-3">
            <label class="form-label">Business Name</label>
            <input type="text" class="form-control" name="businessName" id="editBusinessName" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Pickup Address</label>
            <input type="text" class="form-control" name="pickupAddress" id="editPickupAddress" required>
          </div>
          <div class="mb-3">
            <label class="form-label">City</label>
            <input type="text" class="form-control" name="city" id="editCity" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Postal Code</label>
            <input type="text" class="form-control" name="postalcode" id="editPostalCode" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" id="editStatus" class="form-select">
              <option value="pending">Pending</option>
              <option value="accepted">Accepted</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Changes</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>


    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script>
document.querySelectorAll('.editSellerBtn').forEach(button => {
    button.addEventListener('click', () => {
        const modal = new bootstrap.Modal(document.getElementById('editSellerModal'));
        document.getElementById('editSellerID').value = button.dataset.sellerId;
        document.getElementById('editBusinessName').value = button.dataset.businessName;
        document.getElementById('editPickupAddress').value = button.dataset.pickupAddress;
        document.getElementById('editCity').value = button.dataset.city;
        document.getElementById('editPostalCode').value = button.dataset.postalCode;
        document.getElementById('editStatus').value = button.dataset.status;
        modal.show();
    });
});
</script>


</body>

</html>