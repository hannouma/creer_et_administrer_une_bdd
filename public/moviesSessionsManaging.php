<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Movies Sessions</title>
    <!-- Ajouter la balise meta pour la politique de sécurité du contenu -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; img-src https://*; child-src 'none';">
</head>
<body>

<?php
require_once '../src/User.php';
require_once '../src/Movie.php';
require_once '../src/Session.php';
require_once '../src/BackupManager.php';
require_once './click_tracker.php';

// Start the session
session_start([
    'cookie_lifetime' => 86400, // 24 hours session lifetime
    'cookie_secure'   => true,  // Requires HTTPS
    'cookie_httponly' => true,  // Prevents client-side scripts from accessing cookies
    'use_strict_mode' => true   // Regenerates session ID on every request
]);

// Check if the user is logged in
if (isset($_SESSION['loggedInUser'])) {
    $loggedInUser = $_SESSION['loggedInUser'];
    $roles = $loggedInUser->getRoles();

    //var_dump($loggedInUser);

    // Check if the user has ROLE_ADMIN or COMPLEX_USER role
    if (in_array('ROLE_ADMIN', $roles)) {
        // Include my database connection details
        $dsn = 'mysql:host=localhost;dbname=cinemabdd';
        $username = 'user.php';
        $password = 'Cinem@d4t4B@$e';

        try {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Fetch movies and accumulate click counts
            $movies = Movie::getAllMovies($pdo); // This method fetches all movies
            $clickCounts = []; // Array to store unique click counts
            
            foreach ($movies as $movie) {
                $clickCount = getClickCount($movie->getMovieID());

                // Accumulate the click counts for each movie
                if (isset($totalClickCounts[$movie->getMovieID()])) {
                    $totalClickCounts[$movie->getMovieID()]['title'] = $movie->getMovieTitle();
                    $totalClickCounts[$movie->getMovieID()]['count'] += $clickCount;
                } else {
                    $totalClickCounts[$movie->getMovieID()] = [
                        'title' => $movie->getMovieTitle(),
                        'count' => $clickCount
                    ];
                }
            }

            // Display the total click counts in a table
            echo '<h2><mark>Total Click Counts for Movies</mark></h2>';
            echo '<table border="1">';
            echo '<tr><th>Movie Title</th><th>Total Click Count</th></tr>';

            foreach ($totalClickCounts as $movieId => $data) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($data['title']) . '</td>';
                echo '<td>' . htmlspecialchars($data['count']) . '</td>';
                echo '</tr>';
            }

            echo '</table>';

            // Fetch cinemas from the database
            $cinemas = Cinema::getCinemas($pdo);

            // Loop through each cinema
            foreach ($cinemas as $cinema) {
                // Fetch cinema ID
                $cinemaId = $cinema->getCinemaID();

                echo '<div>';
                echo '<h2>' . '<mark>' . htmlspecialchars($cinema->getName()) . '</mark>' . '</h2>';
                echo '<p>' . 'Location: ' . htmlspecialchars($cinema->getLocation()) . '</p>';

                // Fetch movies for the current cinema
                $movies = Movie::getMoviesByCinema($pdo, $cinemaId);

                // Loop through each movie
                foreach ($movies as $movie) {

                    echo '<div>';
                    echo '<h3><ins>' . htmlspecialchars($movie->getMovieTitle()) . '</ins></h3>';
                    echo '<p><strong>Description:</strong> ' . htmlspecialchars($movie->getMovieDescription()) . '</p>';
                    echo '<p><strong>Duration:</strong> ' . htmlspecialchars($movie->getMovieDuration()) . ' min</p>';
                    echo '<p><strong>Genre:</strong> ' . htmlspecialchars($movie->getMovieGenre()) . '</p>';

                    // Retrieve sessions for the current movie and cinema
                    $sessions = Session::getSessionsByMovie($pdo, $cinemaId, $movie->getMovieID());

                    // Check if there are sessions for the movie
                    if (!empty($sessions)) {
                        echo '<table border="1">';
                        echo '<tr><th>Hall</th><th>Start Time</th><th>End Time</th><th>Date</th><th>Seats</th><th>Available Seats</th><th>Actions</th></tr>';

                        foreach ($sessions as $session) {
                            echo '<tr>';
                            $hall = $session->getHall($pdo, $session->getId());
                            if ($hall) {
                                echo '<td>' . htmlspecialchars($hall->getHallName()) . '</td>';
                                echo '<td>' . htmlspecialchars(date("H:i", strtotime($session->getStartTime()))) . '</td>';
                                echo '<td>' . htmlspecialchars(date("H:i", strtotime($session->getEndTime()))) . '</td>';
                                echo '<td>' . htmlspecialchars(date("d-m-Y", strtotime($session->getDate()))) . '</td>';
                                echo '<td>' . htmlspecialchars($session->getSeats($pdo)) . '</td>';
                                // Fetch available seats using getAvailableSeats method
                                $availableSeats = $session->getAvailableSeats($pdo);
                                echo '<td>' . htmlspecialchars($availableSeats) . '</td>';
                                echo '<td>';
                                echo '<form action="processMoviesSessions.php" method="post">';
                                echo '<input type="hidden" name="session_id" value="' . htmlspecialchars($session->getId()) . '">';
                                echo '<input type="hidden" name="user_id" value="' . htmlspecialchars($loggedInUser->getId()) . '">';
                                echo '<input type="hidden" name="cinema_id" value="' . htmlspecialchars($cinemaId) . '">';
                                echo '<input type="submit" name="edit_session" value="Edit">';
                                echo '<input type="submit" name="delete_session" value="Delete">';
                                echo '</form>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        }

                        echo '</table>';
                        
                    } else {
                        echo '<p>No sessions available for this movie.</p>';
                    }

                    // Add button for adding a new session
                    echo '<form action="processMoviesSessions.php" method="POST">';
                    echo '<input type="hidden" name="movie_id" value="' . htmlspecialchars($movie->getMovieID()) . '">';
                    echo 'Start Time: <input type="time" name="start_time" placeholder="Start Time">';
                    echo 'End Time: <input type="time" name="end_time" placeholder="End Time">';
                    echo '<input type="text" name="date" placeholder="Date (dd-mm-yyyy)">';
                    // Add a hidden input for the cinema_id associated with the selected hall
                    echo '<input type="hidden" name="cinema_id" value="' . htmlspecialchars($cinemaId) . '">';
                    echo '<input type="hidden" name="user_id" value="' . htmlspecialchars($loggedInUser->getId()) . '">';
                    echo '<select name="hall_id" required>'; // Added 'required' for validation

                    // Fetch halls for the current cinema
                    $halls = Hall::getHallsByCinema($pdo, $cinemaId);
                    foreach ($halls as $hall) {
                        echo '<option value="' . htmlspecialchars($hall->getHallID()) . '">' . htmlspecialchars($hall->getHallName()) . '</option>';
                    }

                    echo '</select>';
                    echo '<input type="submit" name="add_session" value="Add Session">';
                    echo '</form>';
                    echo '</div>';
                }

                echo'<h3>Search for a Movie</h3>';
                echo'<form action="processSearchMovie.php" method="post">';
                echo'    <input type="text" name="search_query" placeholder="Enter movie title">';
                echo'    <input type="hidden" name="cinema_id" value="' . htmlspecialchars($cinema->getCinemaID()) . '">';
                echo'    <input type="submit" name="search_movie" value="Search">';
                echo'</form>';


                // Add the necessary HTML form elements for adding movies
                echo '<h3>Add New Movie</h3>';
                echo '<form action="processMoviesSessions.php" method="post">';
                echo '<input type="hidden" name="cinema_id" value="' . htmlspecialchars($cinema->getCinemaID()) . '">';
                echo 'Title: <input type="text" name="movie_title"><br>';
                echo 'Description: <input type="text" name="movie_description"><br>';
                echo 'Duration: <input type="text" name="movie_duration"><br>';
                echo 'Genre: <input type="text" name="movie_genre"><br>';
                echo '<input type="hidden" name="user_id" value="' . htmlspecialchars($loggedInUser->getId()) . '">';
                echo '<input type="hidden" name="date" value="' . date('Y-m-d') .'">';
                echo '<input type="submit" name="add_movie" value="Add Movie">';
                echo '</form>';
                echo '</div>';
                echo '<hr>';
            }

            echo '<form action="moviesSessionsManaging.php" method="post">';
            echo '<input type="submit" name="backup" value="Backup Database">';
            echo '<input type="submit" name="restore" value="Restore Database">';
            echo '</form>';
            
            // Handle form submission
            if (isset($_POST['backup'])) {
                BackupManager::backupDatabase(); // Trigger the backup process
            } elseif (isset($_POST['restore'])) {
                // Trigger the restore process
                BackupManager::restoreLatestBackup();
            }

        } catch (PDOException $e) {
            echo 'Error connecting to the database: ' . $e->getMessage();
        }
    }  elseif (in_array('COMPLEX_USER', $roles)) {
        // Fetch cinema ID associated with the complex user
        $complexUserId = $loggedInUser->getId();
        $dsn = 'mysql:host=localhost;dbname=cinemaBDD';
        $username = 'user.php';
        $password = 'Cinem@d4t4B@$e';

        try {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Fetch cinema ID for complex user from the database
            $stmt = $pdo->prepare("SELECT cinema_id FROM users WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $complexUserId, PDO::PARAM_STR);
            $stmt->execute();
            $cinemaId = $stmt->fetchColumn();
            
            // Fetch cinema-specific data based on cinema ID
            $stmt = $pdo->prepare("SELECT * FROM cinemas WHERE cinema_id = :cinema_id");
            $stmt->bindParam(':cinema_id', $cinemaId, PDO::PARAM_STR);
            $stmt->execute();
            $cinema = $stmt->fetch(PDO::FETCH_ASSOC);

            // Display cinema-specific data
            if ($cinema) {
                echo '<h1>Welcome to ' . htmlspecialchars($cinema['name']) . ' sessions and movies managing page</h1>';

                // Fetch movies for the current cinema
                $movies = Movie::getMoviesByCinema($pdo, $cinemaId);

                // Loop through each movie
                foreach ($movies as $movie) {
                    
                    echo '<div>';
                    echo '<h3 style="color: blue;">' . htmlspecialchars($movie->getMovieTitle()) . '</h3>';
                    echo '<p><strong>Description:</strong> ' . htmlspecialchars($movie->getMovieDescription()) . '</p>';
                    echo '<p><strong>Duration:</strong> ' . htmlspecialchars($movie->getMovieDuration()) . ' min</p>';
                    echo '<p><strong>Genre:</strong> ' . htmlspecialchars($movie->getMovieGenre()) . '</p>';

                    // Retrieve sessions for the current movie and cinema
                    $sessions = Session::getSessionsByMovie($pdo, $cinemaId, $movie->getMovieID());

                    // Check if there are sessions for the movie
                    if (!empty($sessions)) {
                        echo '<table border="1">';
                        echo '<tr><th>Hall</th><th>Start Time</th><th>End Time</th><th>Date</th><th>Seats</th><th>Available Seats</th><th>Actions</th></tr>';

                        foreach ($sessions as $session) {
                            echo '<tr>';
                            $hall = $session->getHall($pdo, $session->getId());
                            if ($hall) {
                                echo '<td>' . htmlspecialchars($hall->getHallName()) . '</td>';
                                echo '<td>' . htmlspecialchars(date("H:i", strtotime($session->getStartTime()))) . '</td>';
                                echo '<td>' . htmlspecialchars(date("H:i", strtotime($session->getEndTime()))) . '</td>';
                                echo '<td>' . htmlspecialchars(date("d-m-Y", strtotime($session->getDate()))) . '</td>';
                                echo '<td>' . htmlspecialchars($session->getSeats($pdo)) . '</td>';
                                // Fetch available seats using getAvailableSeats method
                                $availableSeats = $session->getAvailableSeats($pdo);
                                echo '<td>' . htmlspecialchars($availableSeats) . '</td>';
                                echo '<td>';
                                echo '<form action="processMoviesSessions.php" method="post">';
                                echo '<input type="hidden" name="session_id" value="' . htmlspecialchars($session->getId()) . '">';
                                echo '<input type="hidden" name="user_id" value="' . htmlspecialchars($loggedInUser->getId()) . '">';
                                echo '<input type="hidden" name="cinema_id" value="' . htmlspecialchars($cinemaId) . '">';
                                echo '<input type="submit" name="edit_session" value="Edit">';
                                echo '<input type="submit" name="delete_session" value="Delete">';
                                echo '</form>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        }

                        echo '</table>';
                    } else {
                        echo '<p>No sessions available for this movie.</p>';
                    }

                    // Add button for adding a new session
                    echo '<form action="processMoviesSessions.php" method="post">';
                    echo '<input type="hidden" name="movie_id" value="' . htmlspecialchars($movie->getMovieID()) . '">';
                    echo 'Start Time: <input type="time" name="start_time" placeholder="Start Time">';
                    echo 'End Time: <input type="time" name="end_time" placeholder="End Time">';
                    echo '<input type="text" name="date" placeholder="Date (dd-mm-yyyy)">';
                    // Add a hidden input for the cinema_id associated with the selected hall
                    echo '<input type="hidden" name="cinema_id" value="' . htmlspecialchars($cinemaId) . '">';
                    echo '<input type="hidden" name="user_id" value="' . htmlspecialchars($loggedInUser->getId()) . '">';
                    echo '<select name="hall_id" required>'; // Added 'required' for validation

                    // Fetch halls for the current cinema
                    $halls = Hall::getHallsByCinema($pdo, $cinemaId);
                    foreach ($halls as $hall) {
                        echo '<option value="' . htmlspecialchars($hall->getHallID()) . '">' . htmlspecialchars($hall->getHallName()) . '</option>';
                    }

                    echo '</select>';
                    echo '<input type="submit" name="add_session" value="Add Session">';
                    echo '</form>';

                    echo '</div>';
                }

                echo'<h3>Search for a Movie</h3>';
                echo'<form action="processSearchMovie.php" method="post">';
                echo'    <input type="text" name="search_query" placeholder="Enter movie title">';
                echo '   <input type="hidden" name="cinema_id" value="' . htmlspecialchars($cinemaId) . '">';
                echo'    <input type="submit" name="search_movie" value="Search">';
                echo'</form>';

                // Add the necessary HTML form elements for adding movies
                echo '<h3>Add New Movie</h3>';
                echo '<form action="processMoviesSessions.php" method="post">';
                echo '<input type="hidden" name="cinema_id" value="' . htmlspecialchars($cinemaId) . '">';
                echo 'Title: <input type="text" name="movie_title"><br>';
                echo 'Description: <input type="text" name="movie_description"><br>';
                echo 'Duration: <input type="text" name="movie_duration"><br>';
                echo 'Genre: <input type="text" name="movie_genre"><br>';
                echo '<input type="hidden" name="user_id" value="' . htmlspecialchars($loggedInUser->getId()) . '">';
                echo '<input type="hidden" name="date" value="' . date('Y-m-d') . '">';
                echo '<input type="submit" name="add_movie" value="Add Movie">';
                echo '</form>';
                
            } else {
                echo 'Cinema not found.';
            }
        } catch (PDOException $e) {
            echo 'Error: ' . htmlspecialchars($e->getMessage());
        }
    }
} else {
    // Redirect to login.php if not logged in
    header('Location: login.php');
    exit();
}

?>

</body>
</html>
