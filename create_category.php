<?php
require('connect.php');

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category'])) {
    $new_category = filter_input(INPUT_POST, 'new_category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    if (!empty($new_category)) {
        $insert_cat = $db->prepare("INSERT INTO category (foodtype) VALUES (:foodtype)");
        $insert_cat->bindValue(':foodtype', $new_category);
        $insert_cat->execute();

        $message = "Category added successfully!";
    } else {
        $message = "Please enter a valid category name.";
    }
}
// Handle renaming an existing category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rename_category'])) {
    $selected_category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
    $updated_name = filter_input(INPUT_POST, 'updated_category_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!empty($updated_name) && $selected_category_id) {
        $update_stmt = $db->prepare("UPDATE category SET foodtype = :foodtype WHERE id = :id");
        $update_stmt->bindValue(':foodtype', $updated_name);
        $update_stmt->bindValue(':id', $selected_category_id, PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            $message = "Category renamed successfully!";
        } else {
            $message = "Failed to update category.";
        }
    } else {
        $message = "Please select a category and enter a new name.";
    }
}
// Fetch all categories
$cat_stmt = $db->prepare("SELECT * FROM category");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css?v=1.0">
    <title>Create Category</title>
</head>
<body>
    <h2>Create New Category</h2>

    <?php if (!empty($message)): ?>
        <p style="color: green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form action="create_category.php" method="POST">
        <label for="new_category">New Category Name:</label>
        <input type="text" id="new_category" name="new_category" required>
        <button type="submit" name="create_category">Add Category</button>
    </form>
    <hr>
    <h2>Rename Existing Category</h2>
    <form action="create_category.php" method="POST">
        <label for="category_id">Select Category:</label>
        <select name="category_id" id="category_id" required>
            <option value="">-- Choose a Category --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['foodtype']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="updated_category_name">New Name:</label>
        <input type="text" id="updated_category_name" name="updated_category_name" required>

        <button type="submit" name="rename_category">Rename Category</button>
    </form>

    <p><a href="products.php">Back to Menu</a></p>
</body>
</html>
