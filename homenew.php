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

// Get movies for slider (recent 5 movies)
$sliderQuery = "SELECT title, director, image, content FROM users ORDER BY id DESC LIMIT 5";
$sliderResult = $conn->query($sliderQuery);
$sliderMovies = [];
if ($sliderResult->num_rows > 0) {
    while($row = $sliderResult->fetch_assoc()) {
        $sliderMovies[] = $row;
    }
}

// Get all movies for thumbnail display
$moviesQuery = "SELECT title, director, image FROM users ORDER BY title ASC";
$moviesResult = $conn->query($moviesQuery);
$allMovies = [];
if ($moviesResult->num_rows > 0) {
    while($row = $moviesResult->fetch_assoc()) {
        $allMovies[] = $row;
    }
}

// Search functionality
$searchResults = [];
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $searchQuery = "SELECT title, director, image FROM users WHERE title LIKE '%$search%' ORDER BY title ASC";
    $searchResult = $conn->query($searchQuery);
    if ($searchResult->num_rows > 0) {
        while($row = $searchResult->fetch_assoc()) {
            $searchResults[] = $row;
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
    <title>MovieZone - Your Ultimate Movie Platform</title>
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
        
        .search-results {
            padding: 0 15px;
        }
        
        .slider-container {
            width: 100%;
            position: relative;
            margin-bottom: 40px;
            overflow: hidden;
        }
        
        .slider {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }
        
        .slide {
            min-width: 100%;
            height: 400px;
            background-color: var(--bg-lighter);
            display: flex;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        
        .slide-img {
            flex: 1;
            overflow: hidden;
        }
        
        .slide-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .slide-img:hover img {
            transform: scale(1.05);
        }
        
        .slide-content {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .slide-content h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: var(--text-light);
        }
        
        .slide-content p.director {
            font-size: 1rem;
            color: var(--accent-color);
            margin-bottom: 20px;
        }
        
        .slide-content p.description {
            font-size: 1rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .slider-nav {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .slider-nav-item {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.3);
            margin: 0 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .slider-nav-item.active {
            background-color: var(--accent-color);
            transform: scale(1.2);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
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
        
        @media (max-width: 768px) {
            .slide {
                flex-direction: column;
                height: auto;
            }
            
            .slide-img, .slide-content {
                flex: none;
            }
            
            .slide-img {
                height: 250px;
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
            <h1>MovieZone</h1>
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
        
        <!-- Search Results -->
        <div class="search-results">
            <?php if(!empty($searchResults)): ?>
                <h3 style="margin: 15px 0; color: var(--accent-color);">Search Results:</h3>
                <?php foreach($searchResults as $movie): ?>
                    <a href="guestview.php?title=<?php echo urlencode($movie['title']); ?>&director=<?php echo urlencode($movie['director']); ?>">
                        <?php echo htmlspecialchars($movie['title']); ?> (<?php echo htmlspecialchars($movie['director']); ?>)
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <a href="index.php">Home</a>
        <a href="#">Movies</a>
        <a href="#">TV Series</a>
        <a href="#">Blog</a>
        <a href="#">About</a>
        <a href="#">Contact</a>
        <a href="login.php">Login</a>
    </div>
    
    <!-- Featured Slider -->
    <div class="slider-container">
        <div class="slider" id="slider">
            <?php if(!empty($sliderMovies)): ?>
                <?php foreach($sliderMovies as $index => $movie): ?>
                    <div class="slide">
                        <div class="slide-img">
                            <a href="guestview.php?title=<?php echo urlencode($movie['title']); ?>&director=<?php echo urlencode($movie['director']); ?>">
                                <img src="<?php 
                                 $target_dir = "uploads2/";
                                 $target_file=  $target_dir . $movie['image'];
                                 echo htmlspecialchars( $target_file); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            </a>
                        </div>
                        <div class="slide-content">
                            <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
                            <p class="director">Director: <?php echo htmlspecialchars($movie['director']); ?></p>
                            <p class="description"><?php echo htmlspecialchars($movie['content']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="slide">
                    <div class="slide-content" style="flex: 2;">
                        <h2>Welcome to MovieZone</h2>
                        <p class="description">Your ultimate platform for movies, TV series, and blogs.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Slider Navigation -->
        <div class="slider-nav" id="slider-nav">
            <?php if(!empty($sliderMovies)): ?>
                <?php for($i = 0; $i < count($sliderMovies); $i++): ?>
                    <div class="slider-nav-item <?php echo ($i === 0) ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></div>
                <?php endfor; ?>
            <?php else: ?>
                <div class="slider-nav-item active"></div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <h2 class="section-title">Featured Movies</h2>
        
        <!-- Movies Grid    CARDS  -->
        <div class="movies-grid">
            <?php if(!empty($allMovies)): ?>
                <?php foreach($allMovies as $movie): ?>
                    <div class="movie-card">
                        <a href="guestview.php?title=<?php echo urlencode($movie['title']); ?>&director=<?php echo urlencode($movie['director']); ?>">
                            <div class="movie-thumbnail">
                                <?php 
                                 $target_dir = "uploads2/";
                                 $target_file=  $target_dir . $movie['image']; ?>
                                <img src="<?php echo htmlspecialchars($target_file); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            </div>
                            <div class="movie-info">
                                <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                <p class="movie-director"><?php echo htmlspecialchars($movie['director']); ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No movies available at the moment.</p>
            <?php endif; ?>
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
        
        // Slider Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.getElementById('slider');
            const slides = document.querySelectorAll('.slide');
            const navItems = document.querySelectorAll('.slider-nav-item');
            
            if (slides.length <= 1) return;
            
            let currentSlide = 0;
            
            // Initialize slider
            updateSlider();
            
            // Auto slide every 5 seconds
            setInterval(() => {
                currentSlide = (currentSlide + 1) % slides.length;
                updateSlider();
            }, 5000);
            
            // Click on navigation dots
            navItems.forEach((item, index) => {
                item.addEventListener('click', () => {
                    currentSlide = index;
                    updateSlider();
                });
            });
            
            function updateSlider() {
                slider.style.transform = `translateX(-${currentSlide * 100}%)`;
                
                navItems.forEach((item, index) => {
                    if (index === currentSlide) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });
            }
        });
        
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