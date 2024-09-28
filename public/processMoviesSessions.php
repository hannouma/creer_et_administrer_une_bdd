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
require_once '../src/Session.php';
require_once '../src/Movie.php';
require_once '../src/User.php';
require_once '../src/BackupManager.php';
require_once '../vendor/autoload.php';

// Load environment variables from the .env file
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include your database connection details
    // Use environment variables for MySQL
    $dsn = $_ENV['DB_DSN'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Handle adding an existing movie to a certain cinema complex
        if (isset($_POST['add_to_cinema'])) {
            // Retrieve movie details from the form and sanitize
            $movieId = (int) $_POST['movie_id'];
            $cinemaId = htmlspecialchars($_POST['cinema_id'], ENT_QUOTES, 'UTF-8');

            // Add the existing movie to the cinema
            $relationshipAdded = Movie::addExistingMovieToCinema($pdo, $movieId, $cinemaId);

            if ($relationshipAdded) {
                echo 'Movie added to your cinema successfully!';
            } else {
                echo 'Failed to add movie to your cinema, the movie might already exist in your cinema complex please check.';
            }

        }
        // Handle Add Movie
        if (isset($_POST['add_movie'])) {
            // Retrieve and sanitize movie details from the form submission
            $cinemaID = htmlspecialchars($_POST['cinema_id'], ENT_QUOTES, 'UTF-8');
            $movieTitle = htmlspecialchars($_POST['movie_title'], ENT_QUOTES, 'UTF-8');
            $movieDescription = htmlspecialchars($_POST['movie_description'], ENT_QUOTES, 'UTF-8');
            $movieDuration = filter_var($_POST['movie_duration'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $movieGenre = htmlspecialchars($_POST['movie_genre'], ENT_QUOTES, 'UTF-8');

            // Check if any field is empty
            if (empty($movieTitle) || empty($movieDescription) || empty($movieDuration) || empty($movieGenre)) {
                echo 'Error: All fields are required.';
            } else {
                // Check if the movie already exists
                $existingMovieId = Movie::getExistingMovieId($pdo, $movieTitle, $movieDuration);
                
                if ($existingMovieId) {
                    // Movie already exists, do not add it again
                    echo 'Error: Movie already exists.';
                } else {
                    // Create a new Movie instance
                    $movie = new Movie(null, $movieTitle, $movieDescription, $movieDuration, $movieGenre);

                    // Save the movie details to the database and get the last inserted movie ID
                    $lastMovieId = $movie->addMovie($pdo, $cinemaID);
                    if ($lastMovieId !== null) {
                        // Redirect back to moviesSessionsManaging.php or wherever appropriate
                        header('Location: moviesSessionsManaging.php');
                        exit();
                    } else {
                        // Handle the case where adding the session fails
                        echo 'Error: Failed to add session.';
                    }
                }
            }
        }

        // Handle Add Session
        if (isset($_POST['add_session'])) {
            // Retrieve and sanitize session details from the form submission
            $movieId = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
            $startTime = htmlspecialchars($_POST['start_time'], ENT_QUOTES, 'UTF-8');
            $endTime = htmlspecialchars($_POST['end_time'], ENT_QUOTES, 'UTF-8');
            $userId = htmlspecialchars($_POST['user_id'], ENT_QUOTES, 'UTF-8');
            $date = htmlspecialchars($_POST['date'], ENT_QUOTES, 'UTF-8');
            // Convert the date format to 'YYYY-MM-DD'
            $date = date('Y-m-d', strtotime($date));
            $hallId = (int) $_POST['hall_id'];
            $cinemaId = htmlspecialchars($_POST['cinema_id'], ENT_QUOTES, 'UTF-8');

            // Validate input data
            if (empty($startTime) || empty($endTime) || empty($date) || empty($hallId) || empty($userId)) {
                echo 'Error: Please fill in all the required fields.';
                exit();
            }

            // Create a new session with the movie information
            $newSession = new Session(
                null, 
                $startTime, 
                $endTime, 
                $userId,
                $date,
                $cinemaId

            );

            // Perform the add operation
            $sessionInfo = $newSession->addSession($pdo, $movieId, $startTime, $endTime, $hallId, $cinemaId, $date);

            if ($sessionInfo['success']) {
                echo 'Session added successfully.';
                $sessionId = $sessionInfo['sessionId'];
                $sessionData = array(
                    'sessionId' => $sessionId,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'date' => $date,
                    'user_id' => $userId,
                    'cinema_id' => $cinemaId
                );
                BackupManager::backupSessionData($sessionData);
            } else {
                echo 'Error adding session.';
            }
        }

        // Handle Edit Session (Load data for editing)
        if (isset($_POST['edit_session'])) {
            $sessionId = (int) $_POST['session_id'];
            $userId = htmlspecialchars($_POST['user_id'], ENT_QUOTES, 'UTF-8');
            $cinemaId = htmlspecialchars($_POST['cinema_id'], ENT_QUOTES, 'UTF-8');

            
            // Fetch the session data based on session ID
            $sessionData = Session::getSessionsBySessionId($pdo, $sessionId);

            if (!empty($sessionData)) {
                // Display a form with pre-filled data for editing
                foreach ($sessionData as $session) {
                    echo '<form action="processMoviesSessions.php" method="post">';
                    echo '<input type="hidden" name="session_id" value="' . $session->getId() . '">';
                    echo 'Start Time: <input type="time" name="edit_start_time" value="' . date("H:i", strtotime($session->getStartTime())) . '"><br>';
                    echo 'End Time: <input type="time" name="edit_end_time" value="' . date("H:i", strtotime($session->getEndTime())) . '"><br>';
                    echo 'Date: <input type="text" name="edit_date" value="' . date("d-m-Y", strtotime($session->getDate())) . '"><br>';
                    echo 'New Seats Capacity: <input type="number" name="new_seats_capacity" value="' . $session->getSeats($pdo) . '"><br>';
                    $hall = $session->getHall($pdo, $session->getId());
                    $hallId = htmlspecialchars($hall->getHallID());
                    echo '<input type="hidden" name="hall_id" value="' . $hallId . '">';
                    echo '<input type="hidden" name="cinema_id" value="' . $cinemaId . '">';
                    echo '<input type="hidden" name="user_id" value="' . htmlspecialchars($userId) . '">';
                    echo '<input type="submit" name="update_session" value="Update Session">';
                    echo '</form>';
                }
            } else {
                echo 'Session not found.';
            }
        }

        // Handle Update Session
        if (isset($_POST['update_session'])) {

            $sessionId = (int) $_POST['session_id'];
            $startTime = htmlspecialchars($_POST['edit_start_time'], ENT_QUOTES, 'UTF-8');
            $endTime = htmlspecialchars($_POST['edit_end_time'], ENT_QUOTES, 'UTF-8');
            $hallId = (int) $_POST['hall_id'];
            $cinemaId = htmlspecialchars($_POST['cinema_id'], ENT_QUOTES, 'UTF-8');
            $date = htmlspecialchars($_POST['edit_date'], ENT_QUOTES, 'UTF-8');
            // Convert the date format to 'YYYY-MM-DD'
            $date = date('Y-m-d', strtotime($date));
            $newSeatsCapacity = (int) $_POST['new_seats_capacity'];
            $userId = htmlspecialchars($_POST['user_id'], ENT_QUOTES, 'UTF-8');

            // Validate input data
            if (empty($startTime) || empty($endTime) || empty($date) || empty($newSeatsCapacity)) {
                echo 'Error: Please fill in all the required fields.';
                exit();
            }

            // Create a new session with updated data
            $updatedSession = new Session($sessionId, $startTime, $endTime, null, $userId,$date, $cinemaId);

            // Perform the update operation
            $result = $updatedSession->updateSession($pdo, $sessionId, $startTime, $endTime, $hallId, $newSeatsCapacity, $date, $userId);

            if ($result) {
                echo 'Session updated successfully.';
                $sessionData = array(
                    'sessionId' => $sessionId,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'date' => $date,
                    'user_id' => $userId,
                    'cinema_id' => $cinemaId
                );
                BackupManager::backupSessionData($sessionData);
            } else {
                echo 'Error updating session.';
            }
        }
        
        // Function to delete session and related bookings
        function deleteSession($pdo, $sessionToDelete) {
            // Backup session data before deletion
            $sessionData = array(
                'session_id' => $sessionToDelete->getSessionId(), // Get the session ID
                'user_id' => $_SESSION['loggedInUser']->getUserId() // Get the ID of the user who deleted the session
            );
            BackupManager::backupSessionData($sessionData);
            
            // Perform the delete operation
            $result = $sessionToDelete->deleteSession($pdo);
            
            if ($result) {
                echo 'Session deleted successfully.';
            } else {
                echo 'Error deleting session.';
            }
        }

        // Handle Delete Session
        if (isset($_POST['delete_session'])) {
            $sessionId = (int) $_POST['session_id'];
            
            // Fetch the session data based on session ID
            $sessionData = Session::getSessionsBySessionId($pdo, $sessionId);

            if (!empty($sessionData)) {
                // Check if there are reservations for the session
                $reservationCount = $sessionData[0]->getBookingCountForSession($pdo, $sessionId);
                
                // If reservations exist, confirm deletion with the user
                if ($reservationCount > 0) {
                    echo "There are $reservationCount reservations for this session, you will delete all the bookings related to this session if you proceed. Are you sure you want to delete it?";
                    echo '<form action="processMoviesSessions.php" method="post">';
                    echo '<input type="hidden" name="session_id" value="' . $sessionId . '">';
                    echo '<input type="submit" name="confirm_delete_session" value="Confirm Delete">';
                    echo '<input type="submit" name="cancel_delete_session" value="Cancel">';
                    echo '</form>';
                } else {
                    // If no reservations, proceed with deletion
                    deleteSession($pdo, $sessionData[0]);
                }
            } else {
                echo 'Session not found.';
            }
        }
    

    
        // Handle Delete Session Confirmation
        if (isset($_POST['confirm_delete_session'])) {
            $sessionId = (int) $_POST['session_id'];

            // Fetch the session data based on session ID
            $sessionData = Session::getSessionsBySessionId($pdo, $sessionId);

            if (!empty($sessionData)) {
                // Perform the delete operation
                $result = $sessionData[0]->deleteSession($pdo);

                if ($result) {
                    echo 'Session deleted successfully.';
                } else {
                    echo 'Error deleting session.';
                }
            } else {
                echo 'Session not found.';
            }
        } elseif (isset($_POST['cancel_delete_session'])) {
            // Redirect back to moviesSessionsManaging.php if session deletion is cancelled
            header('Location: moviesSessionsManaging.php');
            exit();
        }
    
    } catch (PDOException $e) {
        echo 'Error connecting to the database: ' . htmlspecialchars($e->getMessage());
    }
    
}
?>
</body>
</html>