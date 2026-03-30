<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_orders.php");
    exit;
}

$order_id = intval($_GET['id']);
$message = "";

$stmt = $conn->prepare("
    SELECT orders.*, users.username, users.email 
    FROM orders
    JOIN users ON users.id = orders.user_id
    WHERE orders.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Заказ не найден.");
}

$stmt_items = $conn->prepare("
    SELECT order_items.*, dishes.name 
    FROM order_items
    JOIN dishes ON dishes.id = order_items.dish_id
    WHERE order_items.order_id = ?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result();
$stmt_items->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $status = $_POST['status'];

    $stmt_update = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt_update->bind_param("si", $status, $order_id);

    if ($stmt_update->execute()) {
        $message = "Статус заказа успешно обновлён!";
        $order['status'] = $status;
    } else {
        $message = "Ошибка обновления: " . $stmt_update->error;
    }

    $stmt_update->close();
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование заказа</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2, h3 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #2c7a7b;
            color: #fff;
        }
        tr:hover {
            background: #edf2f7;
        }
        input, select, button {
            padding: 8px;
            width: 100%;
            margin-top: 10px;
        }
        button {
            background: #2c7a7b;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background: #285e61;
        }
        .message {
            text-align:center;
            color:green;
            font-weight:bold;
        }
        .back {
            text-align:center;
            margin-top:20px;
        }
        .back a {
            text-decoration:none;
            color:#2c7a7b;
        }
        .summary {
            font-size: 18px;
            margin-top: 10px;
            font-weight:bold;
        }
        .info-box {
            margin:10px 0;
            padding:10px;
            background:#f7fafc;
            border-radius:5px;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>Редактирование заказа #<?= $order_id ?></h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div class="info-box">
        <p><strong>Клиент:</strong> <?= htmlspecialchars($order['username']) ?> (<?= htmlspecialchars($order['email']) ?>)</p>
        <p><strong>Создан:</strong> <?= $order['created_at'] ?></p>
        <p><strong>Обновлён:</strong> <?= $order['updated_at'] ?></p>
        <p class="summary"><strong>Итоговая сумма:</strong> <?= number_format($order['total'], 2, '.', '') ?> ₽</p>
    </div>

    <h3>Позиции заказа</h3>

    <table>
        <tr>
            <th>Блюдо</th>
            <th>Количество</th>
            <th>Цена (за 1)</th>
            <th>Сумма</th>
        </tr>

        <?php while ($item = $items->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['price'], 2, '.', '') ?> ₽</td>
                <td><?= number_format($item['price'] * $item['quantity'], 2, '.', '') ?> ₽</td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h3>Изменить статус</h3>

    <form method="POST">
        <select name="status" required>
            <option value="new"        <?= $order['status'] === 'new'        ? 'selected' : '' ?>>Новый</option>
            <option value="in_progress" <?= $order['status'] === 'in_progress' ? 'selected' : '' ?>>Готовится</option>
            <option value="completed"   <?= $order['status'] === 'completed'   ? 'selected' : '' ?>>Завершён</option>
            <option value="cancelled"   <?= $order['status'] === 'cancelled'   ? 'selected' : '' ?>>Отменён</option>
        </select>

        <button type="submit">Сохранить</button>
    </form>

    <div class="back">
        <a href="admin_orders.php">← Назад к списку заказов</a>
    </div>

</div>

</body>
</html>
