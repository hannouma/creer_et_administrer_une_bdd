<?php
require_once '../src/BackupManager.php';

$dsn = 'mysql:host=localhost;dbname=cinemaBDD';
$username = 'user.php';
$password = 'Cinem@d4t4B@$e';

session_start();

// Add this line to get the cinema_id from the form
$cinemaId = isset($_GET['cinema_id']) ? $_GET['cinema_id'] : null;
$sessionId = isset($_GET['session_id']) ? $_GET['session_id'] : null;

$usernameError = $emailError = $passwordError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Récupérer les données du formulaire de connexion and Sanitize input data
        $usernameForm = isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : null;
        $emailForm = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : null;
        $passwordForm = isset($_POST['password']) ? htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8') : null;

        // Check if password length is at least 8 characters and contains capitals, numbers, and special characters
        if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}$/', $passwordForm)) {
            $passwordError = "Le mot de passe doit contenir au moins 8 caractères, avec des majuscules, des chiffres et des caractères spéciaux.";
        }

        // Check if email format is valid
        if (!filter_var($emailForm, FILTER_VALIDATE_EMAIL)) {
            $emailError = "L'adresse e-mail n'est pas valide.";
        }

        // Récupérer l'email
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $emailForm, PDO::PARAM_STR);
        $stmt->execute();

        // Vérification si l'email existe
        if($stmt->rowCount() > 0){
            $emailError = "Cette adresse email est déjà utilisée";
        }

        // Hashage de mot de passe
        $hashedPassword = password_hash($passwordForm, PASSWORD_DEFAULT);

        if (empty($usernameError) && empty($emailError) && empty($passwordError)) {
            // Insérer les données dans la base
            $insertQuery = "INSERT INTO users (user_id, username, email, password_hash, cinema_id) VALUES (UUID(), :username, :email, :password_hash, :cinema_id)";
            $stmt = $pdo->prepare($insertQuery);
            $stmt->bindParam(':username', $usernameForm, PDO::PARAM_STR);
            $stmt->bindParam(':email', $emailForm, PDO::PARAM_STR);
            $stmt->bindParam(':password_hash', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':cinema_id', $cinemaId, PDO::PARAM_STR);
            $stmt->execute();

            // Appel de la méthode pour sauvegarder les données des utilisateurs
            $userData = array(
                'username' => $usernameForm,
                'email' => $emailForm,
                'cinema_id' => $cinemaId
            );

            BackupManager::backupUserData($userData);

            echo "Inscription réussie";

            // Redirect the user to login.php with cinema_id and session_id parameters
            header('Location: login.php?cinema_id=' . $cinemaId . '&session_id=' . $sessionId);
            exit();
        }
    }
    catch (PDOException $e){
        echo 'Erreur de connexion à la bdd'.htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <!-- Ajouter la balise meta pour la politique de sécurité du contenu -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; img-src https://*; child-src 'none';">
    <style>
        .error-message {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Inscription</h1>
    <form action="" method="POST">
        <label for="username">Nom : </label>
        <input type="text" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>"><br>
        <span class="error-message"><?php echo $usernameError; ?></span><br>

        <label for="email">Adress e-mail : </label>
        <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
        <?php if(isset($emailError)): ?>
            <span style="color: red;"><?php echo $emailError; ?></span><br><br>
        <?php endif; ?>

        <label for="password">Mot de passe : </label>
        <input type="password" name="password" required><br>
        <span class="error-message"><?php echo $passwordError; ?></span><br>

        <input type="hidden" name="cinema_id" value="<?php echo $cinemaId; ?>">
        
        <input type="hidden" name="session_id" value="<?php echo $sessionId; ?>">

        <input type="submit" value="S'inscrire">
    </form>
</body>
</html>
