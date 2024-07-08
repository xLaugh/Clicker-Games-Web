<?php
include 'connexion.php';

session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

// Récupérer les informations de l'utilisateur depuis la base de données
$username = $_SESSION["username"];
$get_user_info_query = "SELECT manual_clicks, autoclickers, manual_click_price, autoclicker_price, score FROM users WHERE username='$username'";
$get_user_info_result = $conn->query($get_user_info_query);

if ($get_user_info_result->num_rows == 1) {
    $user_info = $get_user_info_result->fetch_assoc();
    $manual_clicks = $user_info["manual_clicks"];
    $autoclickers = $user_info["autoclickers"];
    $manual_click_price = $user_info["manual_click_price"];
    $autoclicker_price = $user_info["autoclicker_price"];
    $score = $user_info["score"];
} else {
    // En cas d'erreur, utiliser des valeurs par défaut
    $manual_clicks = 0;
    $autoclickers = 0;
    $manual_click_price = 20;
    $autoclicker_price = 50;
    $score = 0;
}

// Mise à jour des valeurs dans la base de données lors d'un achat
// Exemple pour le clic manuel
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["buyManualClick"])) {
    if ($score >= $manual_click_price) {
        $score -= $manual_click_price;
        $manual_clicks++;
        $manual_click_price *= 2; // Augmentation du prix du clic manuel
        $update_user_query = "UPDATE users SET score='$score', manual_clicks='$manual_clicks', manual_click_price='$manual_click_price' WHERE username='$username'";
        $conn->query($update_user_query);
    } else {
        echo "Vous n'avez pas assez de points pour acheter cela.";
    }
}

// Mise à jour des valeurs dans la base de données lors de l'achat de l'autoclicker
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["buyAutoClicker"])) {
    if ($score >= $autoclicker_price) {
        $score -= $autoclicker_price;
        $autoclickers++;
        $autoclicker_price *= 2; // Augmentation du prix de l'autoclicker
        $update_user_query = "UPDATE users SET score='$score', autoclickers='$autoclickers', autoclicker_price='$autoclicker_price' WHERE username='$username'";
        $conn->query($update_user_query);
    } else {
        echo "Vous n'avez pas assez de points pour acheter cela.";
    }
}

// Récupérez le score de l'utilisateur depuis la base de données
$username = $_SESSION["username"];
$get_score_query = "SELECT score FROM users WHERE username='$username'";
$get_score_result = $conn->query($get_score_query);

if ($get_score_result->num_rows == 1) {
    $user_data = $get_score_result->fetch_assoc();
    $score = $user_data["score"];
} else {
    $score = 0;
}

// Récupérer les 10 premiers scores
$get_top_scores_query = "SELECT username, score FROM users ORDER BY score DESC LIMIT 10";
$get_top_scores_result = $conn->query($get_top_scores_query);

