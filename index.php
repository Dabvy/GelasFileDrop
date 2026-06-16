<?php
session_start();

$conn = new mysqli("localhost", "root", "", "filedrop");

if ($conn->connect_error) {
    die("Connection failed");
}

// handle file download when id is present in URL
if (isset($_GET["id"])) {

    $stmt = $conn->prepare(
        "SELECT filename, mime_type, file_data
         FROM uploads
         WHERE id = ?"
    );

    $stmt->bind_param("s", $_GET["id"]);
    $stmt->execute();

    $result = $stmt->get_result();

    // if file exists, send it to the browser
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

// handle file upload
if (
    isset($_FILES["filename"]) &&
    $_FILES["filename"]["error"] === UPLOAD_ERR_OK
) {

    // generate unique id for file
    $id = md5(uniqid());

    $filename = $_FILES["filename"]["name"];
    $mime = $_FILES["filename"]["type"];

    // read file contents into memory
    $data = file_get_contents($_FILES["filename"]["tmp_name"]);

    $stmt = $conn->prepare(
        "INSERT INTO uploads (id, filename, mime_type, file_data)
         VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param("ssss", $id, $filename, $mime, $data);
    $stmt->execute();

    // store download link for one-time display
    $_SESSION["link"] = "?id=" . $id;

    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}

// retrieve and clear link after redirect
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