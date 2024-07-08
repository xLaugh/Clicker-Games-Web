<?php
session_start();

if (isset($_SESSION["username"]) && isset($_POST["score"])) {
    include 'connexion.php';

    $username = $_SESSION["username"];
    $score = $_POST["score"];
    $manualClicks = isset($_POST["manualClicks"]) ? $_POST["manualClicks"] : 0;
    $autoclickers = isset($_POST["autoclickers"]) ? $_POST["autoclickers"] : 0;
    $manualClickPrice = isset($_POST["manualClickPrice"]) ? $_POST["manualClickPrice"] : 10; // Valeur par défaut
    $autoclickerPrice = isset($_POST["autoclickerPrice"]) ? $_POST["autoclickerPrice"] : 50; // Valeur par défaut

    // Utiliser une déclaration préparée
    $update_data_query = $conn->prepare("UPDATE users SET score = ?, manual_clicks = ?, autoclickers = ?, 
                                        manual_click_price = ?, autoclicker_price = ? WHERE username = ?");
    
    // Associer les valeurs aux paramètres de la déclaration
    $update_data_query->bind_param("iiiiis", $score, $manualClicks, $autoclickers, $manualClickPrice, $autoclickerPrice, $username);
    
    // Exécuter la déclaration
    if ($update_data_query->execute()) {
        echo "Données sauvegardées avec succès !";
    } else {
        echo "Erreur lors de la sauvegarde des données : " . $conn->error;
    }

    // Fermer la déclaration et la connexion
    $update_data_query->close();
    $conn->close();
} else {
    echo "Erreur : Utilisateur non connecté ou score manquant.";
}
?>
