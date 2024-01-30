<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=nexgi_intern_machine_test', 'username', 'password');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch user's blog posts (assuming you have a 'posts' table)
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $posts = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// CRUD operations
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['create_post'])) {
        $post_title = $_POST['post_title'];
        $post_content = $_POST['post_content'];

        try {
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, post_title, post_content) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $post_title, $post_content]);
            header("Location: dashboard.php");
            exit();
        } catch(PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    } elseif(isset($_POST['update_post'])) {
        $post_id = $_POST['post_id'];
        $post_title = $_POST['post_title'];
        $post_content = $_POST['post_content'];

        try {
            $stmt = $pdo->prepare("UPDATE posts SET post_title = ?, post_content = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$post_title, $post_content, $post_id, $_SESSION['user_id']]);
            header("Location: dashboard.php");
            exit();
        } catch(PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    } elseif(isset($_POST['delete_post'])) {
        $post_id = $_POST['post_id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
            $stmt->execute([$post_id, $_SESSION['user_id']]);
            header("Location: dashboard.php");
            exit();
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
    <title>Dashboard</title>
</head>
<body>
    <h2>Welcome to Your Dashboard</h2>
    <h3>Create New Post</h3>
    <form action="" method="post">
        <input type="text" name="post_title" placeholder="Post Title" required><br>
        <textarea name="post_content" placeholder="Post Content" rows="4" required></textarea><br>
        <button type="submit" name="create_post">Create Post</button>
    </form>

    <h3>Your blog posts:</h3>
    <ul>
    <?php foreach($posts as $post) { ?>
        <li>
            <strong><?php echo $post['post_title']; ?></strong><br>
            <?php echo $post['post_content']; ?><br>
            <form action="" method="post">
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                <input type="text" name="post_title" value="<?php echo $post['post_title']; ?>" required><br>
                <textarea name="post_content" rows="4" required><?php echo $post['post_content']; ?></textarea><br>
                <button type="submit" name="update_post">Update</button>
                <button type="submit" name="delete_post">Delete</button>
            </form>
        </li>
    <?php } ?>
    </ul>
    <a href="logout.php">Logout</a>
</body>
</html>
