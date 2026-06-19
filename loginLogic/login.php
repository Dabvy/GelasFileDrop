<?php
session_start();

$db = new mysqli("localhost", "root", "", "filedrop");

if ($db->connect_error) {
    die("Verbinding mislukt: " . $db->connect_error);
}

// Centrale functie om inlogpogingen te loggen naar de database
function logActivity($conn, $username, $action, $details) {
    $stmt = $conn->prepare("INSERT INTO logs (username, action, details) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $action, $details);
    $stmt->execute();
    $stmt->close();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // We halen id, password én de actuele role op uit de database
    $s = $db->prepare(
        "SELECT id, password, role
         FROM users
         WHERE username=?"
    );

    $s->bind_param("s", $username);
    $s->execute();

    $user = $s->get_result()->fetch_assoc();

    // Controleer of de gebruiker bestaat en het wachtwoord klopt
    if ($user && password_verify($password, $user["password"])) {

        // Genereer een nieuw sessie-ID voor de veiligheid
        session_regenerate_id(true);

        // Sla de actuele gegevens live op in de sessie
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $username;
        $_SESSION["role"] = $user["role"]; 

        // Stuur de gebruiker door naar de juiste pagina op basis van de rol in de database
        if ($_SESSION["role"] === "admin") {
            header("Location: admin.php"); // Direct naar het admin panel
        } else {
            header("Location: ../index.php"); // Normale gebruiker naar de homepage
        }
        exit;
    }

    // Als de inloggegevens niet kloppen: log de mislukte poging direct
    logActivity($db, $username, "Failed Login", "Inlogpoging mislukt (verkeerd wachtwoord of onbekende gebruiker)");
    
    $error = "Wrong username or password";
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>

    <?php if ($error): ?>
    <p style="color: red; text-align: center; font-size: 14px;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input
            type="text"
            name="username"
            placeholder="Username"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
        >

        <div class="button-group">
            <input type="submit" value="Login">
            <a href="register.php" class="register-btn">Registreer hier</a>
        </div>
    </form>
</div>

</body>
</html>