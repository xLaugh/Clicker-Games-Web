<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $username = $_POST["username"];
    $message = $_POST["message"];

    include 'connexion.php';

    // Fonction pour vérifier si un message contient des mots bloqués
    function isMessageBlocked($message) {
        include 'connexion.php';

        // Récupérer la liste des mots bloqués
        $get_blocked_words_query = "SELECT word FROM blocked_words";
        $get_blocked_words_result = $conn->query($get_blocked_words_query);

        if ($get_blocked_words_result !== false) {
            while ($row = $get_blocked_words_result->fetch_assoc()) {
                $blockedWord = $row['word'];

                // Vérifier si le mot bloqué est présent dans le message
                if (stripos($message, $blockedWord) !== false) {
                    $conn->close();
                    return true;
                }
            }
        }

        $conn->close();
        return false;
    }

    // Vérifier le cooldown du message
    $cooldown_query = "SELECT MAX(UNIX_TIMESTAMP(timestamp)) as last_message_time FROM chat_messages WHERE username = ?";
    $cooldown_statement = $conn->prepare($cooldown_query);
    $cooldown_statement->bind_param("s", $username);
    $cooldown_statement->execute();
    $cooldown_result = $cooldown_statement->get_result();
    $cooldown_row = $cooldown_result->fetch_assoc();
    $last_message_time = $cooldown_row['last_message_time'];
    $current_time = time();

    if (($current_time - $last_message_time) < 3) {
        echo "Veuillez attendre 3 secondes entre chaque message.";
    } elseif (!isMessageBlocked($message)) {
        // Utiliser une déclaration préparée
        $insert_message_query = $conn->prepare("INSERT INTO chat_messages (username, message) VALUES (?, ?)");
        
        // Associer les valeurs aux paramètres de la déclaration
        $insert_message_query->bind_param("ss", $username, $message);
        
        // Exécuter la déclaration
        $insert_message_query->execute();

        // Fermer la déclaration
        $insert_message_query->close();
    } else {
        echo "Le message contient des mots bloqués et ne sera pas publié.";
    }

    // Fermer la connexion
    $conn->close();
}
?>
