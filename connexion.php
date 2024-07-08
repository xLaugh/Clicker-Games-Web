<?php
// Connexion à la base de données
$servername = "localhost";
$dbusername = "";
$password = "";
$dbname = "";

$conn = new mysqli($servername, $dbusername, $password, $dbname);

// Vérifiez la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>