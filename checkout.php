<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$cart_items = [];
$total = 0;

$ids = implode(",", array_keys($_SESSION['cart']));
$result = $conn->query("SELECT * FROM dishes WHERE id IN ($ids)");

while ($row = $result->fetch_assoc()) {
    $row['quantity'] = $_SESSION['cart'][$row['id']];
    $row['sum'] = $row['quantity'] * $row['price'];
    $total += $row['sum'];
    $cart_items[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $stmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, dish_id, quantity, price) VALUES (?, ?, ?, ?)");

    foreach ($cart_items as $item) {
        $stmt_item->bind_param(
            "iiid", 
            $order_id,
            $item['id'],
            $item['quantity'],
            $item['price']
        );
        $stmt_item->execute();
    }

    $stmt_item->close();

    unset($_SESSION['cart']);

    header("Location: order_success.php?id=" . $order_id);
    exit;
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            flex-direction:column;
            align-items:stretch;
            justify-content: normal;
        }
        
        .container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align:center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #2c7a7b;
            color: white;
        }

        .total {
            text-align: right;
            font-size: 20px;
            margin-top: 20px;
            font-weight: bold;
        }

        .btn {
            padding: 10px 15px;
            background: #2c7a7b;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
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
        <a href="profile.php">Личный кабинет</a>
        <a href="logout.php">Выход</a>
    </div>
</div>

<div class="container">

    <h2>Оформление заказа</h2>

    <table>
        <tr>
            <th>Блюдо</th>
            <th>Цена</th>
            <th>Количество</th>
            <th>Сумма</th>
        </tr>

        <?php foreach ($cart_items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= number_format($item['price'], 2, '.', '') ?> ₽</td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['sum'], 2, '.', '') ?> ₽</td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p class="total">Итого: <?= number_format($total, 2, '.', '') ?> ₽</p>

    <form method="POST">
        <button class="btn" type="submit">Подтвердить заказ</button>
    </form>

</div>

</body>
</html>
