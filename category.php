<?php
// Include database connection
include 'db.php';
 
// Function to encode tool ID
function encode_id($id) {
    return base64_encode($id);
}

// Function to decode tool ID
function decode_id($encoded_id) {
    return base64_decode($encoded_id);
}

// Check for category parameter
if (!isset($_GET['category']) || empty($_GET['category'])) {
    die("Category not specified.");
}

$category_name = trim($_GET['category']);

// Fetch tools for the selected category
$query = "
    SELECT t.id, t.tool_name, t.description, t.tool_description, t.cover_image
    FROM tools t
    JOIN categories c ON t.category_id = c.id
    WHERE c.category_name = ?
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $category_name);
$stmt->execute();
$result = $stmt->get_result();

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
     <style>
        .cards{
            display:flex;
            flex-direction:column;
            gap:10px;
            width:80%;
            margin:auto;
        }
        .category-card{
            border:1px solid black;
            display:flex;
            padding:10px;
            gap:50px;
            
        }
        .category-card img{
            width:300px;
            height:200px;
        }
        .card-content{
            display:flex;
            flex-direction:column;
            padding:10px;
        }

        @media screen and (max-width:800px){
            .cards{
                width:100%;
            }
            .category-card{
                flex-direction:column;
            }
        }
     </style>
  
</head>
<body>   <?php include 'navbar.php' ?>
<main>
    <div class="cards">
         <?php while ($row = $result->fetch_assoc()): ?>
            <div class="category-card">
                <?php if ($row['cover_image']): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($row['cover_image']); ?>" alt="<?php echo htmlspecialchars($row['tool_name']); ?>">
                <?php endif; ?>
                <div class="card-content">
                    <h2><?php echo htmlspecialchars($row['tool_name']); ?></h2>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <p><a href="tool.php?tool_id=<?php echo urlencode(encode_id($row['id'])); ?>">View Details</a></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    </main>
</body>
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
