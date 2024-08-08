<?php
// Include database connection
include 'db.php';
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Initialize variables
$user_type = '';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Prepare and execute query to fetch user type
    if ($stmt = $mysqli->prepare("SELECT type FROM users WHERE id = ?")) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($user_type);
        $stmt->fetch();
        $stmt->close();
    } else {
        // Error preparing statement
        echo "Error preparing statement: " . $mysqli->error;
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php">
        <h3>AITOOLS</h3>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="tools.php">Tools</a>
            </li>
            <?php if (isset($_SESSION['user_id']) && $user_type === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                </li>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
 