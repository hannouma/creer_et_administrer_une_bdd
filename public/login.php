<?php
require_once '../src/User.php';
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
$username = $_ENV['MYSQLUSER'];
$password = $_ENV['MYSQLPASSWORD'];

// Initialize the error message
$loginErrorMsg = "";

// Get the session_id from POST or GET
$sessionId = isset($_POST['session_id']) ? $_POST['session_id'] : (isset($_GET['session_id']) ? $_GET['session_id'] : null);

// Get the cinema_id from the form
$cinemaId = isset($_POST['cinema_id']) ? $_POST['cinema_id'] : (isset($_GET['cinema_id']) ? $_GET['cinema_id'] : null);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) { // Check if the form is submitted
    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if email and password are set in the form
        // Sanitize input data
        $emailForm = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : null;
        $passwordForm = isset($_POST['password']) ? htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8') : null;

        // Check if both email and password are provided
        if (isset($emailForm) && isset($passwordForm)){
            // Retrieve the user from the database
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':email', $emailForm, PDO::PARAM_STR);
            $stmt->execute();

            // Check if the email exists
            if ($stmt->rowCount() == 1) {
                $monUser = $stmt->fetch(PDO::FETCH_ASSOC);

                // Check if the entered password matches the stored hash
                if (password_verify($passwordForm, $monUser['password_hash'])) {
                    // Create a User object
                    $loggedInUser = new User($monUser['user_id'], $monUser['username'], $monUser['email'], $monUser['password_hash'], $monUser['cinema_id']);

                    // Retrieve user roles from the database
                    $roleQuery = "SELECT * FROM userRoles JOIN roles ON roles.role_id = userRoles.roleId WHERE userId = :userId";
                    $roleStmt = $pdo->prepare($roleQuery);
                    $roleStmt->bindParam(':userId', $monUser['user_id'], PDO::PARAM_STR);
                    $roleStmt->execute();

                    // Add roles to the User object
                    while ($role = $roleStmt->fetch(PDO::FETCH_ASSOC)) {
                        $loggedInUser->addRole($role['name']);
                    }

                    // If the user has no roles, assign ROLE_USER
                    if (empty($loggedInUser->getRoles())) {
                        $loggedInUser->addRole('ROLE_USER');

                        // Save the 'ROLE_USER' to the database
                        $insertRoleQuery = "INSERT INTO userRoles (userId, roleId) VALUES (:userId, :roleId)";
                        $insertRoleStmt = $pdo->prepare($insertRoleQuery);
                        $insertRoleStmt->bindParam(':userId', $monUser['user_id'], PDO::PARAM_STR);
                        $insertRoleStmt->bindValue(':roleId', 1);  // 'ROLE_USER' has roleId 1
                        $insertRoleStmt->execute();
                    }

                    // Set the login success flag and store the User object in the session
                    $_SESSION['loginSuccess'] = true;
                    $_SESSION['loggedInUser'] = $loggedInUser;

                    // Redirect to bookings.php with the success flag
                    header('Location: bookings.php?cinema_id=' . $loggedInUser->getCinemaId() . '&session_id=' . $sessionId);
                    exit();
                } else {
                    // Set the login error message if the Email or password is incorrect.
                    $loginErrorMsg = "Votre email ou votre mot de passe est incorrect, réessayez de vous connecter ou de vous inscrire";
                }
            } else {
                // Set the login error message if User not found.
                $loginErrorMsg = "Réessayez de vous connecter ou de vous inscrire";
            }
        } else {
            // Set the login error message
            $loginErrorMsg = "Please enter both email and password.";
        }
    } catch (PDOException $e) {
        echo 'Erreur de connexion à la BDD' . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <!-- Ajouter la balise meta pour la politique de sécurité du contenu -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; img-src https://*; child-src 'none';">
</head>
<body>
    <h1>Connexion</h1>
    <!-- Display the error message only if the form has been submitted -->
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $loginErrorMsg): ?>
        <p style="color: red;"><?php echo $loginErrorMsg; ?></p>
    <?php endif; ?>
    <form method="POST">
        <!-- EMAIL -->
        <label for="email">Adresse e-mail : </label>
        <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>" required><br>
        <label for="password">Mot de passe : </label>
        <input type="password" name="password" required><br>
        <!-- The hidden input field for cinema_id -->
        <input type="hidden" name="cinema_id" value="<?php echo $cinemaId; ?>">
        <!-- The hidden input field for session_id -->
        <input type="hidden" name="session_id" value="<?php echo $sessionId; ?>">
        <!-- BUTTON -->
        <input type="submit" name="submit" value="Se connecter">
    </form>

    <!-- "Sign Up" button -->
    <p>Don't have an account? <a href="register.php?cinema_id=<?php echo $cinemaId; ?>&session_id=<?php echo $sessionId; ?>">Sign Up</a></p>
</body>
</html>
