<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require '../db.php';

$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';

$where = [];
$params = [];
$types = '';

if (!empty($category)) {
    $where[] = 'f.category = ?';
    $params[] = $category;
    $types .= 's';
}

if (!empty($status)) {
    $where[] = 'f.status = ?';
    $params[] = $status;
    $types .= 's';
}

$whereClause = '';
if (!empty($where)) {
    $whereClause = 'WHERE ' . implode(' AND ', $where);
}

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total records
$countSql = "SELECT COUNT(*) as total FROM feedback f $whereClause";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);
$countStmt->close();

// Fetch records with LIMIT
$sql = "SELECT f.*, u.uFirst, u.uLast FROM feedback f 
        JOIN users u ON f.userID = u.userID 
        $whereClause
        ORDER BY f.createdAt DESC 
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);

// Combine all params into one array for binding
$fullParams = [...$params, $offset, $limit];
$fullTypes = $types . 'ii';
$stmt->bind_param($fullTypes, ...$fullParams);

$stmt->execute();
$result = $stmt->get_result();

// Handle admin reply POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedbackID = $_POST['feedbackID'];
    $status = $_POST['status'];
    $adminReply = $_POST['adminReply'];

    $updateStmt = $conn->prepare("UPDATE feedback SET adminReply = ?, status = ?, updatedAt = NOW() WHERE feedbackID = ?");
    $updateStmt->bind_param("ssi", $adminReply, $status, $feedbackID);

    if ($updateStmt->execute()) {
        header("Location: feedback_response.php?success=1");
        exit();
    } else {
        echo "Error updating feedback: " . $updateStmt->error;
    }

    $updateStmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Feedback Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<?php include 'admin_nav.php'; ?>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>
    <div class="content-wrapper">
        <div class="container mt-4">
            <h3>Admin Feedback Dashboard</h3>
            <hr>

            <form method="get" class="row g-2 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="Feedback" <?= ($category === 'Feedback') ? 'selected' : '' ?>>Feedback</option>
                        <option value="Complaint" <?= ($category === 'Complaint') ? 'selected' : '' ?>>Complaint</option>
                        <option value="Suggestion" <?= ($category === 'Suggestion') ? 'selected' : '' ?>>Suggestion</option>
                        <option value="Bug" <?= ($category === 'Bug') ? 'selected' : '' ?>>Bug</option>
                        <option value="General" <?= ($category === 'General') ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?= ($status === 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="In Progress" <?= ($status === 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                        <option value="Resolved" <?= ($status === 'Resolved') ? 'selected' : '' ?>>Resolved</option>
                        <option value="Dismissed" <?= ($status === 'Dismissed') ? 'selected' : '' ?>>Dismissed</option>
                    </select>
                </div>
                <div class="col-md-4 align-self-end">
                    <button type="submit" class="btn btn-outline-secondary">Apply Filters</button>
                    <a href="feedback_response.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>

            <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                <div class="card my-3 shadow-sm">
                    <div class="card-header d-flex justify-content-between">
                        <div>
                            <strong><?= htmlspecialchars($row['subject']) ?></strong><br>
                            <small class="text-muted">
                                From: <a href="manage_users.php?userID=<?= $row['userID'] ?>">
                                    <?= htmlspecialchars($row['uFirst']) . ' ' . htmlspecialchars($row['uLast']) ?>
                                </a> |
                                Category: <?= $row['category'] ?> |
                                Status: <?= $row['status'] ?>
                            </small>
                        </div>
                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                                data-bs-target="#feedback<?= $i ?>" aria-expanded="false"
                                aria-controls="feedback<?= $i ?>">
                            View Details
                        </button>
                    </div>
                    <div class="collapse" id="feedback<?= $i ?>">
                        <div class="card-body">
                            <p><strong>Message:</strong><br><?= nl2br(htmlspecialchars($row['message'])) ?></p>
                            <?php if (!empty($row['adminReply'])): ?>
                                <div class="alert alert-info">
                                    <strong>Admin Reply:</strong><br><?= nl2br(htmlspecialchars($row['adminReply'])) ?>
                                </div>
                            <?php endif; ?>
                            <form method="post" action="feedback_response.php">
                                <input type="hidden" name="feedbackID" value="<?= $row['feedbackID'] ?>">
                                <div class="mb-2">
                                    <label>Status:</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option <?= $row['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option <?= $row['status'] == 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                                        <option <?= $row['status'] == 'Dismissed' ? 'selected' : '' ?>>Dismissed</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label>Reply:</label>
                                    <textarea name="adminReply" class="form-control" rows="3"
                                              required><?= htmlspecialchars($row['adminReply']) ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Update Feedback</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php $i++; endwhile; ?>

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                            <a class="page-link"
                               href="?category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>&page=<?= $p ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
