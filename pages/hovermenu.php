<?php
require_once "db.php";

// Fetch all categories
$categories = [];
$result = $conn->query("SELECT * FROM categories ORDER BY name ASC");

while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Organize categories into parent-child
$menu = [];

// First, set the parent categories
foreach ($categories as $cat) {
    if (is_null($cat['parentID'])) {
        $menu[$cat['categoryID']] = [  // Use categoryID as the key for the parent category
            'name' => $cat['name'],
            'children' => []
        ];
    }
}

// Then, set the children under the correct parent
foreach ($categories as $cat) {
    if (!is_null($cat['parentID'])) {
        // Ensure the parent exists in the menu before adding the child
        if (isset($menu[$cat['parentID']])) {
            $menu[$cat['parentID']]['children'][] = $cat['name'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>

    <!--bootstrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!--frontawesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!--stylesheet-->
    <link rel="stylesheet" href="assets/css/theme.css" />
    <link rel="stylesheet" href="assets/css/hovermenu.css" />

</head>

<body>

<nav class="category-menu">
  <ul>
  <?php foreach ($menu as $parentID => $main): ?>
  <li class="has-mega-menu">
    <a href="category.php?categoryID=<?= $parentID ?>"><?= htmlspecialchars($main['name']) ?></a>
    <div class="mega-menu">
      <div class="mega-menu-content">
        <div class="column">
          <h4>Popular categories in <?= htmlspecialchars($main['name']) ?></h4>
          <ul>
            <?php foreach ($main['children'] as $childName): ?>
              <?php
              // Find the child category ID
              $childID = null;
              foreach ($categories as $catOption) {
                  if ($catOption['name'] === $childName) {
                      $childID = $catOption['categoryID'];
                      break;
                  }
              }

              ?>
            <?php if ($childID): ?>
              <li><a href="category.php?categoryID=<?= $childID ?>"><?= htmlspecialchars($childName) ?></a></li>
            <?php endif; ?>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </li>
<?php endforeach; ?>

  </ul>
</nav>

</body>

</html>