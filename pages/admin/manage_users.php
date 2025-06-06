<?php
// Database connection
require '../db.php';

if (isset($_GET['userID'])) {
    $userID = $_GET['userID'];

    // Example: fetch user info
    $stmt = $conn->prepare("SELECT * FROM users WHERE userID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Update User
    if (isset($_POST['userID']) && isset($_POST['uFirst'])) {
        $id = $_POST['userID'];
        $first = $_POST['uFirst'];
        $last = $_POST['uLast'];
        $email = $_POST['email'];
        $password = $_POST['password'] ?? '';

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET uFirst=?, uLast=?, email=?, uPassword=? WHERE userID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $first, $last, $email, $hashedPassword, $id);
        } else {
            $sql = "UPDATE users SET uFirst=?, uLast=?, email=? WHERE userID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $first, $last, $email, $id);
        }

        $stmt->execute();
        exit;
    }


    // 2. Fetch Users for Display
    $search = $_POST['search'] ?? '';
    $role = $_POST['role'] ?? '';
    $userID = $_POST['userID'] ?? '';

    $sql = "SELECT * FROM users WHERE (uFirst LIKE ? OR uLast LIKE ? OR email LIKE ?)";
    $searchWildcard = "%$search%";
    $params = [$searchWildcard, $searchWildcard, $searchWildcard];
    $types = "sss";


    if (!empty($role)) {
        $sql .= " AND role = ?";
        $params[] = $role;
        $types .= "s";
    }

    if (!empty($userID)) {
        $sql .= " AND userID = ?";
        $params[] = $userID;
        $types .= "i";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    // Output only the table rows
    while ($row = $result->fetch_assoc()):

        // Check if user is a buyer
        $buyerCheck = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
        $buyerCheck->bind_param("i", $row['userID']);
        $buyerCheck->execute();
        $buyerResult = $buyerCheck->get_result();
        $hasBuyer = $buyerResult->num_rows > 0;
        $buyerCheck->close();

        // Check if user is a seller
        $sellerCheck = $conn->prepare("SELECT sellerID FROM sellers WHERE userID = ?");
        $sellerCheck->bind_param("i", $row['userID']);
        $sellerCheck->execute();
        $sellerResult = $sellerCheck->get_result();
        $hasSeller = $sellerResult->num_rows > 0;
        $sellerCheck->close();

        ?>

        <tr data-id="<?= $row['userID'] ?>">
            <td class="userID"><?= $row['userID'] ?></td>
            <td class="uFirst"><?= $row['uFirst'] ?></td>
            <td class="uLast"><?= $row['uLast'] ?></td>
            <td class="email"><?= $row['email'] ?></td>

            <td class="role">
                <?php if ($hasBuyer): ?>
                    <a href="manage_buyers.php?userID=<?= $row['userID'] ?>" class="btn btn-primary btn-sm mb-1">Buyer</a><br>
                <?php endif; ?>

                <?php if ($hasSeller): ?>
                    <a href="manage_sellers.php?userID=<?= $row['userID'] ?>" class="btn btn-success btn-sm">Seller</a>
                <?php endif; ?>

                <?php if (!$hasBuyer && !$hasSeller): ?>
                    <span class="text-muted">User</span>
                <?php endif; ?>
            </td>


            <td class="status" data-status="<?= $row['status'] ?>">
                <?= ucfirst($row['status']) ?>
            </td>
            <td>
                <button class="btn btn-sm btn-warning editBtn">Edit</button>
            </td>
        </tr>
    <?php
    endwhile;
    exit;
}