$top_scores = [];
if ($get_top_scores_result->num_rows > 0) {
    while ($row = $get_top_scores_result->fetch_assoc()) {
        $top_scores[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="styles.css" rel="stylesheet">
    <link href="clicker.css" rel="stylesheet">
    <title>Clicker Game</title>
    <style>
    #chat-container {
        display: block; /* Affichage normal par défaut */
    }

    @media screen and (max-width: 900px) {
        #chat-container {
            display: none; /* Cacher le chat lorsque la largeur de l'écran est inférieure à 768 pixels */
        }
    }
</style>
</head>
<body>
    <h1>Bienvenue <?php echo $_SESSION["username"]; ?>!</h1>

    <h2>Clicker Game</h2>

    <button id="infoButton">Informations</button>

    <div id="infoModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Bienvenue dans le Clicker Game !</h2>
            <p>Dans ce jeu simple, l'objectif est de gagner des points en cliquant sur le bouton principal.</p>
            <p>Comment jouer :</p>
            <ul>
                <li>Cliquez sur le bouton principal pour gagner des points.</li>
                <li>Utilisez vos points pour acheter des améliorations qui augmentent automatiquement votre gain de points.</li>
                <li>Continuez à amasser des points et débloquez de nouvelles améliorations pour progresser dans le jeu.</li>
            </ul>
            <p>Le but ultime est d'accumuler autant de points que possible!</p>
        </div>
    </div>

    <p>Cliquez sur le bouton pour augmenter votre score :</p>
    <button id="clickButton">Cliquer</button>
    <p>Score : <span id="score"><?php echo $score; ?></span></p>
    <button id="saveButton">Sauvegarder le score</button>
    <br>
    <div id="boutique">
        <h3>Boutique</h3>
        <p>Nombre de clics manuels bonus : <span id="manualClicks"><?php echo $manual_clicks; ?></span></p>
        <p>Prix : <span id="manualClickPrice"><?php echo $manual_click_price; ?></span> points</p>
        <form method="post" onsubmit="return false;">
            <input type="submit" id="buyManualClick" value="Acheter +1 clic manuel">
        </form>
        <p>Autoclicker : <span id="autoClickerCount"><?php echo $autoclickers; ?></span></p>
        <p>Prix : <span id="autoClickerPrice"><?php echo $autoclicker_price; ?></span> points</p>
        <form method="post" onsubmit="return false;">
            <input type="submit" id="buyAutoClicker" value="Acheter Autoclicker (+1/s)">
        </form>

    </div>
    <br>
    <button id="logoutButton">Déconnexion</button>
    <br><br>
    <h3>Top 10 Scores</h3>
    <table id="topScoresTable"></table>

    <div class="chat" id="chat-container">
    <div id="chat-header">Chat</div>
    <div id="chat-messages-container">
        <?php
        include 'connexion.php';

        // Récupération des messages du chat
        $get_chat_messages_query = "SELECT username, message, timestamp FROM chat_messages ORDER BY timestamp DESC LIMIT 10";
        $get_chat_messages_result = $conn->query($get_chat_messages_query);

        $chat_messages = [];
        if ($get_chat_messages_result !== false) {
            // Vérifiez si la requête a réussi
            while ($row = $get_chat_messages_result->fetch_assoc()) {
                $chat_messages[] = $row;
            }
        }

        $conn->close();
        

        // Affichage des messages du chat
        if (!empty($chat_messages)) {
            foreach ($chat_messages as $message) {
                echo "<p><strong>{$message['username']}:</strong> {$message['message']}</p>";
            }
        } else {
            echo "<p>Aucun message dans le chat.</p>";
        }
        ?>
    </div>
    <form id="chat-form">
        <input type="hidden" id="chat-username" value="<?php echo $username; ?>">
        <input type="text" id="chat-input" placeholder="Entrez votre message">
        <button type="submit">Envoyer</button>
    </form>



    <script>
        var score = <?php echo $score; ?>;
        var manualClicks = <?php echo $manual_clicks; ?>;
        var autoclickers = <?php echo $autoclickers; ?>;
        var manualClickPrice = <?php echo $manual_click_price; ?>;
        var autoclickerPrice = <?php echo $autoclicker_price; ?>;
        var autoClickerInterval;
        var buyAutoClickerButton = document.getElementById("buyAutoClicker");

        function calculateNewPrice(currentPrice) {
            return Math.ceil(currentPrice * 2);
        }

        function updateDisplay() {
            document.getElementById("score").innerText = score;
            document.getElementById("manualClicks").innerText = manualClicks;
            document.getElementById("manualClickPrice").innerText = manualClickPrice;
            document.getElementById("autoClickerCount").innerText = autoclickers;
            document.getElementById("autoClickerPrice").innerText = autoclickerPrice;
        }

        function saveScoreAndBoutiqueToDatabase() {
            var buyAutoClickerButton = document.getElementById("buyAutoClicker");  // Déplacez cette ligne ici
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    console.log("Données sauvegardées avec succès !");
                }
            };
            xhr.open("POST", "save_score.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send("score=" + score + "&manualClicks=" + manualClicks + "&autoclickers=" + autoclickers +
                "&manualClickPrice=" + manualClickPrice + "&autoclickerPrice=" + autoclickerPrice);
        }

        setInterval(function () {
            saveScoreAndBoutiqueToDatabase();
        }, 5000);

        document.getElementById("clickButton").addEventListener("click", function () {
            score += (1 + manualClicks);
            updateDisplay();
        });

        document.getElementById("saveButton").addEventListener("click", function () {
            saveScoreAndBoutiqueToDatabase();
            alert("Score sauvegardé avec succès !");
        });

        document.getElementById("buyManualClick").addEventListener("click", function () {
            if (score >= manualClickPrice) {
                score -= manualClickPrice;
                manualClicks++;
                manualClickPrice = calculateNewPrice(manualClickPrice);
                updateDisplay();
                saveScoreAndBoutiqueToDatabase();
            } else {
                alert("Vous n'avez pas assez de points pour acheter cela.");
            }
        });

        document.getElementById("buyAutoClicker").addEventListener("click", function () {
            if (score >= autoclickerPrice) {
                score -= autoclickerPrice;
                autoclickers++;
                autoclickerPrice = calculateNewPrice(autoclickerPrice);
                updateDisplay();
                saveScoreAndBoutiqueToDatabase();
                if (autoclickers === 1) {
                    autoClickerInterval = setInterval(function () {
                        score += autoclickers;  // Ajouter le nombre d'autoclickers au score
                        updateDisplay();
                    }, 1000);
                }
            } else {
                alert("Vous n'avez pas assez de points pour acheter cela.");
            }
        });

        var autoClickerInterval;

        function startAutoClickerInterval() {
            autoClickerInterval = setInterval(function () {
                score += autoclickers;  // Ajouter le nombre d'autoclickers au score
                updateDisplay();
            }, 1000);
        }

        function stopAutoClickerInterval() {
            clearInterval(autoClickerInterval);
        }

        updateDisplay();

        if (autoclickers > 0) {
            startAutoClickerInterval();
        }

        document.getElementById("logoutButton").addEventListener("click", function () {
            clearInterval(autoClickerInterval);
            window.location.href = "index.php";
        });

        // Fonction pour mettre à jour l'affichage des 10 premiers scores
        function updateTopScores() {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var topScores = JSON.parse(xhr.responseText);
                    var topScoresTable = document.getElementById("topScoresTable");

                    // Effacer le contenu actuel du tableau
                    topScoresTable.innerHTML = "";

                    // Ajouter les en-têtes du tableau
                    var headerRow = topScoresTable.insertRow(0);
                    var usernameHeader = headerRow.insertCell(0);
                    var scoreHeader = headerRow.insertCell(1);
                    usernameHeader.innerHTML = "<b>Pseudo</b>";
                    scoreHeader.innerHTML = "<b>Score</b>";

                    // Ajouter chaque score au tableau
                    for (var i = 0; i < topScores.length; i++) {
                        var row = topScoresTable.insertRow(i + 1);
                        var usernameCell = row.insertCell(0);
                        var scoreCell = row.insertCell(1);
                        usernameCell.innerHTML = topScores[i].username;
                        scoreCell.innerHTML = topScores[i].score;
                    }
                }
            };
            xhr.open("GET", "get_top_scores.php", true);
            xhr.send();
        }

        // Fonction pour envoyer un message du chat
