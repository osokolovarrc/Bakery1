<?php
require('connect.php');

// Get the ID from the URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die("Invalid request. No valid ID provided.");
}

// First, fetch the menu item to delete its image
$query = "SELECT image_path FROM menu WHERE menu_item_id = :id";
$statement = $db->prepare($query);
$statement->bindValue(':id', $id, PDO::PARAM_INT);
$statement->execute();
$row = $statement->fetch(PDO::FETCH_ASSOC);

// If found, delete the image if it exists
if ($row && !empty($row['image_path']) && file_exists($row['image_path'])) {
    unlink($row['image_path']);
}

// Now delete the menu item from the database
$delete_query = "DELETE FROM menu WHERE menu_item_id = :id";
$delete_stmt = $db->prepare($delete_query);
$delete_stmt->bindValue(':id', $id, PDO::PARAM_INT);

if ($delete_stmt->execute()) {
    // Redirect back to menu list
    header("Location: products.php");
    exit();
} else {
    echo "Failed to delete the menu item.";
}
?>
