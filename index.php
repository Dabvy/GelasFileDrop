<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: loginLogic/login.php");
    exit;
}

// Forceer HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

// Verbinding met database
$db = new mysqli("localhost", "root", "", "filedrop");

// Download bestand via ID
if (isset($_GET["id"])) {

    $s = $db->prepare(
        "SELECT filename,mime_type,file_data,recipient
         FROM uploads
         WHERE id=?"
    );

    $s->bind_param("s", $_GET["id"]);
    $s->execute();

    if ($f = $s->get_result()->fetch_assoc()) {

        // Alleen de bedoelde ontvanger mag downloaden
        if ($_SESSION["username"] !== $f["recipient"]) {
            exit("You are not allowed to download this file.");
        }

        header("Content-Type: " . $f["mime_type"]);
        header(
            'Content-Disposition: attachment; filename="' .
            $f["filename"] .
            '"'
        );

        exit($f["file_data"]);
    }

    exit("File not found");
}

$error = "";

// Upload verwerken
if (!empty($_FILES["filename"]) && $_FILES["filename"]["error"] == 0) {

    $recipient = trim($_POST["recipient"] ?? "");

    // Controleer of gebruiker bestaat
    $check = $db->prepare(
        "SELECT id
         FROM users
         WHERE username=?"
    );

    $check->bind_param("s", $recipient);
    $check->execute();

    if (!$check->get_result()->fetch_assoc()) {
        $error = "User does not exist.";
    }

    // Toegestane extensies
    $ext = strtolower(
        pathinfo($_FILES["filename"]["name"], PATHINFO_EXTENSION)
    );

    $allowed = ["png", "jpg", "jpeg", "gif"];

    if (!$error) {

        // Controleer extensie
        if (!in_array($ext, $allowed))
            $error = "Only PNG, JPG, JPEG and GIF allowed.";

        // Controleer maximale grootte (1 MB)
        elseif ($_FILES["filename"]["size"] > 1048576)
            $error = "Max size is 1 MB.";

        // Controleer of het echt een afbeelding is
        elseif (!getimagesize($_FILES["filename"]["tmp_name"]))
            $error = "Invalid image.";

        else {

            $id = md5(uniqid());
            $name = $_FILES["filename"]["name"];
            $mime = mime_content_type($_FILES["filename"]["tmp_name"]);
            $data = file_get_contents($_FILES["filename"]["tmp_name"]);

            $s = $db->prepare(
                "INSERT INTO uploads
                (id, filename, mime_type, file_data, recipient)
                VALUES (?, ?, ?, ?, ?)"
            );

            $s->bind_param(
                "sssss",
                $id,
                $name,
                $mime,
                $data,
                $recipient
            );

            $s->execute();

            $_SESSION["link"] =
                "https://" .
                $_SERVER["HTTP_HOST"] .
                strtok($_SERVER["REQUEST_URI"], '?') .
                "?id=" . $id;

            header("Location: " . $_SERVER["PHP_SELF"]);
            exit;
        }
    }
}

// Link ophalen en daarna verwijderen uit sessie
$link = $_SESSION["link"] ?? "";
unset($_SESSION["link"]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>GelasFileDrop</title>
</head>
<body>

<h2>GelasFileDrop</h2>

<p>
    Logged in as:
    <?= htmlspecialchars($_SESSION["username"]) ?>
</p>

<p>
    <a href="loginLogic/logout.php">Logout</a>
</p>

<ul>
    <li>PNG</li>
    <li>JPG</li>
    <li>JPEG</li>
    <li>GIF</li>
</ul>

<p>Max size: 1 MB</p>

<?php if ($error) echo "<p>$error</p>"; ?>

<form method="post" enctype="multipart/form-data">

    <input
        type="text"
        name="recipient"
        placeholder="Send to username"
        required
    >

    <br><br>

    <input
        type="file"
        name="filename"
        accept=".png,.jpg,.jpeg,.gif"
        required
    >

    <input type="submit" value="Upload">

</form>

<?php if ($link): ?>

<p>Upload successful!</p>

<a href="<?= htmlspecialchars($link) ?>" id="link">
    <?= htmlspecialchars($link) ?>
</a>

<button onclick="navigator.clipboard.writeText(document.getElementById('link').href)">
    Copy
</button>

<?php endif; ?>

</body>
</html>