?>
<!----------------------------------------------------------------------------------------------------------------------------->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navigation -->
    <?php include 'admin_nav.php'; ?>

    <div class="d-flex">
        <!-- Sidebar with links -->
        <?php include 'sidebar.php'; ?>

        <div class="content-wrapper">
            <div id="dynamic-content" class="mt-4">
                <div class="container mt-4">
                    <h3>Manage Users</h3>
                    <hr>
                    <div class="card p-3 mb-4 shadow-sm rounded-3">
                        <!--Input-->
                        <div class="input-group">
                            <input type="text" id="searchBox" class="form-control"
                                placeholder="Search by name or email...">

                            <select id="roleFilter" class="form-select" style="max-width: 150px;">
                                <option value="">All Roles</option>
                                <option value="buyer">Buyer</option>
                                <option value="seller">Seller</option>
                                <option value="admin">Admin</option>
                            </select>

                            <button id="searchBtn" class="btn btn-outline-secondary" type="button">Search</button>
                        </div>
                    </div>
                    <!--Table-->
                    <table class="table table-bordered table-hover" id="userTableContainer">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- AJAX results will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editUserForm">
                    <div class="modal-header">
                        <h5>Edit User</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="userID" id="editUserID">
                        <label for="uFirst" class="mb-1">First Name</label>
                        <input type="text" name="uFirst" class="form-control mb-2" id="editUFirst"
                            placeholder="First Name">
                        <label for="uLast" class="mb-1">Last Name</label>
                        <input type="text" name="uLast" class="form-control mb-2" id="editULast"
                            placeholder="Last Name">
                        <label for="email" class="mb-1">Email</label>
                        <input type="email" name="email" class="form-control mb-2" id="editEmail" placeholder="Email">
                        <label for="password" class="mb-1">Change Password</label>
                        <input type="password" name="password" class="form-control mb-2" id="editPassword"
                            placeholder="Leave blank to keep current">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--bootstrap &  jQuery-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function () {

            // Load users
            function loadUsers(query = '', role = '', userID = '') {
                $.post('', { search: query, role: role, userID: userID }, function (data) {
                    $('#userTableContainer tbody').html(data);
                });

            }

            // Load specific user if userID is present in query params
            const urlParams = new URLSearchParams(window.location.search);
            const userIDFromURL = urlParams.get('userID');
            if (userIDFromURL) {
                loadUsers('', '', userIDFromURL);
            } else {
                loadUsers(); // default load
            }

            // Search button click
            $('#searchBtn').click(function () {
                const searchText = $('#searchBox').val();
                const selectedRole = $('#roleFilter').val();
                loadUsers(searchText, selectedRole);
            });

            $('#searchBox').on('keypress', function (e) {
                if (e.which === 13) {
                    $('#searchBtn').click();
                }
            });


            // Edit user
            $(document).on('click', '.editBtn', function () {
                let row = $(this).closest('tr');
                $('#editUserID').val(row.data('id'));
                $('#editUFirst').val(row.find('.uFirst').text());
                $('#editULast').val(row.find('.uLast').text());
                $('#editEmail').val(row.find('.email').text());
                $('#editUserModal').modal('show');
            });

            // Save edited user data
            $('#editUserForm').submit(function (e) {
                e.preventDefault();

                const formData = {
                    userID: $('#editUserID').val(),
                    uFirst: $('#editUFirst').val(),
                    uLast: $('#editULast').val(),
                    email: $('#editEmail').val(),
                };

                const newPassword = $('#editPassword').val();
                if (newPassword.trim() !== '') {
                    formData.password = newPassword;
                }

                // Show confirmation dialog
                const confirmEdit = confirm('Are you sure you want to save these changes?');

                if (confirmEdit) {
                    // Proceed with the update
                    $.post('', formData, function () {
                        $('#editUserModal').modal('hide');
                        alert('User details updated successfully!');
                        loadUsers();
                    });
                } else {
                    // Cancel
                    return false;
                }

            });

            // Toggle user status
            $(document).on('click', '.toggleStatusBtn', function () {
                const userID = $(this).data('id');
                $.post('', { toggleStatusID: userID }, function () {
                    loadUsers();
                });
            });

        });

    </script>

</body>

</html>