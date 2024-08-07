<?php
// Include database connection
include 'db.php';
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details to check if the user is an admin
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_type);
$stmt->fetch();
$stmt->close();

// Redirect to login if not an admin
if ($user_type !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
    <title>Admin Dashboard</title>
    <style>
        
        .dashboard {
            max-width: 600px;
            margin: 0 auto;
        }
        .dashboard h1 {
            text-align: center;
        }
        .dashboard ul {
            list-style-type: none;
            padding: 0;
        }
        .dashboard ul li {
            margin: 10px 0;
        }
        .dashboard ul li a {
            text-decoration: none;
            color: #007bff;
        }
        .dashboard ul li a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php' ?>
    <div class="dashboard">
        
        <ul>
            <li><a href="add_tool.php">Add Tool</a></li>
            <li><a href="add_category.php">Add Category</a></li>
            <li><a href="manage_tools.php">Manage Tools</a></li>
            <li><a href="manage_categories.php">Manage Categories</a></li>
        </ul>
        <p><a href="logout.php">Logout</a></p>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</body>
</html>
