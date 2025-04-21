<?php
require('connect.php');
session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $message = "Please enter both email and password.";
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

     if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Store role in session
        $_SESSION['welcome_message'] = "Welcome, {$user['username']}!";
        header('Location: menu.php');
        exit;
    } else {
            $message = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="main.css?v=<?= time(); ?>">
</head>
<body>
    <h1>Login</h1>

    <?php if (!empty($message)): ?>
        <p style="color: red;"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="email">Email:</label><br>
        <input type="email" name="email" id="email" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" name="password" id="password" required><br><br>

        <button type="submit" name="login">Login</button>
    </form>

    <p><a href="register.php">Don't have an account? Register here.</a></p>
</body>
</html>
