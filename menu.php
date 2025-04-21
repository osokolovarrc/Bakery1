<?php
require('connect.php');
session_start(); // Start the session to access session variables

// Get all unique categories from the menu table
$category_query = "SELECT id, foodtype FROM category ORDER BY foodtype ASC";
$category_stmt = $db->prepare($category_query);
$category_stmt->execute();
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$results_per_page = 5; //  switch the value of N to a smaller or larger number
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

$count_sql = "
    SELECT COUNT(*) FROM menu
    LEFT JOIN category ON menu.category_id = category.id
    WHERE 1 = 1
";

$params = [];

if (!empty($category)) {
    $count_sql .= " AND menu.category_id = :category";
    $params[':category'] = $category;
}
if (!empty($search)) {
    $count_sql .= " AND menu.name LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

$count_stmt = $db->prepare($count_sql);
foreach ($params as $key => $val) {
    $count_stmt->bindValue($key, $val);
}
$count_stmt->execute();
$total_results = $count_stmt->fetchColumn();
$total_pages = ceil($total_results / $results_per_page);


if (!empty($category) && !empty($search)) {
    $query = "
        SELECT menu.*, category.foodtype 
        FROM menu
        LEFT JOIN category ON menu.category_id = category.id
        WHERE menu.category_id = :category
        AND menu.name LIKE :search
        ORDER BY menu_item_id DESC
        LIMIT :offset, :limit
    ";

    $statement = $db->prepare($query);
    $statement->bindValue(':category', $category, PDO::PARAM_INT);
    $statement->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->bindValue(':limit', $results_per_page, PDO::PARAM_INT);
} elseif (!empty($category)) {
    $query = "
        SELECT menu.*, category.foodtype 
        FROM menu
        LEFT JOIN category ON menu.category_id = category.id
        WHERE menu.category_id = :category
        ORDER BY menu_item_id DESC
        LIMIT :offset, :limit
    ";

    $statement = $db->prepare($query);
    $statement->bindValue(':category', $category, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->bindValue(':limit', $results_per_page, PDO::PARAM_INT);
} elseif (!empty($search)) {
    $query = "
        SELECT menu.*, category.foodtype 
        FROM menu
        LEFT JOIN category ON menu.category_id = category.id
        WHERE menu.name LIKE :search
        ORDER BY menu_item_id DESC
        LIMIT :offset, :limit
    ";

    $statement = $db->prepare($query);
    $statement->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->bindValue(':limit', $results_per_page, PDO::PARAM_INT);

} else {
    $query = "
        SELECT menu.*, category.foodtype 
        FROM menu
        LEFT JOIN category ON menu.category_id = category.id
        ORDER BY menu_item_id DESC
        LIMIT :offset, :limit
    ";

    $statement = $db->prepare($query);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->bindValue(':limit', $results_per_page, PDO::PARAM_INT);
}



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
    <link rel="stylesheet" href="styles.css?v=1.0">
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
                <li><a href="register.php">SIGN UP</a></li>
            </ul>
        </nav>
        <div class="search">
            <form method="GET" action="menu.php">
                <input type="text" name="search" placeholder="Search..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button type="submit">SEARCH</button>
            </form>
        </div>
  
    </nav>

    <!-- Header for the Products page -->
    <header id="products">
        <h1>Our Menu</h1>
    </header>
    <form method="GET" action="menu.php" id="category-filter-form">
        <label for="category">Filter by Category:</label>
        <select name="category" id="category">
            <option value="">-- All --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['id']) ?>" 
                    <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['foodtype']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Apply</button>
    </form>



    <!-- Each product block -->
    <section id="product">
        <?php if (empty($rows)): ?>
            <p style="text-align: center;">No menu items found for this category.</p>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <div class="goods">
                    <h2><?= htmlspecialchars($row['name']) ?></h2>
                    <img class="product_image" src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                    <p class="description"><?= htmlspecialchars($row['description']) ?></p>
                    <p><strong>Category:</strong> <?= htmlspecialchars($row['foodtype']) ?></p>
                    <a href="details.php?id=<?= $row['menu_item_id'] ?>">
                        <button>Details</button>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="menu.php?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>">« Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="menu.php?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>"
               <?= $i == $page ? 'class="active"' : '' ?>>
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="menu.php?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>">Next »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>


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