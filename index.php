<?php
session_start();

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
        "SELECT filename,mime_type,file_data
         FROM uploads
         WHERE id=?"
    );

    $s->bind_param("s", $_GET["id"]);
    $s->execute();

    // Bestand gevonden
    if ($f = $s->get_result()->fetch_assoc()) {

        header("Content-Type: ".$f["mime_type"]);
        header(
            'Content-Disposition: attachment; filename="'.$f["filename"].'"'
        );

        exit($f["file_data"]);
    }

    exit("File not found");
}

$error = "";

// Upload verwerken
if (!empty($_FILES["filename"]) && $_FILES["filename"]["error"] == 0) {

    // Toegestane extensies
    $ext = strtolower(
        pathinfo($_FILES["filename"]["name"], PATHINFO_EXTENSION)
    );

    $allowed = ["png","jpg","jpeg","gif"];

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

        // Bestandgegevens ophalen
        $id = md5(uniqid());
        $name = $_FILES["filename"]["name"];
        $mime = mime_content_type($_FILES["filename"]["tmp_name"]);
        $data = file_get_contents($_FILES["filename"]["tmp_name"]);

        // Opslaan in database
        $s = $db->prepare(
            "INSERT INTO uploads (id, filename, mime_type, file_data)
             VALUES (?, ?, ?, ?)"
        );

        $s->bind_param("ssss", $id, $name, $mime, $data);
        $s->execute();

        // Downloadlink opslaan voor na redirect
        $_SESSION["link"] =
            "https://" .
            $_SERVER["HTTP_HOST"] .
            strtok($_SERVER["REQUEST_URI"], '?') .
            "?id=" . $id;

        header("Location: ".$_SERVER["PHP_SELF"]);
        exit;
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

<!-- Ondersteunde bestandstypes -->
<ul>
    <li>PNG</li>
    <li>JPG</li>
    <li>JPEG</li>
    <li>GIF</li>
</ul>

<p>Max size: 1 MB</p>

<!-- Eventuele foutmelding tonen -->
<?php if ($error) echo "<p>$error</p>"; ?>

<!-- Uploadformulier -->
<form method="post" enctype="multipart/form-data">
    <input
        type="file"
        name="filename"
        accept=".png,.jpg,.jpeg,.gif"
        required
    >
    <input type="submit" value="Upload">
</form>

<!-- Downloadlink tonen na upload -->
<?php if ($link): ?>
<p>Upload successful!</p>

<a href="<?= htmlspecialchars($link) ?>" id="link">
    <?= htmlspecialchars($link) ?>
</a>

<!-- Knop om link te kopiëren -->
<button onclick="navigator.clipboard.writeText(document.getElementById('link').href)">
    Copy
</button>
<?php endif; ?>

</body>
</html>