<?php
include 'connexion.php';

// Fonction pour vérifier si un message contient des mots bloqués
function isMessageBlocked($conn, $message) {
    // Récupérer la liste des mots bloqués
    $get_blocked_words_query = "SELECT word FROM blocked_words";
    $get_blocked_words_statement = $conn->prepare($get_blocked_words_query);
    $get_blocked_words_statement->execute();
    $get_blocked_words_statement->bind_result($blockedWord);

    while ($get_blocked_words_statement->fetch()) {
        // Vérifier si le mot bloqué est présent dans le message
        if (stripos($message, $blockedWord) !== false) {
            return true;
        }
    }

    return false;
}

// Récupération des messages du chat
$get_chat_messages_query = "SELECT username, message, timestamp FROM chat_messages ORDER BY timestamp DESC LIMIT 10";
$get_chat_messages_statement = $conn->prepare($get_chat_messages_query);
$get_chat_messages_statement->execute();
$get_chat_messages_statement->bind_result($username, $messageContent, $timestamp);

$chat_messages = [];
while ($get_chat_messages_statement->fetch()) {
    $chat_messages[] = ['username' => $username, 'message' => $messageContent, 'timestamp' => $timestamp];
}

// Affichage des messages du chat
if (!empty($chat_messages)) {
    foreach ($chat_messages as $message) {
        $username = $message['username'];
        $messageContent = $message['message'];

        // Vérifier si le message contient des mots bloqués
        $isBlocked = isMessageBlocked($conn, $messageContent);

        if (!$isBlocked) {
            echo "<p><strong>{$username}:</strong> {$messageContent}</p>";
        } else {
            echo "<p><strong>{$username}:</strong> Ce message a été bloqué.</p>";
        }
    }
} else {
    echo "<p>Aucun message dans le chat.</p>";
}

$conn->close();
?>
