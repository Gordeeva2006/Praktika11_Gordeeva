<?php
session_start();
require_once "db_connect.php";

if (!isset($_GET['id'])) {
    header("Location: dishes.php");
    exit;
}

$order_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT orders.total, users.username 
    FROM orders
    JOIN users ON users.id = orders.user_id
    WHERE orders.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$order = $result->fetch_assoc();

if (!$order) {
    header("Location: dishes.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ успешно оформлен</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            flex-direction:column;
            align-items:stretch;
            justify-content: normal;
        }
        
        .container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            padding: 35px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.12);
        }

        h2 {
            color: #2c7a7b;
            font-size: 28px;
        }

        p {
            font-size: 18px;
            color: #333;
        }

        .btn {
            padding: 12px 20px;
            background: #2c7a7b;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            margin-top: 25px;
        }

        .btn:hover {
            background: #285e61;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background: #2c7a7b;
            color: #fff;
        }

        .nav a {
            color: #fff;
            margin-left: 10px;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="nav">
    <div><strong>Ресторан</strong></div>
    <div>
        <a href="dishes.php">Меню</a>
        <a href="cart.php">Корзина</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php">Личный кабинет</a>
            <a href="logout.php">Выход</a>
        <?php else: ?>
            <a href="login.php">Войти</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">

    <h2>Заказ успешно оформлен!</h2>

    <p><strong>Номер заказа:</strong> <?= $order_id ?></p>
    <p><strong>Сумма заказа:</strong> <?= number_format($order['total'], 2, '.', '') ?> ₽</p>
    <p>Спасибо за заказ, <?= htmlspecialchars($order['username']) ?>!</p>

    <a href="dishes.php" class="btn">Вернуться к меню</a>

</div>

</body>
</html>
