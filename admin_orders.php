<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['delete'])) {
    $order_id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_orders.php");
    exit;
}

$query = "
    SELECT orders.*, users.username 
    FROM orders
    JOIN users ON users.id = orders.user_id
    ORDER BY orders.id DESC
";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление заказами</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .container {
            max-width: 1000px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; margin-bottom: 20px; }
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
            background: #f7fafc;
        }
        .actions a {
            margin-right: 10px;
            color: #2c7a7b;
            font-weight: bold;
            text-decoration: none;
        }
        .actions a.delete {
            color: #e53e3e;
        }
        .status {
            font-weight: bold;
        }
        .status.new { color: #3182ce; }
        .status.in_progress { color: #d69e2e; }
        .status.completed { color: #38a169; }
        .status.cancelled { color: #e53e3e; }

        .back {
            text-align: center;
            margin-top: 20px;
        }
        .back a {
            color: #2c7a7b;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>Все заказы</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Клиент</th>
            <th>Сумма</th>
            <th>Статус</th>
            <th>Создан</th>
            <th>Обновлён</th>
            <th>Действия</th>
        </tr>

        <?php while ($order = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['username']) ?></td>
                <td><?= number_format($order['total'], 2, '.', '') ?> ₽</td>

                <td>
                    <span class="status <?= $order['status'] ?>">
                        <?php
                            switch ($order['status']) {
                                case 'new': echo 'Новый'; break;
                                case 'in_progress': echo 'Готовится'; break;
                                case 'completed': echo 'Завершён'; break;
                                case 'cancelled': echo 'Отменён'; break;
                            }
                        ?>
                    </span>
                </td>

                <td><?= $order['created_at'] ?></td>
                <td><?= $order['updated_at'] ?></td>

                <td class="actions">
                    <a href="admin_edit_order.php?id=<?= $order['id'] ?>">Редактировать</a>
                    <a class="delete" href="?delete=<?= $order['id'] ?>"
                       onclick="return confirm('Удалить заказ?')">Удалить</a>
                </td>
            </tr>
        <?php endwhile; ?>

    </table>
    <div class="back">
        <a href="admin_dashboard.php">← Назад в панель администратора</a>
    </div>
</div>

</body>
</html>
