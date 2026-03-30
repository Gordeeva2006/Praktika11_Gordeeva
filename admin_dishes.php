<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['delete'])) {
    $dish_id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM dishes WHERE id = ?");
    $stmt->bind_param("i", $dish_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dishes.php");
    exit;
}

$result = $conn->query("SELECT * FROM dishes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление блюдами</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
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
        .add-btn {
            display: inline-block;
            background: #2c7a7b;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
        }
        .add-btn:hover {
            background: #285e61;
        }
        img {
            max-width: 70px;
            border-radius: 5px;
        }
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
    <h2>Управление блюдами</h2>

    <a class="add-btn" href="admin_add_dish.php">+ Добавить блюдо</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Фото</th>
            <th>Название</th>
            <th>Цена</th>
            <th>Доступно</th>
            <th>Действия</th>
        </tr>

        <?php while ($dish = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $dish['id'] ?></td>

                <td>
                    <?php if (!empty($dish['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($dish['image']) ?>" alt="dish">
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>

                <td><?= htmlspecialchars($dish['name']) ?></td>
                <td><?= number_format($dish['price'], 2, '.', '') ?> ₽</td>
                <td><?= $dish['available'] ? "Да" : "Нет" ?></td>

                <td class="actions">
                    <a href="admin_edit_dish.php?id=<?= $dish['id'] ?>">Редактировать</a>
                    <a class="delete" href="?delete=<?= $dish['id'] ?>" onclick="return confirm('Удалить блюдо?')">Удалить</a>
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
