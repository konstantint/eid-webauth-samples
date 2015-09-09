<?php
ini_set("session.cookie_secure", "1");
session_start();

unset($_SESSION["clientCN"]);

header("Location: index.php");


