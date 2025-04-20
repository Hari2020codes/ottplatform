<?php
// Start session for login management
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
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

// Get user information
$userId = $_SESSION['user_id'];
$userQuery = "SELECT * FROM userd WHERE id = '$userId'";
$userResult = $conn->query($userQuery);
$userData = $userResult->fetch_assoc();

// Get user's watch history
$historyQuery = "SELECT w.*, u.title, u.image, u.type FROM watch_history w 
                JOIN users u ON w.content_id = u.id 
                WHERE w.user_id = '$userId' 
                ORDER BY w.watched_at DESC 
                LIMIT 6";
$historyResult = $conn->query($historyQuery);

// Get user's watchlist
$watchlistQuery = "SELECT wl.*, u.title, u.image, u.type FROM watchlist wl 
                 JOIN users u ON wl.content_id = u.id 
                 WHERE wl.user_id = '$userId' 
                 ORDER BY wl.added_at DESC 
                 LIMIT 6";
$watchlistResult = $conn->query($watchlistQuery);

// Get recently added content
$recentContentQuery = "SELECT id, title, image, type, date FROM users 
                     ORDER BY date DESC 
                     LIMIT 6";
$recentContentResult = $conn->query($recentContentQuery);

// Profile update message
$message = '';
$error = '';

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Check if username or email already exists for other users
    $checkQuery = "SELECT * FROM userd WHERE (username = '$username' OR email = '$email') AND id != '$userId'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
        $error = "Username or email already exists";
    } else {
        // Verify current password
        if (!empty($currentPassword)) {
            // In a production environment, use password_verify
            if ($currentPassword == $userData['password']) {
                // Check if new passwords match
                if ($newPassword == $confirmPassword) {
                    // In a production environment, use password_hash
                    $updateQuery = "UPDATE userd SET username = '$username', email = '$email', password = '$newPassword' WHERE id = '$userId'";
                } else {
                    $error = "New passwords do not match";
                }
            } else {
                $error = "Current password is incorrect";
            }
        } else {
            $updateQuery = "UPDATE userd SET username = '$username', email = '$email' WHERE id = '$userId'";
        }
        
        // Execute update if no errors
        if (empty($error) && isset($updateQuery)) {
            if ($conn->query($updateQuery) === TRUE) {
                $_SESSION['username'] = $username;
                $message = "Profile updated successfully!";
                
                // Refresh user data
                $userResult = $conn->query($userQuery);
                $userData = $userResult->fetch_assoc();
            } else {
                $error = "Error updating profile: " . $conn->error;
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
    <title>My Profile - MovieZone</title>
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
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles */
        header {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo h1 {
            font-size: 1.8rem;
            color: var(--accent-color);
            letter-spacing: 1px;
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .user-info:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: var(--bg-lighter);
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            min-width: 200px;
            z-index: 10;
            display: none;
        }
        
        .user-dropdown.active {
            display: block;
        }
        
        .user-dropdown ul {
            list-style: none;
            padding: 10px 0;
        }
        
        .user-dropdown li {
            padding: 0;
        }
        
        .user-dropdown a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .user-dropdown a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .user-dropdown i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .divider {
            height: 1px;
            background-color: rgba(255, 255, 255, 0.1);
            margin: 8px 0;
        }
        
        /* Main Content */
        .main-content {
            padding: 30px 0;
        }
        
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-title h2 {
            font-size: 2rem;
            font-weight: 600;
        }
        
        .page-title p {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 5px;
        }
        
        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        
        /* Profile Card */
        .profile-card {
            background-color: var(--bg-lighter);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .profile-info {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 600;
            margin: 0 auto 15px;
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .profile-email {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-item {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--accent-color);
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .profile-actions a {
            display: