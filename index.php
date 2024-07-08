<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clicker Game</title>
    <link href="index.css" rel="stylesheet">
</head>
<body>

<?php
include 'connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["signup"])) {
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $check_username_query = "SELECT * FROM users WHERE username='$username'";
    $check_username_result = $conn->query($check_username_query);

    if ($check_username_result->num_rows == 0) {
        $insert_user_query = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
        if ($conn->query($insert_user_query) === TRUE) {
            echo "<p>Inscription réussie. Connectez-vous maintenant.</p>";
        } else {
            echo "Erreur lors de l'inscription: " . $conn->error;
        }
    } else {
        echo "Le pseudo est déjà pris. Veuillez en choisir un autre.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $get_user_query = "SELECT * FROM users WHERE username='$username'";
    $get_user_result = $conn->query($get_user_query);

    if ($get_user_result->num_rows == 1) {
        $user_data = $get_user_result->fetch_assoc();
        if (password_verify($password, $user_data["password"])) {
            session_start();
            $_SESSION["username"] = $username;
            header("Location: clicker_game.php");
        } else {
            echo "<p>Mot de passe incorrect.</p>";
        }
    } else {
        echo "<p>Utilisateur non trouvé.</p>";
    }
}

$conn->close();
?>

<h2>Clicker Game</h2>

<!-- Formulaire d'inscription et de connexion -->
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <label for="username">Pseudo:</label>
    <input type="text" name="username" required><br>

    <label for="password">Mot de passe:</label>
    <input type="password" name="password" required><br>

    <!-- Bouton d'inscription -->
    <input type="submit" name="signup" value="S'inscrire">

    <!-- Bouton de connexion -->
    <input type="submit" name="login" value="Se connecter">
</form>

</body>
</html>
