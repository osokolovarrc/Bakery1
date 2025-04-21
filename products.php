<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require('connect.php');

$availabilityFilter = isset($_GET['availability']) ? $_GET['availability'] : '';
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : ''; // NEW LINE

// Build base query and parameters
$query = "SELECT menu.*, category.foodtype 
          FROM menu 
          LEFT JOIN category ON menu.category_id = category.id";
$params = [];
$conditions = [];

// Filtering by availability
if ($availabilityFilter === 'Available' || $availabilityFilter === 'Unavailable') {
    $conditions[] = "availability_status = :availability";
    $params[':availability'] = $availabilityFilter;
}

// Add WHERE if there are conditions
if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

// Sorting
if ($sortOrder === 'name_asc') {
    $query .= " ORDER BY name ASC";
} elseif ($sortOrder === 'name_desc') {
    $query .= " ORDER BY name DESC";
} elseif ($sortOrder === 'price_asc') {
    $query .= " ORDER BY price ASC";
} elseif ($sortOrder === 'price_desc') {
    $query .= " ORDER BY price DESC";
} else {
    $query .= " ORDER BY menu_item_id DESC";
}


$statement = $db->prepare($query);
$statement->execute($params);
$rows = $statement->fetchAll();

if (empty($rows)) {
    die("No menu items found.");
}

$name = $description = $price = $availability_status = "";
$cat_stmt = $db->prepare("SELECT * FROM category");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll();

if ($_POST && !empty($_POST['name']) && !empty($_POST['description']) && !empty($_POST['price']) && isset($_POST['availability_status'])) {
    // Sanitize and validate form inputs
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $availability_status = filter_input(INPUT_POST, 'availability_status', FILTER_SANITIZE_STRING);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);

    
    // Handle image upload
    $image_path = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $image_directory = "images/";
        $image_path = $image_directory . $image_name;

        // Move the uploaded image to the images directory
        if (!move_uploaded_file($image_tmp_name, $image_path)) {
            echo "Failed to upload image.";
            exit();
        }
    }

    // Validate availability_status
    if ($availability_status !== 'Available' && $availability_status !== 'Unavailable') {
        $availability_status = 'Unavailable';
    }

    // Prepare SQL to insert menu item into the database
    $query = "INSERT INTO menu (name, description, price, availability_status, image_path, category_id) 
          VALUES (:name, :description, :price, :availability_status, :image_path, :category_id)";

    $statement = $db->prepare($query);
    $statement->bindValue(':name', $name);
    $statement->bindValue(':description', $description);
    $statement->bindValue(':price', $price);
    $statement->bindValue(':availability_status', $availability_status);
    $statement->bindValue(':image_path', $image_path); // Store the image path
    $statement->bindValue(':category_id', $category_id);

    $cat_stmt = $db->prepare("SELECT * FROM category");
    $cat_stmt->execute();
    $categories = $cat_stmt->fetchAll();

    // Execute the query
    if ($statement->execute()) {
        header("Location: products.php");
        exit();
    } else {
        echo "Failed to add menu item.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Welcome to our Menu!</title>
</head>
<body>
    <!-- Main Navigation -->
    <nav id="navigation1" aria-label="Main Navigation">
        <img class="logo" src="images/croissant.png" alt="logo">
        <nav>
            <ul class="list1">
                <li><a href="index.php">HOME </a></li>
                <li><a href="menu.php">PRODUCTS </a></li>
                <li><a href="contact.php">CONTACT US</a></li>
            </ul>
        </nav>
        <div class="search">
            <input id="search-input" type="search" placeholder="Search..." >
            <button>SEARCH</button>
        </div>  
    </nav>
    
    <h1> Menu</h1>
    <a href="post.php">Add menu item</a> <br/>
    <a href="create_category.php">Create/Update category</a> <br/>
    
    <form method="GET" action="products.php">
        <label for="availability-filter">Availability:</label>
        <select id="availability-filter" name="availability">
            <option value="">All</option>
            <option value="Available" <?php echo (isset($_GET['availability']) && $_GET['availability'] == 'Available') ? 'selected' : ''; ?>>Available</option>
            <option value="Unavailable" <?php echo (isset($_GET['availability']) && $_GET['availability'] == 'Unavailable') ? 'selected' : ''; ?>>Unavailable</option>
        </select>

        <label for="sort">Sort by:</label>
        <select id="sort" name="sort">
            <option value="">Default</option>
            <option value="name_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
            <option value="name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
            <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
            <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
        </select>


        <button type="submit">Apply</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Image</th>
                <th>Description</th>
                <th>Price</th>
                <th>Availability</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>
                        <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" width="100">
                    </td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>$<?= number_format($row['price'], 2) ?></td>
                    <td><?= $row['availability_status'] === 'Available' ? 'YES' : 'NO'; ?></td>
                    <td>
                        <form method="post" action="update_category.php">
                            <input type="hidden" name="menu_item_id" value="<?= $row['menu_item_id'] ?>">
                            <select name="category_id" onchange="this.form.submit()">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $row['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['foodtype']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>

                    <td>
                        <a href="edit.php?id=<?= $row['menu_item_id'] ?>">Edit</a> |
                        <a href="delete.php?id=<?= $row['menu_item_id'] ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>



