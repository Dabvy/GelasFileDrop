<?php
session_set_cookie_params(0, '/');
session_start();

// Als de gebruiker AL ingelogd is, stuur hem naar de hoofdpagina (map omhoog)
if (isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

$db = new mysqli("localhost", "root", "", "filedrop");
if ($db->connect_error) {
    die("Verbinding mislukt: " . $db->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $db->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && password_verify($password, $result["password"])) {
        $_SESSION["user_id"] = $result["id"];
        $_SESSION["username"] = $result["username"];
        $_SESSION["role"] = $result["role"];
        
        header("Location: ../index.php"); // Ga naar de hoofdmap
        exit;
    } else {
        $error = "Onjuiste gebruikersnaam of wachtwoord.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Filedrop - Inloggen</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="register-container">
    <h2>Inloggen bij Filedrop</h2>
    
    <?php if ($error) echo "<p style='color: red;'>$error</p>"; ?>

    <form method="POST">
        <label for="username">Gebruikersnaam</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Wachtwoord</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Inloggen</button>
    </form>
    <br>
    <a href="register.php" class="login-link">Nog geen account? Registreer hier</a>
</div>
</body>
</html>
<?php $db->close(); ?>