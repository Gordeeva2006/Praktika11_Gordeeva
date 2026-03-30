<?php
session_start();
require_once "db_connect.php";

if (!isset($_GET['id'])) {
    header("Location: dishes.php");
    exit;
}

$dish_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT id FROM dishes WHERE id = ?");
$stmt->bind_param("i", $dish_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    header("Location: dishes.php");
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$dish_id])) {
    $_SESSION['cart'][$dish_id]++;
} else {
    $_SESSION['cart'][$dish_id] = 1;
}

header("Location: cart.php");
exit;
