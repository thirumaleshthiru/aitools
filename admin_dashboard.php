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
            margin-top: 50px;
            padding: 20px;
        }
        .dashboard h1 {
            margin-bottom: 30px;
        }
        .dashboard ul {
            padding-left: 0;
        }
        .dashboard ul li {
            margin-bottom: 15px;
        }
        .dashboard ul li a {
            display: block;
            padding: 15px;
            font-size: 16px;
            color: #ffffff;
            background-color: #007bff;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.2s;
        }
        .dashboard ul li a:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        .dashboard ul li a:active {
            background-color: #00408d;
            transform: translateY(0);
        }
        .dashboard .logout-link {
            text-align: center;
            margin-top: 20px;
        }
        .dashboard .logout-link a {
            color: #007bff;
            text-decoration: none;
        }
        .dashboard .logout-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container dashboard">
        <h1 class="text-center">Admin Dashboard</h1>
        <ul class="list-unstyled">
            <li><a href="add_tool.php" class="btn btn-primary">Add Tool</a></li>
            <li><a href="add_category.php" class="btn btn-primary">Add Category</a></li>
            <li><a href="manage_tools.php" class="btn btn-primary">Manage Tools</a></li>
            <li><a href="manage_categories.php" class="btn btn-primary">Manage Categories</a></li>
        </ul>
        <div class="logout-link">
            <p><a href="logout.php">Logout</a></p>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
