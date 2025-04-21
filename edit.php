<?php
// Ensure an ID is provided
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: products.php");
    exit();
}

require('connect.php');

$message = "";

// Step 1: Fetch category to edit
$category_id = filter_input(INPUT_GET, 'cat_id', FILTER_SANITIZE_NUMBER_INT);

if ($category_id) {
    $cat_stmt = $db->prepare("SELECT * FROM category WHERE id = :id");
    $cat_stmt->bindValue(':id', $category_id, PDO::PARAM_INT);
    $cat_stmt->execute();
    $category = $cat_stmt->fetch();

    if (!$category) {
        $message = "Category not found.";
    }
} else {
    $message = "Invalid category ID.";
}

// Step 2: Handle renaming submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rename_category'])) {
    $updated_name = filter_input(INPUT_POST, 'updated_category_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!empty($updated_name) && $category_id) {
        $update_stmt = $db->prepare("UPDATE category SET foodtype = :foodtype WHERE id = :id");
        $update_stmt->bindValue(':foodtype', $updated_name);
        $update_stmt->bindValue(':id', $category_id, PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            $message = "Category renamed successfully!";
            // Refresh to get updated name
            header("Location: edit.php?id=" . $category_id);
            exit();
        } else {
            $message = "Failed to update category.";
        }
    } else {
        $message = "Please enter a new valid name.";
    }
}

// File validation function (checks if the file is a valid image)
function file_is_an_image($temporary_path, $new_path) {
    $allowed_mime_types = ['image/gif', 'image/jpeg', 'image/png'];
    $allowed_file_extensions = ['gif', 'jpg', 'jpeg', 'png'];
    
    // Get image information
    $image_info = getimagesize($temporary_path);

    // Check if getimagesize returned a valid result
    if ($image_info === false) {
        return false; // It's not a valid image
    }

    $actual_file_extension = pathinfo($new_path, PATHINFO_EXTENSION);
    $actual_mime_type = $image_info['mime']; // Access mime only if it's a valid image
    
    $file_extension_is_valid = in_array($actual_file_extension, $allowed_file_extensions);
    $mime_type_is_valid = in_array($actual_mime_type, $allowed_mime_types);
    
    return $file_extension_is_valid && $mime_type_is_valid;
}

// Build and prepare SQL String with :id placeholder parameter.
$query = "SELECT * FROM menu WHERE menu_item_id = :id LIMIT 1";
$statement = $db->prepare($query);
$statement->bindValue(':id', $id, PDO::PARAM_INT);
$statement->execute();

// Fetch the row selected by primary key id.
$row = $statement->fetch(PDO::FETCH_ASSOC);
// Fetch all categories for the dropdown
$cat_stmt = $db->prepare("SELECT * FROM category");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll();


// Check if the query returned a result
if (!$row) {
    die("Error: Menu item not found.");
}

// Handle form submission to update the product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['delete'])) {
        // Delete the menu item and the associated image
        if ($row['image_path'] && file_exists($row['image_path'])) {
            unlink($row['image_path']);  // Delete the image from the server
        }

        // Delete the menu item from the database
        $delete_query = "DELETE FROM menu WHERE menu_item_id = :id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($delete_stmt->execute()) {
            echo "Menu item deleted successfully!";
            header("Location: products.php");  // Redirect after deletion
            exit();
        } else {
            echo "Failed to delete the menu item.";
        }
    } else {
        // Update the product
        $new_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $new_description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $new_price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $new_availability_status = filter_input(INPUT_POST, 'availability_status', FILTER_SANITIZE_STRING);

        // Handle the case where the image should be deleted
        if (isset($_POST['delete_image'])) {
            // If user checked the delete image checkbox, set image_path to NULL
            $new_image_path = null;
            if ($row['image_path'] && file_exists($row['image_path'])) {
                unlink($row['image_path']);  // Delete the image file
            }
        } else {
            // If a new image is uploaded
            $new_image_path = $row['image_path'];  // Default to the current image path
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                // Validate the uploaded image before saving
                $image_filename = $_FILES['image']['name'];
                $temporary_image_path = $_FILES['image']['tmp_name'];
                $new_image_path = 'images/' . basename($image_filename);  // Set new path for the image

                // Validate if it's a valid image
                if (!file_is_an_image($temporary_image_path, $new_image_path)) {
                    echo "The uploaded file is not a valid image. Please upload a valid image (GIF, JPEG, PNG).";
                    exit();
                }

                // Move the uploaded file to the 'images' directory
                if (move_uploaded_file($temporary_image_path, $new_image_path)) {
                    // If a new image is uploaded, remove the old image from the server
                    if ($row['image_path'] && file_exists($row['image_path'])) {
                        unlink($row['image_path']);  // Delete the old image
                    }
                } else {
                    echo "Error moving the uploaded file.";
                    exit();
                }
            }
        }

        // Update the menu item in the database
        $update_query = "UPDATE menu SET name = :name, description = :description, price = :price, 
                 availability_status = :availability_status, image_path = :image_path WHERE menu_item_id = :id";

        $update_stmt = $db->prepare($update_query);

        $update_stmt->bindValue(':name', $new_name, PDO::PARAM_STR);
        $update_stmt->bindValue(':description', $new_description, PDO::PARAM_STR);
        $update_stmt->bindValue(':price', $new_price, PDO::PARAM_STR);
        $update_stmt->bindValue(':availability_status', $new_availability_status, PDO::PARAM_STR);
        $update_stmt->bindValue(':image_path', $new_image_path, PDO::PARAM_STR);
        $update_stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            echo "Menu item updated successfully!";
            header("Location: edit.php?id=" . $id);  // Redirect to the updated product
            exit();
        } else {
            echo "Failed to update the menu item.";
        }
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
    <title>Edit Menu Item</title>
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

    <h1>Edit Menu Item</h1>

    <form method="post" action="edit.php?id=<?= $row['menu_item_id'] ?>&cat_id=<?= $category['id'] ?>" enctype="multipart/form-data">

        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" required><?= htmlspecialchars($row['description']) ?></textarea>

        <label for="price">Price</label>
        <input type="text" id="price" name="price" value="<?= htmlspecialchars($row['price']) ?>" required>

        <label for="availability_status">Availability</label>
        <select name="availability_status" id="availability_status" required>
            <option value="Available" <?= $row['availability_status'] == 'Available' ? 'selected' : '' ?>>Available</option>
            <option value="Unavailable" <?= $row['availability_status'] == 'Unavailable' ? 'selected' : '' ?>>Unavailable</option>
        </select>

        <!-- Image upload field -->
        <label for="image">Menu Item Image</label>
        <input type="file" id="image" name="image" accept="image/*"><br>

        <!-- Checkbox to delete image (only show if there is an image) -->
        <?php if ($row['image_path']): ?>
            <label for="delete_image">Delete Image:</label>
            <input type="checkbox" id="delete_image" name="delete_image" value="1"><br>
        <?php endif; ?>

        <label for="category_id">Category</label>
        <select id="category_id" name="category_id" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $row['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['foodtype']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        

        <button type="submit" name="submit">Update Menu Item</button>
        <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</button>
    </form>

    <?php if ($row['image_path']): ?>
        <h3>Current Image:</h3>
        <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="Current Image" style="width: 300px;">
    <?php endif; ?>

    <a href="products.php">Back to Menu</a>
</body>
</html>

