<?php
require('connect.php');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: products.php");
    exit();
}

$query = "DELETE FROM menu WHERE menu_item_id = :id";
$statement = $db->prepare($query);
$statement->bindValue(':id', $id, PDO::PARAM_INT);

if ($statement->execute()) {
    echo "Menu item deleted successfully!";
    header("Location: products.php");
    exit();
} else {
    echo "Failed to delete the menu item.";
}
?>
