<?php
// Include database connection
include 'db.php';
session_start();

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

// Handle tool deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $tool_id = intval($_GET['delete']);

    // Prepare statement to delete tool
    $stmt = $mysqli->prepare("DELETE FROM tools WHERE id = ?");
    $stmt->bind_param("i", $tool_id);

    if ($stmt->execute()) {
        echo "<p>Tool deleted successfully!</p>";
    } else {
        echo "<p>Error deleting tool. Please try again.</p>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
    <title>Manage Tools</title>
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f4f4f4;
        }
        .update-btn, .delete-btn {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .update-btn {
            color: green;
        }
        .delete-btn {
            color: red;
        }
        .update-btn:hover {
            background-color: #e0f0e0;
        }
        .delete-btn:hover {
            background-color: #f0e0e0;
        }
    </style>
</head>
<body>   <?php include 'navbar.php' ?>
    <div class="container">
        <h1>Manage Tools</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tool Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch tools from the database
                $stmt = $mysqli->prepare("
                    SELECT tools.id, tools.tool_name, tools.description, categories.category_name
                    FROM tools
                    LEFT JOIN categories ON tools.category_id = categories.id
                ");
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['tool_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                    echo "<td>
                        <a href='update_tool.php?id=" . htmlspecialchars($row['id']) . "' class='update-btn'>Update</a>
                        <a href='manage_tools.php?delete=" . htmlspecialchars($row['id']) . "' class='delete-btn' onclick=\"return confirm('Are you sure you want to delete this tool?');\">Delete</a>
                    </td>";
                    echo "</tr>";
                }

                $stmt->close();
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</body>
</html>
