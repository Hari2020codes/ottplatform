<?php
// Start session for login management
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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

// Get statistics
$totalMoviesQuery = "SELECT COUNT(*) as total FROM users WHERE type='movie'";
$totalSeriesQuery = "SELECT COUNT(*) as total FROM users WHERE type='series'";
$totalUsersQuery = "SELECT COUNT(*) as total FROM userd WHERE role='user'";
$totalBlogsQuery = "SELECT COUNT(*) as total FROM blogs";

$totalMovies = $conn->query($totalMoviesQuery)->fetch_assoc()['total'] ?? 0;
$totalSeries = $conn->query($totalSeriesQuery)->fetch_assoc()['total'] ?? 0;
$totalUsers = $conn->query($totalUsersQuery)->fetch_assoc()['total'] ?? 0;
$totalBlogs = $conn->query($totalBlogsQuery)->fetch_assoc()['total'] ?? 0;

// Get recent uploads
$recentUploadsQuery = "SELECT id, title, director, date, type FROM users ORDER BY id DESC LIMIT 5";
$recentUploads = $conn->query($recentUploadsQuery);

// Get recent users
$recentUsersQuery = "SELECT id, username, email, created_at FROM userd WHERE role='user' ORDER BY id DESC LIMIT 5";
$recentUsers = $conn->query($recentUsersQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MovieZone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="cssfiles\admindashstyle.css">
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
                <a href="dashboard.php" class="active">
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
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h2>Dashboard</h2>
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
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon movies">
                    <i class="fas fa-film"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $totalMovies; ?></h3>
                    <p>Total Movies</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon series">
                    <i class="fas fa-tv"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $totalSeries; ?></h3>
                    <p>Total Series</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $totalUsers; ?></h3>
                    <p>Registered Users</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blogs">
                    <i class="fas fa-blog"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $totalBlogs; ?></h3>
                    <p>Blog Posts</p>
                </div>
            </div>
        </div>
        
        <!-- Recent Uploads -->
        <div class="section">
            <div class="section-header">
                <h3>Recent Uploads</h3>
                <a href="movies.php">View All</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Director</th>
                            <th>Type</th>
                            <th>Upload Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentUploads && $recentUploads->num_rows > 0): ?>
                            <?php while ($upload = $recentUploads->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($upload['title']); ?></td>
                                    <td><?php echo htmlspecialchars($upload['director']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $upload['type'] === 'movie' ? 'badge-primary' : 'badge-success'; ?>">
                                            <?php echo ucfirst($upload['type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($upload['date']); ?></td>
                                    <td>
                                        <a href="edit_content.php?id=<?php echo $upload['id']; ?>" class="action-btn">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="view_content.php?id=<?php echo $upload['id']; ?>" class="action-btn">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="delete_content.php?id=<?php echo $upload['id']; ?>" class="action-btn" onclick="return confirm('Are you sure you want to delete this item?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No recent uploads found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Users -->
        <div class="section">
            <div class="section-header">
                <h3>Recent Users</h3>
                <a href="users.php">View All</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Registered Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentUsers && $recentUsers->num_rows > 0): ?>
                            <?php while ($user = $recentUsers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['created_at'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="action-btn">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="view_user.php?id=<?php echo $user['id']; ?>" class="action-btn">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="action-btn" onclick="return confirm('Are you sure you want to delete this user?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No recent users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle user menu dropdown if needed
        document.addEventListener('DOMContentLoaded', function() {
            const userInfo = document.querySelector('.user-info');
            if (userInfo) {
                userInfo.addEventListener('click', function() {
                    // Add dropdown menu toggle logic here
                });
            }
        });
    </script>
</body>
</html>