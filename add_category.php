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

// Initialize variables
$category_name = '';
$errors = array();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form input
    $category_name = trim($_POST['category_name']);

    // Validate input
    if (empty($category_name)) {
        $errors[] = "Category name is required.";
    } else {
        // Check if category already exists
        $stmt = $mysqli->prepare("SELECT id FROM categories WHERE category_name = ?");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Category already exists.";
        }
        $stmt->close();
    }

    // Insert category into database if no errors
    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);
        
        if ($stmt->execute()) {
            echo "Category added successfully!";
        } else {
            $errors[] = "Error adding category. Please try again.";
        }
        $stmt->close();
    }
}

// Display errors if any
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
    <title>Add Category</title>
    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body><?php include 'navbar.php' ?>
    <div class="container">
        <h1>Add Category</h1>
        <form action="add_category.php" method="post">
            <div class="form-group">
                <label for="category_name">Category Name:</label>
                <input type="text" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category_name); ?>" required>
            </div>
            <button type="submit">Add Category</button>
        </form>
    </div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</body>
</html>
