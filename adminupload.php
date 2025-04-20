<?php
// Start session for login management
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../logandreg.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "movies";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$title = $director = $genre = $content = $type = '';
$message = '';
$error = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $director = $conn->real_escape_string($_POST['director']);
    $genre = $conn->real_escape_string($_POST['genre']);
    $content = $conn->real_escape_string($_POST['content']);
    $type = $conn->real_escape_string($_POST['type']);
    $date = date("Y-m-d");
    
    // Check if all required fields are filled
    if (empty($title) || empty($director) || empty($genre) || empty($content) || empty($type)) {
        $error = "All fields are required";
    } else {
        // Upload video file
        $target_dir_video = "../uploads/";
        $video_file = $_FILES["video"]["name"];
        $video_tmp = $_FILES["video"]["tmp_name"];
        $video_ext = strtolower(pathinfo($video_file, PATHINFO_EXTENSION));
        $video_new_name = uniqid() . "." . $video_ext;
        $video_upload_path = $target_dir_video . $video_new_name;
        
        // Upload image file
        $target_dir_image = "../uploads2/";
        $image_file = $_FILES["image"]["name"];
        $image_tmp = $_FILES["image"]["tmp_name"];
        $image_ext = strtolower(pathinfo($image_file, PATHINFO_EXTENSION));
        $image_new_name = uniqid() . "." . $image_ext;
        $image_upload_path = $target_dir_image . $image_new_name;
        
        // Check file extensions
        $allowed_video = array("mp4", "avi", "mov", "wmv", "flv", "mkv");
        $allowed_image = array("jpg", "jpeg", "png", "gif");
        
        if (!in_array($video_ext, $allowed_video)) {
            $error = "Video file type not allowed. Please upload: " . implode(", ", $allowed_video);
        } elseif (!in_array($image_ext, $allowed_image)) {
            $error = "Image file type not allowed. Please upload: " . implode(", ", $allowed_image);
        } else {
            // Move uploaded files to destination directories
            if (move_uploaded_file($video_tmp, $video_upload_path) && move_uploaded_file($image_tmp, $image_upload_path)) {
                // Insert data into database
                $sql = "INSERT INTO users (title, director, genre, content, video, image, date, type) 
                        VALUES ('$title', '$director', '$genre', '$content', '$video_new_name', '$image_new_name', '$date', '$type')";
                
                if ($conn->query($sql) === TRUE) {
                    $message = "Content uploaded successfully!";
                    // Clear form fields after successful upload
                    $title = $director = $genre = $content = $type = '';
                } else {
                    $error = "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                $error = "Error uploading files";
            }
        }
    }
}

// Get all genres for dropdown
$genresQuery = "SELECT DISTINCT genre FROM users ORDER BY genre ASC";
$genresResult = $conn->query($genresQuery);
$genres = [];

