<!-- index.php -->
<?php
require_once 'User.php';

session_start([
    'cookie_lifetime' => 86400, // 24 hours session lifetime
    'cookie_secure'   => true,  // Requires HTTPS
    'cookie_httponly' => true,  // Prevents client-side scripts from accessing cookies
    'use_strict_mode' => true   // Regenerates session ID on every request
]);

$dsn = 'mysql:host=localhost;dbname=cinemaBDD';
$username = 'user.php';
$password = 'Cinem@d4t4B@$e';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $cinemaQuery = "SELECT * FROM cinemas";
    $cinemaStmt = $pdo->query($cinemaQuery);
    $cinemas = $cinemaStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo 'Error connecting to the database: ' . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Complexes</title>
    <!-- Ajouter la balise meta pour la politique de sécurité du contenu -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; img-src https://*; child-src 'none';">
</head>
<body>
    <h1>Cinema Complexes</h1>

    <?php if (!empty($cinemas)) : ?>
        <ul>
            <?php foreach ($cinemas as $cinema) : ?>
                <li>
                    <a href="movies.php?cinema_id=<?php echo htmlspecialchars($cinema['cinema_id']); ?>">
                        <?php echo htmlspecialchars($cinema['name']); ?> - <?php echo htmlspecialchars($cinema['location']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p>No cinema complexes available.</p>
    <?php endif; ?>
</body>
</html>
