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

// Handle tool deletion
if (isset($_GET['delete_id'])) {
    $tool_id = (int)$_GET['delete_id'];

    // Fetch the cover image path from the database
    $stmt = $mysqli->prepare("SELECT cover_image FROM tools WHERE id = ?");
    $stmt->bind_param("i", $tool_id);
    $stmt->execute();
    $stmt->bind_result($cover_image);
    $stmt->fetch();
    $stmt->close();

    $stmt = $mysqli->prepare("DELETE FROM tools WHERE id = ?");
    $stmt->bind_param("i", $tool_id);
    if ($stmt->execute()) {
        if ($cover_image) {
            $file_path = $cover_image;
            if (file_exists($file_path)) {
                if (unlink($file_path)) {
                    $message = "Tool deleted successfully, and image removed!";
                } else {
                    $message = "Tool deleted, but failed to remove image.";
                }
            } else {
                $message = "Tool deleted, but image file does not exist.";
            }
        } else {
            $message = "Tool deleted successfully!";
        }
    } else {
        $message = "Error deleting tool. Please try again.";
    }
    $stmt->close();
}

// Fetch tools with category names
$query = "
    SELECT t.id, t.tool_name, t.link, t.description, t.tool_description, c.category_name
    FROM tools t
    LEFT JOIN categories c ON t.category_id = c.id
";
$result = $mysqli->query($query);

if (!$result) {
    die("Error fetching tools: " . $mysqli->error);
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
            margin: 50px auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn-delete {
            color: #d9534f;
        }
        .message {
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1>Manage Tools</h1>
        <?php if (isset($message)) { echo "<p class='message'>$message</p>"; } ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tool Name</th>
                    <th>Link</th>
                   
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['tool_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['link']); ?></td>
                        
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td>
                            <a href="update_tool.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-primary">Update</a>
                            <a href="manage_tools.php?delete_id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this tool?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Free the result and close the connection
$result->free();
$mysqli->close();
?>