if ($genresResult && $genresResult->num_rows > 0) {
    while ($row = $genresResult->fetch_assoc()) {
        $genres[] = $row['genre'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Content - MovieZone Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="cssfiles/adminupstyle2.css">
    <link rel="stylesheet" href="cssfiles/adminupstyle.css">
    <style>
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>MovieZone</h1>
            <p>Admin Panel</p>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="admindash.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="movies.php">
                    <i class="fas fa-film"></i>
                    <span>Movies</span>
                </a>
            </li>
            <li>
                <a href="series.php">
                    <i class="fas fa-tv"></i>
                    <span>TV Series</span>
                </a>
            </li>
            <li>
                <a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="blogs.php">
                    <i class="fas fa-blog"></i>
                    <span>Blogs</span>
                </a>
            </li>
            <li>
                <a href="categories.php">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li>
                <a href="adminupload.php" class="active">
                    <i class="fas fa-upload"></i>
                    <span>Upload Content</span>
                </a>
            </li>
            <li>
                <a href="settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content  USER avatar and admin name-->
    <div class="main-content">
        <div class="page-header">
            <h2>Upload New Content</h2>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo $_SESSION['username']; ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>
        
        <div class="section">
            <!-- Alert Messages -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Upload Form -->
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-control" value="<?php echo $title; ?>" placeholder="Enter title">
                </div>
                
                <div class="form-group">
                    <label for="director">Director</label>
                    <input type="text" id="director" name="director" class="form-control" value="<?php echo $director; ?>" placeholder="Enter director name">
                </div>
                
                <div class="form-group">
                    <label for="genre">Genre</label>
                    <select id="genre" name="genre" class="form-control">
                        <option value="">Select Genre</option>
                        <?php
                        // Add option to create new genre
                        echo '<option value="new">+ Add New Genre</option>';
                        
                        // List existing genres
                        foreach ($genres as $existingGenre) {
                            $selected = ($genre === $existingGenre) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($existingGenre) . '" ' . $selected . '>' . htmlspecialchars($existingGenre) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <!-- Hidden new genre input field that shows when "Add New Genre" is selected -->
                <div class="form-group" id="new-genre-group" style="display: none;">
                    <label for="new-genre">New Genre Name</label>
                    <input type="text" id="new-genre" name="new_genre" class="form-control" placeholder="Enter new genre name">
                </div>
                
                <div class="form-group">
                    <label for="type">Content Type</label>
                    <select id="type" name="type" class="form-control">
                        <option value="">Select Type</option>
                        <option value="movie" <?php echo ($type === 'movie') ? 'selected' : ''; ?>>Movie</option>
                        <option value="series" <?php echo ($type === 'series') ? 'selected' : ''; ?>>TV Series</option>
                    </select>
                </div>
                
                <!-- Season and Episode fields (for TV Series) -->
                <div id="series-fields" style="display: <?php echo ($type === 'series') ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label for="season">Season</label>
                        <input type="number" id="season" name="season" class="form-control" min="1" placeholder="Season number">
                    </div>
                    
                    <div class="form-group">
                        <label for="episode">Episode</label>
                        <input type="number" id="episode" name="episode" class="form-control" min="1" placeholder="Episode number">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="content">Description</label>
                    <textarea id="content" name="content" class="form-control" placeholder="Enter content description"><?php echo $content; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Video File</label>
                    <div class="file-input-group">
                        <input type="file" id="video" name="video" accept=".mp4,.avi,.mov,.wmv,.flv,.mkv">
                        <label for="video" class="file-input-label">
                            <i class="fas fa-video"></i>
                            <span>Choose Video File</span>
                        </label>
                    </div>
                    <div class="selected-file" id="selected-video">No file selected</div>
                    <small>Allowed formats: MP4, AVI, MOV, WMV, FLV, MKV</small>
                </div>
                
                <div class="form-group">
                    <label>Thumbnail Image</label>
                    <div class="file-input-group">
                        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif">
                        <label for="image" class="file-input-label">
                            <i class="fas fa-image"></i>
                            <span>Choose Thumbnail Image</span>
                        </label>
                    </div>
                    <div class="selected-file" id="selected-image">No file selected</div>
                    <small>Allowed formats: JPG, JPEG, PNG, GIF</small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='admindash.php'">Cancel</button>
                    <button type="submit" class="btn">Upload Content</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // File input display
        document.getElementById('video').addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
            document.getElementById('selected-video').textContent = fileName;
        });
        
        document.getElementById('image').addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
            document.getElementById('selected-image').textContent = fileName;
        });
        
        // Show/hide series-specific fields
        document.getElementById('type').addEventListener('change', function() {
            var seriesFields = document.getElementById('series-fields');
            if (this.value === 'series') {
                seriesFields.style.display = 'block';
            } else {
                seriesFields.style.display = 'none';
            }
        });
        
        // Show/hide new genre field
        document.getElementById('genre').addEventListener('change', function() {
            var newGenreGroup = document.getElementById('new-genre-group');
            if (this.value === 'new') {
                newGenreGroup.style.display = 'block';
            } else {
                newGenreGroup.style.display = 'none';
            }
        });
        
        // Toggle user menu dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const userInfo = document.querySelector('.user-info');
            if (userInfo) {
                userInfo.addEventListener('click', function() {
                    // Add dropdown menu toggle logic here
                    console.log('User menu clicked');
                });
            }
        });
    </script>
</body>
</html>