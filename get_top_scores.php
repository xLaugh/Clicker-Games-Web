<?php
include 'connexion.php';

$get_top_scores_query = "SELECT username, score FROM users ORDER BY score DESC LIMIT 10";
$get_top_scores_result = $conn->query($get_top_scores_query);

$top_scores = [];
if ($get_top_scores_result->num_rows > 0) {
    while ($row = $get_top_scores_result->fetch_assoc()) {
        $top_scores[] = $row;
    }
}

echo json_encode($top_scores);

$conn->close();
?>
