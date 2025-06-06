<?php
session_start();
$userID = $_SESSION['userID']; // assuming user is logged in
require '../db.php'; // your DB connection

$stmt = $conn->prepare("SELECT * FROM feedback WHERE userID = ? ORDER BY createdAt DESC");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$stmt->close();
$conn->close();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    

    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $category = $_POST['category'];

    $stmt = $conn->prepare("INSERT INTO feedback (userID, subject, message, category) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userID, $subject, $message, $category);

    if ($stmt->execute()) {
         $message = "Feedback was submitted successfully!" ;
         
    } else {
        $message = "Something went wrong";
    }

    $stmt->close();
    $conn->close();

    header("Location: ../account.php?page=feedback&status=success&message=". urlencode($message)); 

}

?>

<!-- Feedback Form -->
 
<div class="card shadow-sm mt-4 mb-5" style="background-color: #F1F1F1;">
    <div class="card-body mb-0">
        <div class="container my-4">
            <h3 class="mb-3">Submit Feedback</h3>
            <hr>

<!-- Tabs navigation -->
    <ul class="nav nav-tabs" id="feedbackTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="new-tab" data-bs-toggle="tab" href="#new" role="tab">New</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="history-tab" data-bs-toggle="tab" href="#history" role="tab">History</a>
        </li>
    </ul>

    <div class="tab-content mt-3" id="feedbackTabsContent">
<div class="tab-pane fade show active" id="new" role="tabpanel">

            <form method="POST" action="account/submit_feedback.php">
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" placeholder="Enter subject" required>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select" required>
                        <option value="">Select a category</option>
                        <option>Feedback</option>
                        <option>Complaint</option>
                        <option>Suggestion</option>
                        <option>Bug</option>
                        <option>Other</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" placeholder="Write your message here..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit Feedback</button>
            </form>
            </div>
                    <div class="tab-pane fade" id="history" role="tabpanel">
                        <?php
                        while ($row = $result->fetch_assoc()) {
                        ?>
                            <div class="card mb-3 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($row['subject']) ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        Category: <?= $row['category'] ?> | Status: <?= $row['status'] ?>
                                    </h6>
                                    <p class="card-text">
                                        <strong>Message:</strong><br>
                                        <?= nl2br(htmlspecialchars($row['message'])) ?>
                                    </p>
                                    <?php if (!empty($row['adminReply'])): ?>
                                        <div class="alert alert-info">
                                            <strong>Admin Reply:</strong><br>
                                            <?= nl2br(htmlspecialchars($row['adminReply'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php
                        }
                        ?>

                    </div>



            
            </div>
        </div>
    </div>
</div>

