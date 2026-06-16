<?php
session_start();
$conn = new mysqli("localhost", "root", "", "filedrop");
if ($conn->connect_error) {
    die("Connection failed");
}


if (isset($_GET["id"])) {

    $stmt = $conn->prepare(
        "SELECT filename, mime_type, file_data
         FROM uploads
         WHERE id = ?"
    );
    $stmt->bind_param("s", $_GET["id"]);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($file = $result->fetch_assoc()) {
        header("Content-Type: " . $file["mime_type"]);
        header(
            "Content-Disposition: attachment; filename=\"" .
            $file["filename"] .
            "\""
        );
        echo $file["file_data"];
        exit;
    }
    die("File not found");
}


session_start();

// Stuur door naar HTTPS als verbinding niet beveiligd is
if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] === "off") {
    $redirect = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    header("Location: $redirect", true, 301);
    exit;
}

if (
    isset($_FILES["filename"]) &&
    $_FILES["filename"]["error"] === UPLOAD_ERR_OK
) {
    $uploadOk = 1;
    $filename = $_FILES["filename"]["name"];
    $imageFileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // laat bepaalde file types toe
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif") {
        echo "Sorry, alleen JPG, JPEG, PNG en GIF files zijn toegestaan.";
        $uploadOk = 0;
    }

    // Chekt of de file size te groot is
    if ($_FILES["filename"]["size"] > 500000) {
        echo "Sorry, je bestand is te groot.";
        $uploadOk = 0;
    }

    // Checkt of er al een bestand met dezelfde naam bestaat
    if (file_exists($filename)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        $id = md5(uniqid());
        $mime = $_FILES["filename"]["type"];
        $data = file_get_contents($_FILES["filename"]["tmp_name"]);

        $stmt = $conn->prepare(
            "INSERT INTO uploads (id, filename, mime_type, file_data)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $id, $filename, $mime, $data);
        $stmt->execute();

        $_SESSION["link"] = "?id=" . $id;
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    }
}

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
 <form method="post" enctype="multipart/form-data">
    <input type="file" name="filename" required>
    <input type="submit" value="Upload">
 </form>
 <?php if ($link): ?>
    <p>Upload successful!</p>
    <a href="<?= htmlspecialchars($link) ?>">
        <?= htmlspecialchars($link) ?>
    </a>
<?php endif; ?>
</body>
          </html>
