<?php
// Include database connection
include 'db.php';

// Fetch all categories with tool count greater than 0
$query = "
SELECT c.id, c.category_name, COUNT(t.id) AS tool_count
FROM categories c
LEFT JOIN tools t ON c.id = t.category_id
GROUP BY c.id, c.category_name
HAVING COUNT(t.id) > 0
";
$result = $mysqli->query($query);

if (!$result) {
die("Error fetching categories: " . $mysqli->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tools</title>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
.category-list {
    list-style-type: none;
    padding: 10px;  
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));  
    gap: 70px;  
    margin-top:5%;
}

.category {
    border: 1px solid black;
    padding: 9px;
    display: flex;
    gap: 10px;
    justify-content: space-around;
    align-items: center;
    border-radius:18px;
     
}

.category a {
    text-decoration: none;
}

.category span {
    border: 1px solid black;
    border-radius: 50%;
    padding: 10px;
    width: 20px;  
    height: 20px;  
    display: flex;
    justify-content: center;
    align-items: center;
}

</style>
</head>
<body>
<?php include 'navbar.php'; ?>

<main>
<div class="container">
<div class="category-list">
<?php while ($row = $result->fetch_assoc()): ?>
<div class="category">
<a href="category.php?category=<?php echo urlencode($row['category_name']); ?>">
<?php echo htmlspecialchars($row['category_name']); ?> 
</a>
<span><?php echo $row['tool_count']; ?></span>
</div>
<?php endwhile; ?>
</div>
</div>
</main>

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
