<?php
require_once './vendor/autoload.php';

// Load environment variables from the .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Use environment variables for MySQL
$dsn = $_ENV['DB_DSN'];
$username = $_ENV['MYSQLUSER'];
$password = $_ENV['MYSQLPASSWORD'];

try{
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //Récupèrer les utilisateurs
    $query = "SELECT * FROM users";
    $stmt = $pdo->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Afficher les utilisateurs
    foreach ($users as $user){
        echo "ID : ".$user['id']."<br>";
        echo "Username : ".$user['username']."<br>";
        echo "email : ".$user['email']."<br>";
        echo "<br>";
    }
}
catch (PDOException $e){
    echo 'Erreue de connexion à la bdd'.$e->getMessage();
}