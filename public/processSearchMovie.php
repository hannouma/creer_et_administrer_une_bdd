<?php
require_once '../src/Movie.php';
require_once '../src/User.php';
$allowedFiles = array(
    'User.php',
    'Movie.php'
);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_movie'])) {

    $dsn = 'mysql:host=localhost;dbname=cinemaBDD';
    $username = 'user.php';
    $password = 'Cinem@d4t4B@$e';

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Retrieve the search query from the form
        $searchQuery = htmlspecialchars($_POST['search_query'], ENT_QUOTES, 'UTF-8');
        $cinemaID = htmlspecialchars($_POST['cinema_id'], ENT_QUOTES, 'UTF-8');

        // Perform a search query to find movies with titles similar to the search query
        $movies = Movie::searchMovies($pdo, $searchQuery);

        // Display search results
        if (!empty($movies)) {
            echo '<h3>Search Results</h3>';
            foreach ($movies as $movie) {
                echo '<p>Title: ' . htmlspecialchars($movie->getMovieTitle()) . '</p>';
                echo '<p>Description: ' . htmlspecialchars($movie->getMovieDescription()) . '</p>';
                echo '<p>Duration: ' . htmlspecialchars($movie->getMovieDuration()) . '</p>';
                echo '<p>Genre: ' . htmlspecialchars($movie->getMovieGenre()) . '</p>';
                // Add button to add movie to cinema
                echo '<form action="processMoviesSessions.php" method="post">';
                echo '<input type="hidden" name="movie_id" value="' . htmlspecialchars($movie->getMovieID()) . '">';
                echo '<input type="hidden" name="movie_title" value=" ' . htmlspecialchars($movie->getMovieTitle()) . '">';
                echo '<input type="hidden" name="movie_description" value="'. htmlspecialchars($movie->getMovieDescription()) . '">';
                echo '<input type="hidden" name="movie_duration" value="' . htmlspecialchars($movie->getMovieDuration()) . '">';
                echo '<input type="hidden" name="movie_genre" value="' . htmlspecialchars($movie->getMovieGenre()) . '">';
                // Add hidden input fields for other necessary data such as cinema ID
                echo '<input type="hidden" name="cinema_id" value="'. htmlspecialchars($cinemaID) . '">';
                echo '<input type="submit" name="add_to_cinema" value="Add to My Cinema">';
                echo '</form>';
                echo '<hr>';
            }
        } else {
            echo 'No matching movies found.';
            // Button to go back to moviesSessionManaging.php to add a new movie
            echo '<a href="moviesSessionsManaging.php">Go back to add a new movie</a>';
        }
    } catch (PDOException $e) {
        echo 'Error connecting to the database: ' . htmlspecialchars($e->getMessage());
    }
}
?>
