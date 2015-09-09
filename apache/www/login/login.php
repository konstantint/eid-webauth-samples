<?php
ini_set("session.cookie_secure", "1");
session_start();

$_SESSION["clientCN"] = $_SERVER["SSL_CLIENT_S_DN_CN"];

// Here you would normally simply redirect back to where you came from.
// In our example we are using Javascript, so this is not needed.
//header("Location: index.php");

echo $_SESSION["clientCN"];

