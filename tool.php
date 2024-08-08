<?php

include 'db.php';


function decode_id($encoded_id) {
return base64_decode($encoded_id);
}


if (!isset($_GET['tool_id']) || empty($_GET['tool_id'])) {
die("Tool ID not specified.");
}

$encoded_tool_id = trim($_GET['tool_id']);
$tool_id = decode_id($encoded_tool_id);


$query = "
SELECT tool_name, description, tool_description,link, cover_image
FROM tools
WHERE id = ?
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $tool_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
die("Tool not found.");
}

$tool = $result->fetch_assoc();
$stmt->close();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$name = trim($_POST['name']);
$content = trim($_POST['content']);
$type = $_POST['type']; 

if (empty($content) || !in_array($type, ['review', 'prompt'])) {
$error = "Content is required and type must be either 'review' or 'prompt'.";
} else {
$query = "
INSERT INTO prompts_and_reviews (user_id, tool_id, type, content, guest_name)
VALUES (?, ?, ?, ?, ?)
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("iisss", $user_id, $tool_id, $type, $content, $guest_name);

// Assuming a logged-in user; otherwise, set $user_id as NULL
$user_id = $_SESSION['user_id'] ?? NULL;
$guest_name = empty($name) ? NULL : $name;

if ($stmt->execute()) {
// Redirect to the same page after successful submission
header("Location: " . $_SERVER['PHP_SELF'] . "?tool_id=" . urlencode($encoded_tool_id));
exit();
} else {
$error = "Failed to submit $type: " . $mysqli->error;
}
$stmt->close();
}
}

// Fetch existing reviews and prompts
$query = "
SELECT p.type, p.content, p.created_at, COALESCE(p.guest_name, 'Guest') AS name
FROM prompts_and_reviews p
LEFT JOIN users u ON p.user_id = u.id
WHERE p.tool_id = ?
ORDER BY p.created_at DESC
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $tool_id);
$stmt->execute();
$reviews_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" /><link rel="stylesheet" href="style.css">
<title><?php echo htmlspecialchars($tool['tool_name']); ?></title>
<style>
 .carousel {
    max-width: 500px;
  
}

.carousel-item img {
    width: 100%;
    height: 300px;
    object-fit: cover;
}

.carousel-caption {
    background: rgba(0, 0, 0, 0.5); /* Semi-transparent black background */
    color: #fff; /* White text color */
    padding: 10px;
    border-radius: 5px;
}

.review-form input, .review-form textarea, .review-form select {
    display: block;
    width: 100%;
    margin-bottom: 10px;
    padding: 8px;
}

.review-form button {
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
}

.review-form button:hover {
    background-color: #0056b3;
}

.review-form .cancel-button {
    background-color: #6c757d;
}

.review-form .cancel-button:hover {
    background-color: #5a6268;
}

.review {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 10px;
}

.review p {
    margin: 0;
}

.review .meta {
    font-size: 0.9em;
    color: #666;
}

.info {
    display: flex;
    justify-content: space-between;
}

.tool-details img {
    width: 500px;
    height: 300px;
    border-radius: 5px;
}

.tool-details h1 {
    margin-top: 0;
}

.review-form, .reviews {
    margin-top: 20px;
}
.rev-button{
    padding:10px;
    border-radius:18px;
    background-color:#4158A6;
    border:1px solid white;
    color:white;
}
 
 
@media screen and (max-width: 800px) {
    .info {
        flex-direction: column-reverse;
    }
    .carousel {
    max-width: 100%;
  
}.carousel-item img {
    width: 100%;
    height: auto;
    object-fit: cover;
}

    .tool-details img {
        width: 98%;
        height: 200px;
        border-radius: 5px;
    }

    h1 {
        font-size: 18px;
    }

    h2 {
        font-size: 17px;
    }

    button {
        font-size: 14px;
    }
}
 

</style>
</head>
<body>
<?php include 'navbar.php'; ?>
<main>
<div class="tool-container">
    <div class="info">
        <div class="info-details">
        <h1>
    <?php echo htmlspecialchars($tool['tool_name']); ?>
    <a class="go-to" href="<?php echo htmlspecialchars($tool['link']); ?>" target="_blank">
        <i class="fa-solid fa-link"></i>
    </a>
</h1><br> 

            <div><?php echo $tool['tool_description']; ?></div>
        </div>
        <div class="tool-details">
            <?php if ($tool['cover_image']): ?>
                <img src="<?php echo htmlspecialchars($tool['cover_image']); ?>" alt="<?php echo htmlspecialchars($tool['tool_name']); ?>">
            <?php endif; ?>
            <br><br>
        </div>
    </div>
    <br>
    <div id="videoCarousel" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner" id="videoCarouselInner">
            <!-- Carousel items will be inserted here by JavaScript -->
        </div>
        <a class="carousel-control-prev" href="#videoCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#videoCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
    <br><br>
    <button onclick="toggleForm()" class="rev-button">Add Review or Prompt (Guest)</button>
    <div id="reviewForm" class="review-form" style="display: none;">
        <h2>Submit a Review or Prompt</h2>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <form action="" method="post" id="reviewForm">
            <input type="text" name="name" placeholder="Your Name (optional)">
            <textarea name="content" rows="4" placeholder="Your review or prompt" required></textarea>
            <select name="type" required>
                <option value="" disabled selected>Select type</option>
                <option value="review">Review</option>
                <option value="prompt">Prompt</option>
            </select>
            <label><strong>Note: </strong> Right now you are submitting in the guest account you can't delete or update.</label><br><br>
            <button type="submit">Submit</button>
            <button type="button" class="cancel-button" onclick="toggleForm()">Cancel</button>
        </form>
    </div>
    <br><br>
    <div class="reviews">
        <h2>Reviews and Prompts</h2>
        <?php while ($row = $reviews_result->fetch_assoc()): ?>
            <div class="review">
                <p><strong><?php echo htmlspecialchars($row['name']); ?></strong> (<?php echo htmlspecialchars($row['type']); ?>) - <span class="meta"><?php echo htmlspecialchars($row['created_at']); ?></span></p>
                <p><?php echo htmlspecialchars($row['content']); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
    <br><br><br>
</div>
</main>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
function toggleForm() {
    var form = document.getElementById('reviewForm');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
}

const toolTitle = "<?php echo htmlspecialchars($tool['tool_name']); ?>";
fetch(`http://localhost:3000/search?keyword=${encodeURIComponent(toolTitle)}`)
    .then(response => response.json())
    .then(data => {
        const videoCarouselInner = document.getElementById('videoCarouselInner');
        data.forEach((video, index) => {
            const carouselItem = document.createElement('div');
            carouselItem.className = `carousel-item ${index === 0 ? 'active' : ''}`;
            carouselItem.innerHTML = `
                <a href="${video.link}" target="_blank">
                    <img src="${video.thumbnail}" class="d-block w-100" alt="${video.title}">
                </a>
                <div class="carousel-caption d-none d-md-block">
                    <h5>${video.title}</h5>
                    <p>${video.channelName}</p>
                    <a href='${video.link}' class="watch">Watch Now!</a>
                </div>
            `;
            videoCarouselInner.appendChild(carouselItem);
        });
    })
    .catch(error => console.error('Error fetching video data:', error));
</script>


</body>
</html>
