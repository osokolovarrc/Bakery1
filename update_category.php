<?php
require('connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_item_id = filter_input(INPUT_POST, 'menu_item_id', FILTER_SANITIZE_NUMBER_INT);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);

    if ($menu_item_id && $category_id) {
        $stmt = $db->prepare("UPDATE menu SET category_id = :category_id WHERE menu_item_id = :menu_item_id");
        $stmt->bindValue(':category_id', $category_id);
        $stmt->bindValue(':menu_item_id', $menu_item_id);
        $stmt->execute();
    }
}

header("Location: products.php");
exit();
