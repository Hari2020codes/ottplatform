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
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #0d47a1;
            --text-light: #ffffff;
            --text-dark: #333333;
            --bg-dark: #121212;
            --bg-lighter: #1e1e1e;
            --accent-color: #ff4081;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --info-color: #2196f3;
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
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: var(--bg-lighter);
            padding: 20px 0;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.2);
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 20px 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .sidebar-header h1 {
            font-size: 1.5rem;
            color: var(--accent-color);
        }
        
        .sidebar-header p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 5px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.05);
            border-left-color: var(--accent-color);
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .page-header h2 {
            font-size: 1.8rem;
            color: var(--text-light);
        }
        
        .section {
            background-color: var(--bg-lighter);
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: rgba(76, 175, 80, 0.2);