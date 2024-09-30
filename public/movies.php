<!-- movies.php -->
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
$username = $_ENV['MYSQLUSER'];
$password = $_ENV['MYSQLPASSWORD'];

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['cinema_id'])) {
        $cinemaId = $_GET['cinema_id'] ?? ''; // Retrieve the cinema_id from $_GET and initialize as empty string if not provided

        // Regular expression pattern for matching UUID format
        $uuidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

        // Check if cinema_id matches the UUID pattern
        if (preg_match($uuidPattern, $cinemaId)) {
            // Valid UUID, no need for further sanitization
        } else {
            // Invalid UUID, handle error or set a default value
            echo "Invalid cinema ID";
        }

        // Use a JOIN to retrieve movies based on movie_cinema_relationship
        $moviesQuery = "SELECT movies.* FROM movies
                        JOIN movie_cinema_relationship ON movies.movie_id = movie_cinema_relationship.movie_id
                        WHERE movie_cinema_relationship.cinema_id = :cinema_id";
        $moviesStmt = $pdo->prepare($moviesQuery);
        $moviesStmt->bindParam(':cinema_id', $cinemaId, PDO::PARAM_STR);
        $moviesStmt->execute();
        $movies = $moviesStmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Redirect to the index page if cinema_id is not set
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
    <title>Movies</title>
    <!-- Ajouter la balise meta pour la politique de sécurité du contenu -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; img-src https://*; child-src 'none';">
</head>
<body>
    <h1>Movies</h1>

    <?php if (!empty($movies)) : ?>
        <ul>
            <?php foreach ($movies as $movie) : ?>
                <li>
                    <strong>
                        <a href="sessions.php?cinema_id=<?php echo htmlspecialchars($cinemaId); ?>&movie_id=<?php echo htmlspecialchars($movie['movie_id']); ?>&track_click=true">
                            <?php echo htmlspecialchars($movie['movie_title']); ?>
                        </a>
                    </strong> - <?php echo htmlspecialchars($movie['movie_genre']); ?><br>
                    <em>Description:</em> <?php echo htmlspecialchars($movie['movie_description']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p>No movies available.</p>
    <?php endif; ?>
</body>
</html>
