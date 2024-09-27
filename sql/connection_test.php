<?php
$dsn = 'mysql:host=localhost;dbname=cinemabdd';
$username = 'user.php';
$password = 'Cinem@d4t4B@$e';

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