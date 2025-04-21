<?php
require('connect.php');
session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$username) {
        $message = "Please enter a username.";
    } elseif (!$email) {
        $message = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } else {
        $check = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $check->bindValue(':email', $email);
        $check->execute();

        if ($check->fetchColumn() > 0) {
            $message = "This email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insert = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $insert->bindValue(':username', $username);
            $insert->bindValue(':email', $email);
            $insert->bindValue(':password', $hashedPassword);
            if ($insert->execute()) {
                $message = "Registration successful! You can now <a href='login.php'>log in</a>.";
            } else {
                $message = "An error occurred during registration.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <link rel="stylesheet" href="styles.css?v=1.0">
</head>
<body>
    <h1>Register an Account</h1>

    <?php if (!empty($message)): ?>
        <p style="color: <?= strpos($message, 'successful') !== false ? 'green' : 'red' ?>;">
            <?= $message ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label for="username">Username:</label><br>
        <input type="text" name="username" id="username" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" name="email" id="email" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" name="password" id="password" required><br><br>

        <label for="confirm_password">Confirm Password:</label><br>
        <input type="password" name="confirm_password" id="confirm_password" required><br><br>

        <button type="submit" name="register">Register</button>
    </form>


    <p><a href="login.php">Already have an account? Log in here.</a></p>
</body>
</html>
