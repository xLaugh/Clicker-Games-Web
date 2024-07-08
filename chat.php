<?php
include 'connexion.php';

// Création de la table SQL pour le chat
$create_chat_table_query = "CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($create_chat_table_query);

// Récupération des messages du chat
$get_chat_messages_query = "SELECT username, message, timestamp FROM chat_messages ORDER BY timestamp DESC LIMIT 10";
$get_chat_messages_result = $conn->query($get_chat_messages_query);

$chat_messages = [];
if ($get_chat_messages_result->num_rows > 0) {
    while ($row = $get_chat_messages_result->fetch_assoc()) {
        $chat_messages[] = $row;
    }
}

$conn->close();
?>
