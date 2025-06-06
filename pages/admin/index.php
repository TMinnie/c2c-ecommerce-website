<?php
include '../db.php';
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home'
?>

<div class="container mt-4">
        <h3 class="mb-4">Welcome, Admin</h3>
        <hr>
        <div class="row text-center">
            <?php
            // Total Users
            $total = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
            $buyers = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'buyer'")->fetch_assoc()['count'];
            $sellers = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'seller'")->fetch_assoc()['count'];
            ?>
            <div class="col-md-4">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <h5>Total Users</h5>
                        <h2><?= $total ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <h5>Total Buyers</h5>
                        <h2><?= $buyers ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-dark mb-4">
                    <div class="card-body">
                        <h5>Total Sellers</h5>
                        <h2><?= $sellers ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>