<?php
session_start();

$db = new mysqli("localhost", "root", "", "filedrop");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $s = $db->prepare(
        "SELECT id,password
         FROM users
         WHERE username=?"
    );

    $s->bind_param("s", $username);
    $s->execute();

    $user = $s->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {

        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["id"];

        // TOEGEVOEGD
        $_SESSION["username"] = $username;

        header("Location: ../index.php");
        exit;
    }

    $error = "Wrong username or password";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<h2>Login</h2>

<?php if ($error): ?>
<p><?= htmlspecialchars($error) ?></p>
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

    <input type="submit" value="Login">
</form>

</body>
</html>