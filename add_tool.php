<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_type);
$stmt->fetch();
$stmt->close();

if ($user_type !== 'admin') {
    header("Location: login.php");
    exit();
}

$tool_name = $description = $tool_description = $category_id = $link = '';
$errors = array();
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tool_name = trim($_POST['tool_name']);
    $description = trim($_POST['description']);
    $tool_description = $_POST['tool_description'];
    $category_id = trim($_POST['category_id']);
    $link = trim($_POST['link']);

    $cover_image_path = null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = basename($_FILES['cover_image']['name']);
        $target_file = $upload_dir . uniqid() . '_' . $file_name;
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
            $cover_image_path = $target_file;
        } else {
            $errors[] = "Error uploading cover image.";
        }
    }

    if (empty($tool_name)) {
        $errors[] = "Tool name is required.";
    }
    if (empty($description)) {
        $errors[] = "Description is required.";
    }
    if (empty($tool_description)) {
        $errors[] = "Tool description is required.";
    }
    if (empty($category_id)) {
        $errors[] = "Category is required.";
    }
    if(empty($link)){
        $errors[] = "Tool Link is required.";
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO tools (tool_name, description, tool_description, cover_image, category_id, link) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $tool_name, $description, $tool_description, $cover_image_path, $category_id, $link);

        if ($stmt->execute()) {
            $success = true;
            $tool_name = $description = $tool_description = $category_id = '';
        } else {
            $errors[] = "Error adding tool. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Tool</title>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<style>
h1 {
    margin-bottom: 20px;
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
.form-group input, .form-group select {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
}
.form-group textarea {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
}
.form-group #editor-container {
    height: 200px;
}
button {
    padding: 10px 20px;
    background-color: #C75B7A;
    color: #fff;
    border: none;
    cursor: pointer;
    font-size: 16px;
}
button:hover {
    background-color: #4cae4c;
}
.error {
    color: #d9534f;
    font-weight: bold;
}
.success {
    color: #5cb85c;
    font-weight: bold;
}
</style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
    <h1>Add Tool</h1>
    <div id="messages">
        <?php
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo "<p class='error'>$error</p>";
            }
        }

        if ($success) {
            echo "<p class='success'>Tool added successfully!</p>";
        }
        ?>
    </div>
    <form id="tool-form" action="add_tool.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="tool_name">Tool Name:</label>
            <input type="text" id="tool_name" name="tool_name" value="<?php echo htmlspecialchars($tool_name); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($description); ?>" required>
        </div>
        <div class="form-group">
            <label for="tool_description">Tool Description:</label>
            <div id="editor-container"></div>
            <textarea name="tool_description" id="tool_description" style="display:none;"></textarea>
        </div>
        <div class="form-group">
            <label for="link">Tool Link:</label>
            <input type="text" id="link" name="link" value="<?php echo htmlspecialchars($link); ?>" required>
        </div>
        <div class="form-group">
            <label for="cover_image">Cover Image:</label>
            <input type="file" id="cover_image" name="cover_image">
        </div>
        <div class="form-group">
            <label for="category_id">Category:</label>
            <select id="category_id" name="category_id" required>
                <?php
                $result = $mysqli->query("SELECT id, category_name FROM categories");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value=\"" . htmlspecialchars($row['id']) . "\">" . htmlspecialchars($row['category_name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit">Add Tool</button>
    </form>
</div>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
var quill = new Quill('#editor-container', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, false] }],
            ['bold', 'italic', 'underline'],
            ['link', 'image'],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'color': [] }, { 'background': [] }]
        ]
    }
});

document.querySelector('form').addEventListener('submit', function(event) {
    var toolDescription = document.querySelector('textarea[name="tool_description"]');
    toolDescription.value = quill.root.innerHTML;
    if (toolDescription.value === '<p><br></p>') {
        event.preventDefault();
        alert('Tool description cannot be empty.');
    }
});

if (<?php echo json_encode($success); ?>) {
    document.getElementById('tool-form').reset();
    quill.setContents([{ insert: '\n' }]);
}
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
