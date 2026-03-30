<?php
$host = 'localhost';
$dbname = 'restaurant_system';
$user = 'root';     
$pass = '';        

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}
?>
