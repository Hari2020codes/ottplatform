<?php
// Start session for login management
session_start();

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

$error = '';
$success = '';

// Registration Process
if (isset($_POST['register'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if username or email already exists
        $checkQuery = "SELECT * FROM userd WHERE username = '$username' OR email = '$email'";
        $checkResult = $conn->query($checkQuery);
        
        if ($checkResult->num_rows > 0) {
            $error = "Username or email already exists";
        } else {
            // Hash password
           // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $insertQuery = "INSERT INTO userd (username, email, password, role) VALUES ('$username', '$email', '$password', 'user')";
            
            if ($conn->query($insertQuery) === TRUE) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}

// Login Process
if (isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Username and password are required";
    } else {
        // Check user credentials
        $loginQuery = "SELECT * FROM userd WHERE username = '$username' OR email = '$username'";
        $result = $conn->query($loginQuery);
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($password==$user['password']) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: user/profile.php");
                }
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Username not found";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register - MovieZone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="cssfiles\logstyle.css">
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #0d47a1;
            --text-light: #ffffff;
            --text-dark: #333333;
            --bg-dark: #121212;
            --bg-lighter: #1e1e1e;
            --accent-color: #ff4081;
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
    <!-- Header with Logo and Menu Button -->
    <header class="header">
        <div class="logo">
            <a href="index.php">
                <h1>MovieZone</h1>
            </a>
        </div>
        <button class="menu-btn" onclick="openNav()">
            <i class="fas fa-bars"></i>
        </button>
    </header>
    
    <!-- Side Navigation Menu -->
    <div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <a href="homenew.php">Home</a>
        <a href="#">Movies</a>
        <a href="#">TV Series</a>
        <a href="#">Blog</a>
        <a href="#">About</a>
        <a href="#">Contact</a>
        <a href="login.php" class="active">Login</a>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <div class="auth-container">
            <div class="auth-image">
                <h2>Welcome to MovieZone</h2>
                <p>Your ultimate destination for movies, TV series, and blogs. Join us to get access to exclusive content and features.</p>
            </div>
            <div class="auth-form">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="tabs">
                    <button class="tab-item active" data-tab="login">Login</button>
                    <button class="tab-item" data-tab="register">Register</button>
                </div>
                
                <!-- Login Form -->
                <div class="tab-content active" id="login-tab">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="login-username">Username or Email</label>
                            <input type="text" id="login-username" name="username" class="form-control" placeholder="Enter your username or email">
                        </div>
                        <div class="form-group">
                            <label for="login-password">Password</label>
                            <input type="password" id="login-password" name="password" class="form-control" placeholder="Enter your password">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="remember-me" name="remember">
                            <label for="remember-me">Remember me</label>
                        </div>
                        <div class="forgot-password">
                            <a href="#">Forgot password?</a>
                        </div>
                        <button type="submit" name="login" class="btn">Login</button>
                    </form>
                    
                    <div class="divider">
                        <span>OR</span>
                    </div>
                    
                    <div class="social-login">
                        <a href="#" class="social-btn btn-facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-btn btn-google">
                            <i class="fab fa-google"></i>
                        </a>
                        <a href="#" class="social-btn btn-twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                    
                    <p class="footer-text">Don't have an account? <a href="#" class="switch-tab" data-tab="register">Register now</a></p>
                </div>
                
                <!-- Register Form -->
                <div class="tab-content" id="register-tab">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="register-username">Username</label>
                            <input type="text" id="register-username" name="username" class="form-control" placeholder="Choose a username">
                        </div>
                        <div class="form-group">
                            <label for="register-email">Email</label>
                            <input type="email" id="register-email" name="email" class="form-control" placeholder="Enter your email">
                        </div>
                        <div class="form-group">
                            <label for="register-password">Password</label>
                            <input type="password" id="register-password" name="password" class="form-control" placeholder="Create a password">
                        </div>
                        <div class="form-group">
                            <label for="confirm-password">Confirm Password</label>
                            <input type="password" id="confirm-password" name="confirm_password" class="form-control" placeholder="Confirm your password">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                        </div>
                        <button type="submit" name="register" class="btn">Register</button>
                    </form>
                    
                    <p class="footer-text">Already have an account? <a href="#" class="switch-tab" data-tab="login">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Side Navigation
        function openNav() {
            document.getElementById("mySidenav").style.width = "300px";
        }
        
        function closeNav() {
            document.getElementById("mySidenav").style.width = "0";
        }
        
        // Tab Switching
        document.addEventListener('DOMContentLoaded', function() {
            const tabItems = document.querySelectorAll('.tab-item');
            const tabContents = document.querySelectorAll('.tab-content');
            const switchTabs = document.querySelectorAll('.switch-tab');
            
            function switchTab(tabId) {
                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.remove('active');
                });
                
                // Remove active class from all tab items
                tabItems.forEach(item => {
                    item.classList.remove('active');
                });
                
                // Show selected tab content and set tab as active
                document.getElementById(`${tabId}-tab`).classList.add('active');
                document.querySelector(`.tab-item[data-tab="${tabId}"]`).classList.add('active');
            }
            
            // Tab button click event
            tabItems.forEach(item => {
                item.addEventListener('click', () => {
                    const tabId = item.getAttribute('data-tab');
                    switchTab(tabId);
                });
            });
            
            // Switch tab link click event
            switchTabs.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const tabId = link.getAttribute('data-tab');
                    switchTab(tabId);
                });
            });
        });
    </script>
</body>
</html>