<?php
require('connect.php');
session_start(); // Start the session to access session variables

// Fetch all items from the menu without filtering by type
$query = "SELECT * FROM menu ORDER BY menu_item_id DESC"; // Sort by menu_item_id (or any other column you prefer)
$statement = $db->prepare($query);
$statement->execute();

$rows = $statement->fetchAll();

if (empty($rows)) {
    die("No menu items found.");
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']); // Assuming 'user_id' is stored in the session when logged in
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Menu</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
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
                <?php if ($isLoggedIn): ?>
                    <li><a href="products.php">ADMIN</a></li> 
                <?php else: ?>
                    <li><a href="login.php">LOG IN</a></li> <!-- Login link if not logged in -->
                <?php endif; ?>
            </ul>
        </nav>
        <div class="search">
            <input id="search-input" type="search" placeholder="Search..." >
            <button>SEARCH</button>
        </div>  
    </nav>

    <!-- Header for the Products page -->
    <header id="products">
        <h1>Our Menu</h1>
    </header>

    <!-- Each product block -->
    <section id="product">
        <?php foreach ($rows as $row): ?>
            <div class="goods">
                <h2><?= htmlspecialchars($row['name']) ?></h2>
                <img class="product_image" src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                <p class="description"><?= htmlspecialchars($row['description']) ?></p>
                <a href="details.php?id=<?= $row['menu_item_id'] ?>">
                    <button>Details</button>
                </a>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- Footer -->
    <footer>
        <nav>
            <ul class="list2">
                <li><a href="index.php">HOME </a></li>
                <li><a href="menu.php">PRODUCTS </a></li>
                <li><a href="contact.php">CONTACT US</a></li>
            </ul>
        </nav>
        <p class="copyright">Copyright &#169; 2011 Sweet Delights Bakery</p>
        <div class="address">
            <p>Central Park West at 79th Street, New York, NY 100024-5192</p> 
        </div>
    </footer>
</body>
</html>

