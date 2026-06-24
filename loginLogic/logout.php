<?php
session_start();
session_destroy();

// Staat al in de loginLogic map, dus direct naar login.php redirecten
header("Location: login.php");
exit;
?>