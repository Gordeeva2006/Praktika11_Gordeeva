<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($_POST['qty'] as $id => $qty) {
        $qty = intval($qty);
        if ($qty > 0) {
            $_SESSION['cart'][$id] = $qty;
        } else {
            unset($_SESSION['cart'][$id]); 
        }
    }
    header("Location: cart.php");
    exit;
}

$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(",", array_keys($_SESSION['cart']));

    $result = $conn->query("SELECT * FROM dishes WHERE id IN ($ids)");

    while ($row = $result->fetch_assoc()) {
        $row['quantity'] = $_SESSION['cart'][$row['id']];
        $row['sum'] = $row['quantity'] * $row['price'];
        $total += $row['sum'];
        $cart_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            flex-direction:column;
            align-items:stretch;
            justify-content: normal;
        }

        form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            width: 100vh;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        .sum {
            font-weight: bold;
            font-size: 18px;
            text-align: right;
            margin-top: 20px;
        }
        .btn {
            padding: 8px 12px;
            display: inline-block;
            text-decoration: none;
            background: #2c7a7b;
            color: white;
            border-radius: 6px;
        }
        .btn:hover {
            background: #285e61;
        }
        .qty-input {
            width: 60px;
            padding: 5px;
            text-align: center;
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
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="login.php">Войти</a>
        <?php else: ?>
            <a href="profile.php">Личный кабинет</a>
            <a href="logout.php">Выход</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">

    <h2>Корзина</h2>

    <?php if (empty($cart_items)): ?>

        <p>Ваша корзина пуста.</p>
        <a href="dishes.php" class="btn">Перейти к меню</a>

    <?php else: ?>

        <form method="POST">

            <table>
                <tr>
                    <th>Блюдо</th>
                    <th>Цена</th>
                    <th>Количество</th>
                    <th>Сумма</th>
                    <th>Удалить</th>
                </tr>

                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= number_format($item['price'], 2, '.', '') ?> ₽</td>
                        <td>
                            <input type="number" name="qty[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="1" class="qty-input">
                        </td>
                        <td><?= number_format($item['sum'], 2, '.', '') ?> ₽</td>
                        <td>
                            <a class="btn" href="cart.php?delete=<?= $item['id'] ?>">×</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </table>

            <button type="submit" class="btn" style="margin-top:20px;">Обновить корзину</button>

        </form>

        <p class="sum">ИТОГО: <?= number_format($total, 2, '.', '') ?> ₽</p>

        <a href="checkout.php" class="btn" style="float:right; margin-top:10px;">
            Оформить заказ →
        </a>

    <?php endif; ?>

</div>

</body>
</html>
