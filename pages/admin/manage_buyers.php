<?php
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Update Buyer
    if (isset($_POST['buyerID'], $_POST['shippingAddress1'], $_POST['shippingAddress2'])) {
        $sql = "UPDATE buyers SET  shippingAddress1=?, shippingAddress2=?, postalCode=? WHERE buyerID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssi",
            $_POST['shippingAddress1'],
            $_POST['shippingAddress2'],
            $_POST['postalCode'],
            $_POST['buyerID']
        );
        $stmt->execute();
        exit;
    }

    // Delete Buyer
    if (isset($_POST['deleteBuyerID'])) {
        $stmt = $conn->prepare("DELETE FROM buyers WHERE buyerID = ?");
        $stmt->bind_param("i", $_POST['deleteBuyerID']);
        $stmt->execute();
        exit;
    }

    // Fetch buyers
    $search = $_POST['search'] ?? '';
    $userID = $_POST['userID'] ?? '';
    $buyerID = $_POST['buyerID'] ?? '';

    $sql = "SELECT * FROM buyers WHERE 1";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND CONCAT(shippingAddress1, ' ', shippingAddress2, postalCode) LIKE ?";
        $params[] = "%$search%";
        $types .= "s";
    }

    if (!empty($userID)) {
        $sql .= " AND userID = ?";
        $params[] = $userID;
        $types .= "i";
    }

    if (!empty($buyerID)) {
        $sql .= " AND buyerID = ?";
        $params[] = $buyerID;
        $types .= "i";
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();


    //Display table
    while ($row = $result->fetch_assoc()):
        ?>
        <tr data-id="<?= $row['buyerID'] ?>">
            <td class="buyerID"><?= $row['buyerID'] ?></td>
            <td class="userID">
                <a href="manage_users.php?userID=<?= $row['userID'] ?>">
                    <?= $row['userID'] ?>
                </a>
            </td>
            <td class="shippingAddress1"><?= $row['shippingAddress1'] ?></td>
            <td class="shippingAddress2"><?= $row['shippingAddress2'] ?></td>
            <td class="postalCode"><?= $row['postalCode'] ?></td>
            <td>
                <button class="btn btn-warning btn-sm editBtn">Edit</button>
                <button class="btn btn-danger btn-sm deleteBtn" data-id="<?= $row['buyerID'] ?>">Delete</button>
            </td>
        </tr>
        <?php
    endwhile;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
    <?php include 'admin_nav.php'; ?>

    <div class="d-flex">
        <?php include 'sidebar.php'; ?>

        <div class="content-wrapper">
            <div id="dynamic-content" class="mt-4">
                <div class="container mt-4">
                    <h3>Manage Buyers</h3>
                    <hr>
                    <!--Input-->
                    <div class="card p-3 mb-4 shadow-sm rounded-3">
                        <div class="input-group">
                            <input type="text" id="searchBox" class="form-control" placeholder="Search by address...">
                            <button id="searchBtn" class="btn btn-outline-secondary" type="button">Search</button>
                        </div>
                    </div>
                    <!--Table-->
                    <table class="table table-bordered table-hover" id="buyerTable">
                        <thead>
                            <tr>
                                <th>Buyer ID</th>
                                <th>User ID</th>
                                <th>Delivery Address</th>
                                <th>City</th>
                                <th>Postal Code</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- AJAX content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editBuyerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editBuyerForm">
                    <div class="modal-header">
                        <h5>Edit Buyer</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="buyerID" id="editBuyerID">
                        <label>Delivery Address</label>
                        <input type="text" name="shippingAddress1" id="editShippingAddress1" class="form-control mb-2" required>
                        <label>City</label>
                        <input type="text" name="shippingAddress2" id="editShippingAddress2" class="form-control mb-2">
                        <label>Postal Code</label>
                        <input type="text" name="postalCode" id="editPostalCode" class="form-control mb-2" required>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            function getParameterByName(name) {
                const url = new URL(window.location.href);
                return url.searchParams.get(name);
            }
            
            function loadBuyers(search = '') {
                const userID = getParameterByName('userID');
                const buyerID = getParameterByName('buyerID');

                const postData = {};
                if (search) postData.search = search;
                if (userID) postData.userID = userID;
                if (buyerID) postData.buyerID = buyerID;

                $.post('manage_buyers.php', postData, function (data) {
                    $('#buyerTable tbody').html(data);
                });
            }


            // Load specific user if userID is present in query params
            const urlParams = new URLSearchParams(window.location.search);
            const userIDFromURL = urlParams.get('userID');
            if (userIDFromURL) {
                loadBuyers('', '', userIDFromURL);
            } else {
                loadBuyers(); // default load
            }

            $(document).on('click', '.editBtn', function () {
                let row = $(this).closest('tr');
                $('#editBuyerID').val(row.find('.buyerID').text());
                $('#editShippingAddress1').val(row.find('.shippingAddress1').text());
                $('#editShippingAddress2').val(row.find('.shippingAddress2').text());
                $('#editPostalCode').val(row.find('.postalCode').text());
                $('#editBuyerModal').modal('show');
            });

            $('#editBuyerForm').submit(function (e) {
                e.preventDefault();
                $.post('manage_buyers.php', $(this).serialize(), function () {
                    $('#editBuyerModal').modal('hide');
                    loadBuyers();
                });
            });

            $(document).on('click', '.deleteBtn', function () {
                if (confirm('Are you sure you want to delete this buyer?')) {
                    $.post('manage_buyers.php', { deleteBuyerID: $(this).data('id') }, function () {
                        loadBuyers();
                    });
                }
            });

            $('#searchBtn').click(function () {
                const searchVal = $('#searchBox').val();
                const userID = getParameterByName('userID');
                const buyerID = getParameterByName('buyerID');

                const postData = {
                    search: searchVal
                };

                if (userID) postData.userID = userID;
                if (buyerID) postData.buyerID = buyerID;

                $.post('manage_buyers.php', postData, function (data) {
                    $('#buyerTable tbody').html(data);
                });
            });
            $('#searchBox').on('keypress', function (e) {
                if (e.which === 13) {
                    $('#searchBtn').click();
                }
            });

        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
