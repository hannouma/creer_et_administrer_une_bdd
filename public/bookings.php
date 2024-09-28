<?php
require_once '../src/User.php';
require_once '../src/Session.php';
require_once '../vendor/autoload.php';

// Load environment variables from the .env file
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Start the session
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

$pdo = new PDO($dsn, $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Function to display the welcome message
function displayWelcome($username, $session, $pdo) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Welcome</title>
        <!-- Ajouter la balise meta pour la politique de sécurité du contenu -->
        <meta http-equiv="Content-Security-Policy" content="default-src 'self'; img-src https://*; child-src 'none';">
    </head>
    <body>
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        
        <?php if ($session): ?>
            <?php
            $sessionId = isset($_GET['session_id']) ? filter_var($_GET['session_id'], FILTER_VALIDATE_INT) : null;
            // Calculate available seats
            $availableSeats = $session->getAvailableSeats($pdo);
            
            // Check if available seats are greater than 0
            if ($availableSeats > 0) {
                echo "<p>Available Seats: $availableSeats</p>";
            } else {
                echo "<p>No more seats available.</p>";
                exit(); // Stop further execution
            }
            ?>
            <form action="process_bookings.php" method="POST">            
                <h2>Selected Session Details</h2>
                <p>Movie: <?php echo htmlspecialchars($session->getMovie($pdo)->getMovieTitle()); ?></p>
                <p>Date: <?php echo date('d-m-Y', strtotime($session->getDate())); ?></p>
                <p>Start Time: <?php echo date('H:i', strtotime($session->getStartTime())); ?></p>
                <p>End Time: <?php echo date('H:i', strtotime($session->getEndTime())); ?></p>
                <p>Hall: <?php echo htmlspecialchars(($session->getHall($pdo, $sessionId)->getHallName() ?? 'Unknown Hall')); ?></p>
                <?php $hallId = $session->getHall($pdo, $_GET['session_id'])->getHallID() ?? 'Unknown Hall'; ?>
                <input type="hidden" name="hall_id" value="<?php echo htmlspecialchars($hallId); ?>">
                <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($sessionId); ?>">
                <!-- Reservation form here with tariff options and seat count -->
                <!-- Tariff options -->
                <h2>Tariff Options</h2>
                <!-- Plein Tarif -->
                <div>
                    <label for="pleinTarif">Plein Tarif ( 9€20 ): </label>
                    <input type="number" name="fullTariffSeats" value="0" min="0">
                </div>

                <!-- Étudiant -->
                <div>
                    <label for="etudiant">Étudiant ( 7€60 ) :</label>
                    <input type="number" name="studentSeats" value="0" min="0">
                </div>

                <!-- Moins de 14 ans -->
                <div>
                    <label for="moins14Ans">Moins de 14 ans ( 5€90 ) :</label>
                    <input type="number" name="under14Seats" value="0" min="0">
                </div>
                <br><br>
                <input type="submit" value="Book Tickets">
            </form>
        <?php endif; ?>
    </body>
    </html>
    <?php
    exit();
}

// Check if the user is logged in
if (isset($_SESSION['loggedInUser'])) {
    $loggedInUser = $_SESSION['loggedInUser'];

    // Check user roles
    $roles = $loggedInUser->getRoles();
    // Check if the user is an admin
    if (in_array('ROLE_ADMIN', $roles) || in_array('COMPLEX_USER', $roles)) {
        // Redirect admin or the comple_user to a different page since they can access everything without needing a session ID
        header('Location: admin_dashboard.php');
        exit();
    }


    // If the user is not an admin, proceed with the bookings page
    // Validate and sanitize session ID
    $sessionId = filter_input(INPUT_GET, 'session_id', FILTER_VALIDATE_INT);
    if ($sessionId === false || $sessionId === null) {
        // Handle invalid or missing session ID
        echo "Invalid session.";
        exit();
    }

    if ($sessionId !== null) {
        // Fetch the session based on session ID
        $sessions = Session::getSessionsBySessionId($pdo, $sessionId);

        // Check if sessions are found
        if (!empty($sessions)) {
            // Fetch the first session (there's only one session for a given session ID)
            $session = $sessions[0];
            // Display the welcome message and booking form
            displayWelcome($loggedInUser->getUsername(), $session, $pdo);
        } else {
            // Handle the case where the session is not found
            echo "Session not found for Session ID: {$sessionId}\n";
            exit();
        }
    } else {
        // Handle the case where session ID is not provided
        echo "Session ID is required.\n";
        exit();
    }
} else {
    // If not logged in, redirect to login.php
    header('Location: login.php');
    exit();
}

?>

