<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "moja_strona";
$port = "3307";
$login = "admin";
$pass = "admin";

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Połączenie nieudane. Błąd: " . $conn->connect_error);
}
?>
