<?php
// Start session for login management
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location:logandreg.php");
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

// Count total watched items
$countHistoryQuery = "SELECT COUNT(*) as count FROM watch_history WHERE user_id = '$userId'";
$countHistoryResult = $conn->query($countHistoryQuery);
$historyCount = $countHistoryResult->fetch_assoc()['count'];

// Count watchlist items
$countWatchlistQuery = "SELECT COUNT(*) as count FROM watchlist WHERE user_id = '$userId'";
$countWatchlistResult = $conn->query($countWatchlistQuery);
$watchlistCount = $countWatchlistResult->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MovieZone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="cssfiles/userprostyle.css"> <!-- Link to your CSS file -->
    <style>
        
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>MovieZone</h1>
                </div>
                <div class="user-menu">
                    <div class="user-info" id="userMenuButton">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($userData['username'], 0, 1)); ?>
                        </div>
                        <span><?php echo $userData['username']; ?></span>
                        <i class="fas fa-chevron-down" style="margin-left: 10px;"></i>
                    </div>
                    <div class="user-dropdown" id="userDropdown">
                        <ul>
                            <li><a href="userprodash.php"><i class="fas fa-user-circle"></i>My Profile</a></li>
                            <li><a href="watchlist.php"><i class="fas fa-bookmark"></i>My Watchlist</a></li>
                            <li><a href="history.php"><i class="fas fa-history"></i>Watch History</a></li>
                            <li class="divider"></li>
                            <li><a href="movies.php"><i class="fas fa-film"></i>Movies</a></li>
                            <li><a href="tvseries.php"><i class="fas fa-tv"></i>TV Series</a></li>
                            <li><a href="blog.php"><i class="fas fa-blog"></i>Blog</a></li>
                            <li class="divider"></li>
                            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="profile-header">
                <div class="page-title">
                    <h2>My Profile</h2>
                    <p>Manage your account and see your activity</p>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Profile Card -->
                <div class="profile-card">
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($userData['username'], 0, 1)); ?>
                        </div>
                        <h3 class="profile-name"><?php echo $userData['username']; ?></h3>
                        <p class="profile-email"><?php echo $userData['email']; ?></p>
                    </div>

                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $historyCount; ?></div>
                            <div class="stat-label">Watched</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $watchlistCount; ?></div>
                            <div class="stat-label">Watchlist</div>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <a href="watchlist.php"><i class="fas fa-bookmark" style="margin-right: 8px;"></i>My Watchlist</a>
                        <a href="history.php"><i class="fas fa-history" style="margin-right: 8px;"></i>Watch History</a>
                    </div>

                    <!-- Edit Profile Form -->
                    <div class="edit-profile-form">
                        <h3 style="margin-bottom: 20px;">Edit Profile</h3>
                        
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-success">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo $userData['username']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $userData['email']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="current_password">Current Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>

                <!-- Dashboard Sections -->
                <div class="dashboard-sections">
                    <!-- Watch History Section -->
                    <div class="section-card">
                        <div class="section-header">
                            <h3 class="section-title">Recent Watch History</h3>
                            <a href="history.php" class="view-all">View All <i class="fas fa-chevron-right"></i></a>
                        </div>
                        
                        <?php if ($historyResult->num_rows > 0): ?>
                            <div class="content-grid">
                                <?php while ($historyItem = $historyResult->fetch_assoc()): ?>
                                    <div class="content-item">
                                        <img src="../uploads/<?php echo $historyItem['image']; ?>" alt="<?php echo $historyItem['title']; ?>" class="content-image">
                                        <div class="content-overlay">
                                            <div class="content-title"><?php echo $historyItem['title']; ?></div>
                                            <div class="content-info">
                                                <span><?php echo ucfirst($historyItem['type']); ?></span>
                                                <span><?php echo date("M d, Y", strtotime($historyItem['watched_at'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-film"></i>
                                <p>No watch history found. Start watching to see your history here!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Watchlist Section -->
                    <div class="section-card">
                        <div class="section-header">
                            <h3 class="section-title">My Watchlist</h3>
                            <a href="watchlist.php" class="view-all">View All <i class="fas fa-chevron-right"></i></a>
                        </div>
                        
                        <?php if ($watchlistResult->num_rows > 0): ?>
                            <div class="content-grid">
                                <?php while ($watchlistItem = $watchlistResult->fetch_assoc()): ?>
                                    <div class="content-item">
                                        <img src="../uploads/<?php echo $watchlistItem['image']; ?>" alt="<?php echo $watchlistItem['title']; ?>" class="content-image">
                                        <div class="content-overlay">
                                            <div class="content-title"><?php echo $watchlistItem['title']; ?></div>
                                            <div class="content-info">
                                                <span><?php echo ucfirst($watchlistItem['type']); ?></span>
                                                <span><?php echo date("M d, Y", strtotime($watchlistItem['added_at'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-bookmark"></i>
                                <p>Your watchlist is empty. Add content to your watchlist!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Recently Added Content -->
                    <div class="section-card">
                        <div class="section-header">
                            <h3 class="section-title">Recently Added</h3>
                            <a href="browse.php" class="view-all">View All <i class="fas fa-chevron-right"></i></a>
                        </div>
                        
                        <?php if ($recentContentResult->num_rows > 0): ?>
                            <div class="content-grid">
                                <?php while ($contentItem = $recentContentResult->fetch_assoc()): ?>
                                    <div class="content-item">
                                        <img src="../uploads/<?php echo $contentItem['image']; ?>" alt="<?php echo $contentItem['title']; ?>" class="content-image">
                                        <div class="content-overlay">
                                            <div class="content-title"><?php echo $contentItem['title']; ?></div>
                                            <div class="content-info">
                                                <span><?php echo ucfirst($contentItem['type']); ?></span>
                                                <span><?php echo date("M d, Y", strtotime($contentItem['date'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-video"></i>
                                <p>No content available yet. Check back soon!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // User dropdown menu toggle
        document.getElementById('userMenuButton').addEventListener('click', function() {
            document.getElementById('userDropdown').classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenuButton');
            const userDropdown = document.getElementById('userDropdown');
            
            if (!userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.remove('active');
            }
        });
    </script>
</body>
</html>