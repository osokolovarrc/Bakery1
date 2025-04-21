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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Create Category</title>
</head>
<body>
    <h1>Create New Category</h1>

    <?php if (!empty($message)): ?>
        <p style="color: green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form action="create_category.php" method="POST">
        <label for="new_category">New Category Name:</label>
        <input type="text" id="new_category" name="new_category" required>
        <button type="submit" name="create_category">Add Category</button>
    </form>

    <p><a href="post.php">Back to Menu</a></p>
</body>
</html>
