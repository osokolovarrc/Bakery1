<?php
require('connect.php'); // Include the connection file to the database

// File upload path function (unchanged)
function file_upload_path($original_filename, $upload_subfolder_name = 'images') {
    $upload_subfolder_url = $upload_subfolder_name;
    $path_segments = [$upload_subfolder_url, basename($original_filename)];
    return join(DIRECTORY_SEPARATOR, $path_segments);
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_image_path = null; // Initialize image path as null
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image_filename = $_FILES['image']['name'];
        $temporary_image_path = $_FILES['image']['tmp_name'];

        // Generate the relative path to store in the database
        $new_image_path = file_upload_path($image_filename);

        // Check if the file is a valid image
        if (file_is_an_image($temporary_image_path, $new_image_path)) {
            // Move the uploaded file to the 'images' folder
            if (move_uploaded_file($temporary_image_path, $new_image_path)) {
                echo "Image uploaded successfully!";
            } else {
                echo "Error moving the uploaded file.";
                exit();
            }
        } else {
            // Handle invalid image case
            echo "Invalid image format. Please upload a valid image (GIF, JPEG, PNG).";
            exit();
        }
    }

    // Process other form data (name, description, price, etc.)
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $availability_status = $_POST['availability_status'];
    $type = $_POST['type']; 

    // If no image was uploaded, set a default image path or NULL
    if (!$new_image_path) {
        $new_image_path = null; // Set this to null if no image is uploaded
    }

    // Insert data into the database
    $query = "INSERT INTO menu (name, description, price, availability_status, image_path, type) 
              VALUES (:name, :description, :price, :availability_status, :image_path, :type)";
    $statement = $db->prepare($query);
    $statement->execute([
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':availability_status' => $availability_status,
        ':image_path' => $new_image_path, // Store the relative path (or NULL if no image)
        ':type' => $type // Store the selected type (sweet or savory)
    ]);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Add New Menu Item</title>
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

    <h1>Add New Menu Item</h1>

    <form action="post.php" method="POST" enctype="multipart/form-data">
        <label for="name">Menu Item Name:</label>
        <input type="text" id="name" name="name" required><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea><br>

        <label for="price">Price:</label>
        <input type="text" id="price" name="price" required><br>

        <label for="availability_status">Availability:</label>
        <select name="availability_status" id="availability_status" required>
            <option value="Available">Available</option>
            <option value="Unavailable">Unavailable</option>
        </select><br>

        <label for="type">Type:</label>
        <select name="type" id="type" required>
            <option value="sweet">Sweet</option>
            <option value="savory">Savory</option>
        </select><br>

        <label for="image">Menu Item Image (Optional):</label>
        <input type="file" id="image" name="image" accept="image/*"><br>

        <input type="submit" value="Add Menu Item">
    </form>

    <!-- Display uploaded image only if the image exists and is valid -->
    <?php if (!empty($new_image_path) && file_exists($new_image_path)): ?>
        <h3>Uploaded Image:</h3>
        <img src="<?= htmlspecialchars($new_image_path) ?>" alt="Uploaded Image" style="width: 300px;">
    <?php else: ?>
        <!-- No image uploaded, nothing displayed -->
        <p>No image uploaded.</p>
    <?php endif; ?>
</body>
</html>
