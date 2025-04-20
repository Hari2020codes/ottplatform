<?php
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

// Get title and director from URL parameters
$title = isset($_GET['title']) ? $conn->real_escape_string($_GET['title']) : '';
$director = isset($_GET['director']) ? $conn->real_escape_string($_GET['director']) : '';

// Get video and other details
$videoData = null;
if ($title && $director) {
    $query = "SELECT title, director, video, image, content, genre, date FROM users WHERE title = '$title' AND director = '$director'";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $videoData = $result->fetch_assoc();
    }
}

// Get related movies (same genre or director)
$relatedMovies = [];
if ($videoData) {
    $genre = $conn->real_escape_string($videoData['genre']);
    $excludeTitle = $conn->real_escape_string($videoData['title']);
    
    $relatedQuery = "SELECT title, director, image FROM users WHERE 
                     (genre = '$genre' OR director = '{$videoData['director']}') 
                     AND title != '$excludeTitle' 
                     ORDER BY RAND() LIMIT 6";
    
    $relatedResult = $conn->query($relatedQuery);
    
    if ($relatedResult && $relatedResult->num_rows > 0) {
        while ($row = $relatedResult->fetch_assoc()) {
            $relatedMovies[] = $row;
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
    <title><?php echo $videoData ? htmlspecialchars($videoData['title']) : 'Video Not Found'; ?> - MovieZone</title>
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
            overflow-x: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 100;
        }
        
        .logo h1 {
            color: var(--text-light);
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .logo a {
            text-decoration: none;
            color: inherit;
        }
        
        .menu-btn {
            background-color: transparent;
            border: none;
            color: var(--text-light);
            font-size: 1.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .menu-btn:hover {
            transform: scale(1.1);
        }
        
        .sidenav {
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 999;
            top: 0;
            right: 0;
            background-color: var(--bg-lighter);
            overflow-x: hidden;
            transition: 0.5s;
            padding-top: 60px;
            box-shadow: -4px 0 10px rgba(0, 0, 0, 0.3);
        }
        
        .sidenav a {
            padding: 15px 25px;
            text-decoration: none;
            font-size: 1.2rem;
            color: var(--text-light);
            display: block;
            transition: 0.3s;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidenav a:hover {
            color: var(--accent-color);
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .sidenav .closebtn {
            position: absolute;
            top: 0;
            right: 25px;
            font-size: 2rem;
            margin-left: 50px;
            border: none;
        }
        
        .sidenav .search-container {
            padding: 15px;
            position: relative;
        }
        
        .sidenav .search-container input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            outline: none;
            transition: all 0.3s ease;
        }
        
        .sidenav .search-container input:focus {
            background-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.3);
        }
        
        .sidenav .search-container button {
            background: transparent;
            border: none;
            position: absolute;
            right: 25px;
            top: 25px;
            color: var(--text-light);
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .video-container {
            background-color: var(--bg-lighter);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }
        
        .video-player {
            width: 100%;
            background-color: #000;
            aspect-ratio: 16 / 9;
        }
        
        .video-player video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .video-info {
            padding: 25px;
        }
        
        .video-title {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .video-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .video-meta span {
            display: flex;
            align-items: center;
        }
        
        .video-meta i {
            margin-right: 5px;
            color: var(--accent-color);
        }
        
        .video-description {
            line-height: 1.6;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .section-title {
            font-size: 1.8rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-color);
            display: inline-block;
        }
        
        .movies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .movie-card {
            background-color: var(--bg-lighter);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .movie-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        
        .movie-thumbnail {
            position: relative;
            overflow: hidden;
            height: 280px;
        }
        
        .movie-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .movie-card:hover .movie-thumbnail img {
            transform: scale(1.1);
        }
        
        .movie-info {
            padding: 15px;
        }
        
        .movie-title {
            font-size: 1.1rem;
            margin-bottom: 5px;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .movie-director {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .not-found {
            text-align: center;
            padding: 50px 20px;
        }
        
        .not-found h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: var(--accent-color);
        }
        
        .not-found p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .not-found .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--primary-color);
            color: var(--text-light);
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .not-found .btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .video-title {
                font-size: 1.8rem;
            }
            
            .movies-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .movie-thumbnail {
                height: 220px;
            }
        }
        
        @media (max-width: 480px) {
            .logo h1 {
                font-size: 2rem;
            }
            
            .video-title {
                font-size: 1.5rem;
            }
            
            .movies-grid {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            }
            
            .movie-thumbnail {
                height: 180px;
            }
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
        <div class="search-container">
            <form action="index.php" method="GET">
                <input type="text" placeholder="Search movies..." name="search" id="search-input" autocomplete="off">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        
        <div class="search-results"></div>
        
        <a href="index.php">Home</a>
        <a href="#">Movies</a>
        <a href="#">TV Series</a>
        <a href="#">Blog</a>
        <a href="#">About</a>
        <a href="#">Contact</a>
        <a href="login.php">Login</a>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <?php if ($videoData): ?>
            <!-- Video Player and Information -->
            <div class="video-container">
                <div class="video-player">
                    <video controls autoplay><?php 
                                 $target_dir = "uploads/";
                                 $target_file=  $target_dir . $videoData['video'];?>
                        <source src="<?php echo htmlspecialchars($target_file); ?>" type="video/mp4">


                        Your browser does not support the video tag.
                    </video>
                </div>
                <div class="video-info">
                    <h1 class="video-title"><?php echo htmlspecialchars($videoData['title']); ?></h1>
                    <div class="video-meta">
                        <span><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($videoData['director']); ?></span>
                        <span><i class="fas fa-film"></i> <?php echo htmlspecialchars($videoData['genre']); ?></span>
                        <span><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($videoData['date']); ?></span>
                    </div>
                    <p class="video-description"><?php echo htmlspecialchars($videoData['content']); ?></p>
                </div>
            </div>
            
            <!-- Related Videos -->
            <?php if (!empty($relatedMovies)): ?>
                <h2 class="section-title">You May Also Like</h2>
                <div class="movies-grid">
                    <?php foreach ($relatedMovies as $movie): ?>
                        <div class="movie-card">
                            <a href="guestview.php?title=<?php echo urlencode($movie['title']); ?>&director=<?php echo urlencode($movie['director']); ?>">
                                <div class="movie-thumbnail">
                                    <img src="<?php
                                 $target_dir = "uploads2/";
                                 $target_file=  $target_dir . $movie['image'];
                                    echo htmlspecialchars($target_file); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                                </div>
                                <div class="movie-info">
                                    <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                    <p class="movie-director"><?php echo htmlspecialchars($movie['director']); ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Content Not Found -->
            <div class="not-found">
                <h2>Video Not Found</h2>
                <p>Sorry, the video you're looking for is not available or may have been removed.</p>
                <a href="index.php" class="btn">Back to Home</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Side Navigation
        function openNav() {
            document.getElementById("mySidenav").style.width = "300px";
        }
        
        function closeNav() {
            document.getElementById("mySidenav").style.width = "0";
        }
        
        // Live Search Suggestions
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                if (this.value.length >= 2) {
                    fetchSearchSuggestions(this.value);
                }
            });
        }
        
        function fetchSearchSuggestions(query) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `search_suggestions.php?query=${encodeURIComponent(query)}`, true);
            xhr.onload = function() {
                if (this.status === 200) {
                    const results = JSON.parse(this.responseText);
                    displaySearchSuggestions(results);
                }
            };
            xhr.send();
        }
        
        function displaySearchSuggestions(results) {
            const searchResults = document.querySelector('.search-results');
            searchResults.innerHTML = '';
            
            if (results.length > 0) {
                const heading = document.createElement('h3');
                heading.style.margin = '15px 0';
                heading.style.color = 'var(--accent-color)';
                heading.textContent = 'Suggestions:';
                searchResults.appendChild(heading);
                
                results.forEach(result => {
                    const link = document.createElement('a');
                    link.href = `guestview.php?title=${encodeURIComponent(result.title)}&director=${encodeURIComponent(result.director)}`;
                    link.textContent = `${result.title} (${result.director})`;
                    searchResults.appendChild(link);
                });
            }
        }
    </script>
</body>
</html>