function sendMessage() {
    var username = document.getElementById("chat-username").value;
    var message = document.getElementById("chat-input").value;

    if (message.trim() !== "") {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById("chat-input").value = ""; // Effacer le champ de saisie après l'envoi
                updateChat(); // Mettre à jour les messages du chat après l'envoi
            }
        };
        xhr.open("POST", "send_message.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send("username=" + username + "&message=" + message);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var infoButton = document.getElementById('infoButton');
    var infoModal = document.getElementById('infoModal');
    var closeButton = document.getElementsByClassName('close')[0];

    infoButton.addEventListener('click', function () {
        infoModal.style.display = 'block';
    });

    closeButton.addEventListener('click', function () {
        infoModal.style.display = 'none';
    });

    window.addEventListener('click', function (event) {
        if (event.target == infoModal) {
            infoModal.style.display = 'none';
        }
    });
});

// Mettre à jour les messages du chat
function updateChat() {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var chatMessagesContainer = document.getElementById("chat-messages-container");
            chatMessagesContainer.innerHTML = xhr.responseText;
        }
    };
    xhr.open("GET", "get_chat_messages.php", true);
    xhr.send();
}

// Mettre à jour les messages du chat toutes les 5 secondes
setInterval(function () {
    updateChat();
}, 5000);

// Ajouter un événement submit au formulaire du chat
document.getElementById("chat-form").addEventListener("submit", function (e) {
    e.preventDefault();
    sendMessage();
});

        // Mettre à jour l'affichage initial et les 10 premiers scores
        updateDisplay();
        updateTopScores();

</script>

</body>
</html>