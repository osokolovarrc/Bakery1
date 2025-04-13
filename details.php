<?php
session_start();
$_SESSION['is_admin'] = true;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: menu.php");
    exit();
}

require('connect.php');

// Get menu item
$query = "SELECT * FROM menu WHERE menu_item_id = :id LIMIT 1";
$statement = $db->prepare($query);
$statement->bindValue(':id', $id, PDO::PARAM_INT);
$statement->execute();
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("Error: Menu item not found.");
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_POST['user_name'])) {
    $comment_text = trim($_POST['comment']);
    $user_name = trim($_POST['user_name']);

    if (!empty($comment_text) && !empty($user_name)) {
        $insert_query = "INSERT INTO comment (menu_item_id, user_name, comment_text) VALUES (:menu_item_id, :user_name, :comment_text)";
        $insert_statement = $db->prepare($insert_query);
        $insert_statement->bindValue(':menu_item_id', $id, PDO::PARAM_INT);
        $insert_statement->bindValue(':user_name', $user_name, PDO::PARAM_STR);
        $insert_statement->bindValue(':comment_text', $comment_text, PDO::PARAM_STR);
        $insert_statement->execute();
    } 
}

// Fetch visible comments only
$comments_query = "SELECT * FROM comment WHERE menu_item_id = :id AND visible = 1 ORDER BY created_at DESC";
$comments_statement = $db->prepare($comments_query);
$comments_statement->bindValue(':id', $id, PDO::PARAM_INT);
$comments_statement->execute();
$comments = $comments_statement->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>View Menu Item</title>
</head>
<body>
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

    <h1>View Menu Item</h1>

    <div class="menu-item-details">
        <p><strong>Name:</strong> <?= htmlspecialchars($row['name']) ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($row['description']) ?></p>
        <p><strong>Price:</strong> $<?= number_format($row['price'], 2) ?></p>
        <p><strong>Availability:</strong> <?= htmlspecialchars($row['availability_status']) ?></p>
      

        <?php if ($row['image_path']): ?>
            <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="Current Image" style="width: 300px;">
        <?php else: ?>
            <p>No image available.</p>
        <?php endif; ?>
    </div>

    <h2>Comments</h2>

    <!-- Comment form -->
    <form method="post" action="">
        <label for="user_name">Your Name:</label>
        <input type="text" id="user_name" name="user_name" placeholder="Enter your name" required><br>
        <textarea name="comment" placeholder="Write your comment..." rows="4" cols="50" required></textarea><br>
        <button type="submit">Submit Comment</button>
    </form>

    <h3>Recent Comments</h3>
    <?php if ($comments): ?>
        <ul>
            <?php foreach ($comments as $comment): ?>
                <li>
                    <p><strong><?= htmlspecialchars($comment['user_name']) ?> says:</strong></p>
                    <p><?= htmlspecialchars($comment['comment_text']) ?></p>
                    <small>Posted on <?= htmlspecialchars($comment['created_at']) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No comments yet.</p>
    <?php endif; ?>

    <a href="menu.php">Back to Menu</a>
</body>
</html>
