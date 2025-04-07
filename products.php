<?php
require('connect.php');

// Capture type filter if provided in the URL
$typeFilter = isset($_GET['type']) ? $_GET['type'] : '';

// Modify query based on the filter (if provided)
if ($typeFilter) {
    // If a type filter is selected, filter the menu by that type and order by menu_item_id
    $query = "SELECT * FROM menu WHERE type = :type ORDER BY menu_item_id DESC"; // Sort by menu_item_id
    $statement = $db->prepare($query);
    $statement->execute([':type' => $typeFilter]);
} else {
    // If no filter is applied, fetch all items ordered by menu_item_id
    $query = "SELECT * FROM menu ORDER BY menu_item_id DESC"; // Sort by menu_item_id
    $statement = $db->prepare($query);
    $statement->execute();
}

$rows = $statement->fetchAll();

if (empty($rows)) {
    die("No menu items found.");
}

$name = $description = $price = $availability_status = "";
if ($_POST && !empty($_POST['name']) && !empty($_POST['description']) && !empty($_POST['price']) && isset($_POST['availability_status'])) {
    // Sanitize and validate form inputs
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $availability_status = filter_input(INPUT_POST, 'availability_status', FILTER_SANITIZE_STRING);
    
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
    $query = "INSERT INTO menu (name, description, price, availability_status, image_path) 
              VALUES (:name, :description, :price, :availability_status, :image_path)";
    $statement = $db->prepare($query);
    $statement->bindValue(':name', $name);
    $statement->bindValue(':description', $description);
    $statement->bindValue(':price', $price);
    $statement->bindValue(':availability_status', $availability_status);
    $statement->bindValue(':image_path', $image_path); // Store the image path

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
    <a href="post.php">Add menu item</a>
    
    <!-- Filter for Menu Type -->
    <form method="GET" action="products.php">
        <label for="type-filter">Filter by type:</label>
        <select id="type-filter" name="type">
            <option value="">All</option>
            <option value="sweet" <?php echo (isset($_GET['type']) && $_GET['type'] == 'sweet') ? 'selected' : ''; ?>>Sweet</option>
            <option value="savory" <?php echo (isset($_GET['type']) && $_GET['type'] == 'savory') ? 'selected' : ''; ?>>Savory</option>
        </select>
        <button type="submit">Filter</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Image</th>
                <th>Description</th>
                <th>Price</th>
                <th>Availability</th>
                <th>Type</th>
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
                    <td><?= htmlspecialchars($row['type']) ?></td> <!-- Display the type of the item -->
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



