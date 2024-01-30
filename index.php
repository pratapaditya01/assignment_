<?php
session_start();

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=nexgi-intern-machine-test', 'username', 'password');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Login functionality
if(isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if($user) {
            if(password_verify($password, $user['password'])) {
                if($user['blocked'] == 1) {
                    $login_error = "Your user is blocked, please contact admin.";
                } elseif($user['status'] == 0) {
                    $login_error = "Your user is disabled, please contact admin.";
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                $login_error = "Invalid email or password.";
            }
        } else {
            $login_error = "Invalid email or password.";
        }
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Registration functionality
if(isset($_POST['register'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Simple email validation
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = "Invalid email format.";
    } else {
        // Check if email already exists
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if($user) {
                $register_error = "Email already exists.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user into database
                $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                $stmt->execute([$email, $hashed_password]);
                $register_success = "Registration successful. You can now login.";
            }
        } catch(PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
</head>
<body>
    <h2>Login</h2>
    <?php if(isset($login_error)) { ?>
        <p><?php echo $login_error; ?></p>
    <?php } ?>
    <form action="" method="post">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" name="login">Login</button>
    </form>

    <h2>Register</h2>
    <?php if(isset($register_error)) { ?>
        <p><?php echo $register_error; ?></p>
    <?php } ?>
    <?php if(isset($register_success)) { ?>
        <p><?php echo $register_success; ?></p>
    <?php } ?>
    <form action="" method="post">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" name="register">Register</button>
    </form>
</body>
</html>
