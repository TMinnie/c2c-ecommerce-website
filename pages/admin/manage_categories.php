<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

// Handle Add/Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $parentID = ($_POST['parentID'] === 'null') ? null : intval($_POST['parentID']);
    $categoryID = isset($_POST['categoryID']) ? intval($_POST['categoryID']) : null;

    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $ext;
        $target = '../assets/images/' . $fileName;

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            die("Unsupported file type.");
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $imagePath = $fileName; // Store only the file name
        }
    }

    if ($categoryID) {
        if ($imagePath) {
            $stmt = $conn->prepare("UPDATE categories SET name=?, description=?, parentID=?, imagePath=? WHERE categoryID=?");
            $stmt->bind_param("ssisi", $name, $description, $parentID, $imagePath, $categoryID);
        } else {
            $stmt = $conn->prepare("UPDATE categories SET name=?, description=?, parentID=? WHERE categoryID=?");
            $stmt->bind_param("ssii", $name, $description, $parentID, $categoryID);
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (name, description, parentID, imagePath) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $name, $description, $parentID, $imagePath);
    }

    $stmt->execute();
    header("Location: manage_categories.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $deleteID = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM categories WHERE categoryID = ?");
    $stmt->bind_param("i", $deleteID);
    $stmt->execute();
    header("Location: manage_categories.php");
    exit;
}

// Helper: Render category table
function renderCategoryRows($parentID='', $conn, $indent = 0)
{
    if (!is_null($parentID)){
        $stmt = $conn->prepare("SELECT * FROM categories WHERE parentID = ? ORDER BY name");
        $stmt->bind_param("i", $parentID);
        $stmt->execute();
        $result = $stmt->get_result();

    } else{
         echo "<p>Please select a category";
    }

    while ($category = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $category['categoryID'] . "</td>";
        echo "<td>" . str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $indent) . htmlspecialchars($category['name']) . "</td>";
        
        $descModalID = 'descModal' . $category['categoryID'];

        echo '
        <td>
            <span class="truncate" style="max-width: 300px; 
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: inline-block;
                vertical-align: middle;
                cursor: pointer;" data-bs-toggle="modal"
                data-bs-target="#' . $descModalID . '">
                ' . htmlspecialchars($category['description']) . '
            </span>

            <!-- Modal for full description -->
            <div class="modal fade" id="' . $descModalID . '" tabindex="-1"
                aria-labelledby="' . $descModalID . 'Label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="' . $descModalID . 'Label">Category Description</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ' . nl2br(htmlspecialchars($category['description'])) . '
                        </div>
                    </div>
                </div>
            </div>
        </td>';


        echo "<td><img src='../assets/images/" . htmlspecialchars($category['imagePath']) . "' style='width: 80px; height: 80px; object-fit: cover;'></td>";
        echo "<td>
                <a href='javascript:void(0);' onclick='openEditCategoryModal(". json_encode($category) .")' class='btn btn-sm btn-warning'>Edit</a>
                <a href='manage_categories.php?delete=" . $category['categoryID'] . "' 
                class='btn btn-sm btn-danger' 
                onclick=\"return confirm('Are you sure you want to delete this category?')\">
                Delete
                </a>
              </td>";
        echo "</tr>";
        renderCategoryRows($category['categoryID'], $conn, $indent + 1);
    }
}

// Fetch Main Categories for dropdown
$mainCategories = $conn->query("SELECT * FROM categories WHERE parentID IS NULL ORDER BY name");

// Fetch Edit Data
$edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM categories WHERE categoryID = $id");
    $edit = $result->fetch_assoc();
}
?>

<!-- HTML OUTPUT ------------------------------------------------------------------------------------------------------------------->

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
                

                <!-- Select Category -->
                <form id="mainCategoryForm" class="mb-5" method="GET" action="manage_categories.php">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Manage Categories</h3>
                    <button class="btn btn-primary" onclick="openAddCategoryModal()">+ Add Category</button>
                </div>
                <hr>
                <div class="card p-3 mb-4 shadow-sm rounded-3">
                    <label for="main" class="form-label">Select Main Category:</label>
                    <div class="input-group">
                        <select name="main" id="main" class="form-select">
                            <option value="">-- Choose a main category --</option>
                            <?php while ($cat = $mainCategories->fetch_assoc()): ?>
                                <option value="<?= $cat['categoryID'] ?>" <?= (isset($_GET['main']) && $_GET['main'] == $cat['categoryID']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" class="btn btn-secondary">View Children</button>
                    </div>
                    </div>
                </form>

                <!-- Category Table Output -->
                <div id="categoryTable" class="mb-3">
                    <?php if (isset($_GET['main']) && $_GET['main']!==''): ?>
                        <?php
                        $mainID = intval($_GET['main']);
                        $result = $conn->query("SELECT * FROM categories WHERE categoryID = $mainID");
                        $mainCat = $result->fetch_assoc();
                        ?>
                        <h5 class="mb-3">Subcategories in <?= htmlspecialchars($mainCat['name']) ?></h5>
                        <div class="table-responsive mb-5" >
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php renderCategoryRows($mainID, $conn); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">No main category selected.</div>
                    <?php endif; ?>
                </div>

                <!-- Add/Edit Category Modal -->
                <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <form method="POST" enctype="multipart/form-data" id="categoryForm">

                                <input type="hidden" name="categoryID" id="categoryID">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="categoryModalLabel"><?= isset($_GET['edit']) ? 'Edit' : 'Add New' ?> Category</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Category Name:</label>
                                            <input type="text" name="name" id="categoryName" class="form-control"
                                                value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Parent Category:</label>
                                            <select name="parentID" class="form-select" id="parentID">
                                                <option value="null">None (Main Category)</option>
                                                <?php
                                                $result = $conn->query("SELECT * FROM categories WHERE parentID IS NULL ORDER BY name");
                                                while ($row = $result->fetch_assoc()):
                                                    $selected = (isset($edit['parentID']) && $edit['parentID'] == $row['categoryID']) ? 'selected' : '';
                                                    ?>
                                                    <option value="<?= $row['categoryID'] ?>" <?= $selected ?>>
                                                        <?= htmlspecialchars($row['name']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Description:</label>
                                        <textarea name="description" class="form-control" id="categoryDescription"
                                            rows="3"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Image (optional):</label>
                                        <input type="file" name="image" class="form-control">
                                        <?php if (!empty($edit['imagePath'])): ?>
                                            <div class="mt-2"><img
                                                    src="../assets/images/<?= htmlspecialchars($edit['imagePath']) ?>"
                                                    width="100"></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        
                                        <label class="form-label">Current Saved Image:</label>
                                        <div id="currentImage"></div> 
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success"><?= $edit ? 'Update' : 'Add' ?>
                                        Category</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script>
    const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));


    function openAddCategoryModal() {
        document.getElementById('categoryModalLabel').textContent = 'Add Category';
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryID').value = '';
        document.getElementById('currentImage').innerHTML = '';
        categoryModal.show();
    }

    function openEditCategoryModal(category) {
        document.getElementById('categoryModalLabel').textContent = 'Edit Category';
        document.getElementById('categoryID').value = category.categoryID;
        document.getElementById('categoryName').value = category.name;
        document.getElementById('categoryDescription').value = category.description;
        document.getElementById('parentID').value = category.parentID ?? 'null';

        if (category.imagePath) {
            document.getElementById('currentImage').innerHTML = `<img src="../assets/images/${category.imagePath}" width="100">`;
        } else {
            document.getElementById('currentImage').innerHTML = '';
        }

        categoryModal.show();
    }
</script>

</body>

</html>