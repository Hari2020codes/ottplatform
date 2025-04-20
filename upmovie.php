<?php
// Start session for login management
session_start();

// Check if user is logged in
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
$message = '';
$error = '';

// Get all genres for dropdown
$genreQuery = "SELECT DISTINCT genre FROM users WHERE genre IS NOT NULL AND genre != ''";
$genreResult = $conn->query($genreQuery);
$genres = [];

if ($genreResult->num_rows > 0) {
    while ($row = $genreResult->fetch_assoc()) {
        if (!empty($row['genre'])) {
            $genres[] = $row['genre'];
        }
    }
}

// Upload Process
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $director = $conn->real_escape_string($_POST['director']);
    $genre = $conn->real_escape_string($_POST['genre']);
    $type = $conn->real_escape_string($_POST['type']);
    $content = $conn->real_escape_string($_POST['content']);
    $releaseDate = $conn->real_escape_string($_POST['release_date']);
    $customGenre = isset($_POST['custom_genre']) ? $conn->real_escape_string($_POST['custom_genre']) : '';
    
    // Use custom genre if provided
    if (!empty($customGenre)) {
        $genre = $customGenre;
    }
    
    // Check if title already exists
    $checkQuery = "SELECT id FROM users WHERE title = '$title' AND director = '$director'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        $error = "A movie with this title and director already exists.";
    } else {
        // File uploads
        $videoFile = $_FILES['video_file'];
        $thumbnailFile = $_FILES['thumbnail_file'];
        
        $videoFileName = '';
        $thumbnailFileName = '';
        $uploadOk = true;
        
        // Video file validation and upload
        if ($videoFile['size'] > 0) {
            $targetVideoDir = "../uploads/";
            $videoFileType = strtolower(pathinfo($videoFile['name'], PATHINFO_EXTENSION));
            $videoFileName = uniqid() . '_' . $title . '.' . $videoFileType;
            $targetVideoFile = $targetVideoDir . $videoFileName;
            
            // Check file size (500MB max)
            if ($videoFile['size'] > 500000000) {
                $error = "Sorry, your video file is too large.";
                $uploadOk = false;
            }
            
            // Allow certain file formats
            if ($videoFileType != "mp4" && $videoFileType != "avi" && $videoFileType != "mov" && $videoFileType != "mkv") {
                $error = "Sorry, only MP4, AVI, MOV & MKV files are allowed for videos.";
                $uploadOk = false;
            }
            
            // Upload file if everything is ok
            if ($uploadOk && !move_uploaded_file($videoFile['tmp_name'], $targetVideoFile)) {
                $error = "Sorry, there was an error uploading your video file.";
                $uploadOk = false;
            }
        } else {
            $error = "Video file is required.";
            $uploadOk = false;
        }
        
        // Thumbnail file validation and upload
        if ($thumbnailFile['size'] > 0) {
            $targetImageDir = "../uploads2/";
            $imageFileType = strtolower(pathinfo($thumbnailFile['name'], PATHINFO_EXTENSION));
            $thumbnailFileName = uniqid() . '_' . $title . '.' . $imageFileType;
            $targetImageFile = $targetImageDir . $thumbnailFileName;
            
            // Check file size (5MB max)
            if ($thumbnailFile['size'] > 5000000) {
                $error = "Sorry, your image file is too large.";
                $uploadOk = false;
            }
            
            // Allow certain file formats
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                $error = "Sorry, only JPG, JPEG & PNG files are allowed for thumbnails.";
                $uploadOk = false;
            }
            
            // Upload file if everything is ok
            if ($uploadOk && !move_uploaded_file($thumbnailFile['tmp_name'], $targetImageFile)) {
                $error = "Sorry, there was an error uploading your thumbnail file.";
                $uploadOk = false;
            }
        } else {
            $error = "Thumbnail image is required.";
            $uploadOk = false;
        }
        
        // Insert into database if uploads are successful
        if ($uploadOk) {
            $insertQuery = "INSERT INTO users (title, director, video, image, content, genre, type, date) 
                          VALUES ('$title', '$director', '$videoFileName', '$thumbnailFileName', '$content', '$genre', '$type', '$releaseDate')";
                          
            if ($conn->query($insertQuery) === TRUE) {
                $message = "Movie/Series uploaded successfully!";
                
                // Reset the form
                $_POST = array();
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Content - MovieZone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #0d47a1;
            --text-light: #ffffff;
            --text-dark: #333333;
            --bg-dark: #121212;
            --bg-lighter: #1e1e1e;
            --accent-color: #ff4081;
            --success-color: #00c853;
            --warning-color: #ffd600;
            --danger-color: #ff1744;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--bg-dark);
            color: var(--text-light);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles */
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 15px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo h1 {
            color: var(--text-light);
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 0;
        }
        
        .admin-nav {
            display: flex;
            gap: 15px;
        }
        
        .admin-nav a {
            color: var(--text-light);
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            background-color: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .admin-nav a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        /* Main Content */
        .main-content {
            padding: 30px 0;
        }
        
        .page-title {
            margin-bottom: 30px;
        }
        
        .page-title h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .page-title p {
            color: rgba(255, 255, 255, 0.7);
        }
        
        /* Upload Form Card */
        .upload-card {
            background-color: var(--bg-lighter);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .form-section {
            margin-bottom: 25px;
        }
        
        .form-section h3 {
            margin-bottom: 15px;
            font-size: 1.3rem;
            color: var(--accent-color);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            color: var(--text-light);
            transition: all 0.3s ease;
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 15px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-color);
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .form-check input[type="checkbox"] {
            margin-right: 10px;
        }
        
        .custom-file {
            position: relative;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .custom-file-input {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }
        
        .custom-file-label {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px dashed rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .custom-file-label i {
            margin-right: 10px;
        }
        
        .custom-file:hover .custom-file-label {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: var(--accent-color);
        }
        
        .file-info {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #e91e63;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        
        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .alert-success {
            background-color: rgba(0, 200, 83, 0.2);
            color: var(--success-color);
            border: 1px solid rgba(0, 200, 83, 0.3);
        }
        
        .alert-danger {
            background-color: rgba(255, 23, 68, 0.2);
            color: var(--danger-color);
            border: 1px solid rgba(255, 23, 68, 0.3);
        }
        
        /* Conditional visibility */
        #customGenreField {
            display: none;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .admin-nav {
                display: none;
            }
            
            .mobile-menu {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Header with Logo and Admin Navigation -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>MovieZone Admin</h1>
                </div>
                <div class="admin-nav">
                    <a href="admindash.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="upload.php" class="active"><i class="fas fa-upload"></i> Upload</a>
                    <a href="manage_content.php"><i class="fas fa-film"></i> Content</a>
                    <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="page-title">
                <h2>Upload Content</h2>
                <p>Add new movies, TV series episodes, or blog posts to the platform</p>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-