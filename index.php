<?php
session_set_cookie_params(0, '/');
session_start();

// Als de gebruiker NIET is ingelogd, stuur naar de submap
if (!isset($_SESSION["user_id"])) {
    header("Location: loginLogic/login.php");
    exit;
}

// Forceer HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

$db = new mysqli("localhost", "root", "", "filedrop");

if ($db->connect_error) {
    die("Verbinding mislukt: " . $db->connect_error);
}

function logActivity($conn, $username, $action, $details) {
    $stmt = $conn->prepare("INSERT INTO logs (username, action, details) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $action, $details);
    $stmt->execute();
    $stmt->close();
}

define('ENCRYPTION_KEY', 'JouwSuperGeheimeSleutel123!#'); 

$error = "";

if (!empty($_FILES["filename"]) && $_FILES["filename"]["error"] == 0) {
    $recipient = trim($_POST["recipient"] ?? "");

    $check = $db->prepare("SELECT id FROM users WHERE username=?");
    $check->bind_param("s", $recipient);
    $check->execute();

    if (!$check->get_result()->fetch_assoc()) {
        $error = "User does not exist.";
    }

    $ext = strtolower(pathinfo($_FILES["filename"]["name"], PATHINFO_EXTENSION));
    $allowed = ["png", "jpg", "jpeg", "gif"];

    if (!$error) {
        if (!in_array($ext, $allowed)) {
            $error = "Only PNG, JPG, JPEG and GIF allowed.";
        } elseif ($_FILES["filename"]["size"] > 1048576) {
            $error = "Max size is 1 MB.";
        } elseif (!getimagesize($_FILES["filename"]["tmp_name"])) {
            $error = "Invalid image.";
        } else {
            $file_id = hash('sha256', uniqid(rand(), true));
            $name = $_FILES["filename"]["name"];
            $mime = mime_content_type($_FILES["filename"]["tmp_name"]);
            $raw_data = file_get_contents($_FILES["filename"]["tmp_name"]);

            $iv_length = openssl_cipher_iv_length('aes-256-cbc');
            $iv = openssl_random_pseudo_bytes($iv_length);
            $encrypted_data = openssl_encrypt($raw_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
            
            $final_payload = base64_encode($iv . $encrypted_data);

            $s = $db->prepare("INSERT INTO uploads (id, filename, mime_type, file_data, recipient, sender) VALUES (?, ?, ?, ?, ?, ?)");
            $sender = $_SESSION["username"];
            $s->bind_param("ssssss", $file_id, $name, $mime, $final_payload, $recipient, $sender);
            
            if ($s->execute()) {
                logActivity($db, $sender, "Upload", $sender . " heeft bestand '" . $name . "' verzonden naar " . $recipient);
            }
            $s->close();

            $current_dir = rtrim(dirname($_SERVER["PHP_SELF"]), '/\\');
            $_SESSION["link"] = "https://" . $_SERVER["HTTP_HOST"] . $current_dir . "/download.php?file=" . $file_id;

            header("Location: " . $_SERVER["PHP_SELF"]);
            exit;
        }
    }
}

$link = $_SESSION["link"] ?? "";
unset($_SESSION["link"]);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>GelasFileDrop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>GelasFileDrop</h2>
    <p>Logged in as: <strong><?= htmlspecialchars($_SESSION["username"]) ?></strong></p>
    <p>
        <a href="loginLogic/logout.php">Logout</a>
        <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
            <a href="loginLogic/admin.php" class="admin-btn">Naar Admin Panel</a>
        <?php endif; ?>
    </p>

    <p>Toegestane bestanden: PNG, JPG, JPEG, GIF (Max 1 MB)</p>

    <?php if ($error) echo "<p style='color: red;'>$error</p>"; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="text" name="recipient" placeholder="Send to username" required><br><br>
        <input type="file" name="filename" accept=".png,.jpg,.jpeg,.gif" required><br><br>
        <input type="submit" value="Upload & Versleutel">
    </form>

    <?php if ($link): ?>
        <hr>
        <p style="color: #28a745; font-weight: bold;">Bestand succesvol versleuteld en opgeslagen!</p>
        <p>Stuur de onderstaande link naar de ontvanger:</p>
        <div class="link-box">
            <a href="<?= htmlspecialchars($link) ?>" id="link"><?= htmlspecialchars($link) ?></a>
        </div>
        <br>
        <button onclick="navigator.clipboard.writeText(document.getElementById('link').href)">Kopieer Link</button>
    <?php endif; ?>
</div>
</body>
</html>
<?php $db->close(); ?>