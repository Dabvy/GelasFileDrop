<?php

session_start();

session_destroy();

header("Location: login.php");
exit;
?>
<link rel="stylesheet" href="style.css">