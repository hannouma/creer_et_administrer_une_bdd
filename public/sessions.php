<!-- sessions.php -->
<?php
require_once '../src/User.php';
require_once './click_tracker.php';
require_once '../vendor/autoload.php';

// Load environment variables from the .env file
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

session_start([
    'cookie_lifetime' => 86400, // 24 hours session lifetime
    'cookie_secure'   => true,  // Requires HTTPS
    'cookie_httponly' => true,  // Prevents client-side scripts from accessing cookies
    'use_strict_mode' => true   // Regenerates session ID on every request
]);

// Use environment variables for MySQL
$dsn = $_ENV['DB_DSN'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['cinema_id']) && isset($_GET['movie_id'])) {
        // Sanitize input parameters to prevent SQL injection
        $cinemaId = htmlspecialchars($_GET['cinema_id'], ENT_QUOTES, 'UTF-8');
        $movieId = htmlspecialchars($_GET['movie_id'], ENT_QUOTES, 'UTF-8');

        // Track clicks if the 'track_click' parameter is present
        if (isset($_GET['track_click']) && $_GET['track_click'] === 'true') {
            incrementClicks($movieId); // Increment the click count for the movie
        }

        $sessionsQuery =  "SELECT s.*, h.hall_name
        FROM sessions s
        JOIN session_movies sm ON s.session_id = sm.session_id
        JOIN session_halls sh ON s.session_id = sh.session_id
        JOIN halls h ON sh.hall_id = h.hall_id
        WHERE s.cinema_id = :cinema_id
        AND sm.movie_id = :movie_id";
        $sessionsStmt = $pdo->prepare($sessionsQuery);
        $sessionsStmt->bindParam(':cinema_id', $cinemaId, PDO::PARAM_STR);
        $sessionsStmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
        $sessionsStmt->execute();
        $sessions = $sessionsStmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // Redirect to the index page if cinema_id or movie_id is not set
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    echo 'Error connecting to the database: ' . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessions</title>
    <!-- Ajouter la balise meta pour la politique de sécurité du contenu -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; img-src https://*; child-src 'none';">
</head>
<body>
    <h1>Sessions</h1>

    <?php if (!empty($sessions)) : ?>
        <ul>
            <?php foreach ($sessions as $session) : ?>
                <li>
                    Date: <?php echo date('d-m-Y', strtotime($session['date'])); ?> - 
                    Start Time: <?php echo date('H:i', strtotime($session['start_time'])); ?> - 
                    End Time: <?php echo date('H:i', strtotime($session['end_time'])); ?> - 
                    Hall: <?php echo isset($session['hall_name']) ? $session['hall_name'] : 'Unknown Hall'; ?>

                    <!-- "Book a Seat" button -->
                    <form action="login.php" method="POST">
                        <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($session['session_id']); ?>">
                        <input type="hidden" name="cinema_id" value="<?php echo htmlspecialchars($cinemaId); ?>">
                        <input type="submit" value="Book a Seat">
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p>No sessions available.</p>
    <?php endif; ?>
</body>
</html>


