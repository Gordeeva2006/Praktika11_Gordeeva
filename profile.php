<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt_update = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
        $stmt_update->bind_param("sssi", $username, $email, $hash, $user_id);
    } else {
        $stmt_update = $conn->prepare("UPDATE users SET username=?, email=?, WHERE id=?");
        $stmt_update->bind_param("ssi", $username, $email, $user_id);
    }

    if ($stmt_update->execute()) {
        $message = "Данные успешно обновлены!";
    } else {
        $message = "Ошибка обновления: " . $stmt_update->error;
    }

    $stmt_update->close();

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$stmt_orders = $conn->prepare("
    SELECT id, total, status, created_at 
    FROM orders 
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$orders = $stmt_orders->get_result();
$stmt_orders->close();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
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
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .block {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2, h3 {
            text-align: center;
        }

        label {
            display:block;
            margin-top: 10px;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-top:5px;
        }

        button {
            width: 100%;
            margin-top: 15px;
            padding: 10px;
            background: #2c7a7b;
            color:white;
            border: none;
            border-radius: 5px;
        }

        button:hover {
            background:#285e61;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align:center;
        }

        th {
            background: #2c7a7b;
            color: white;
        }

        .message {
            text-align:center;
            color: green;
            font-weight:bold;
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

        .status-new { color: blue; }
        .status-in_progress { color: orange; }
        .status-completed { color: green; }
        .status-cancelled { color: red; }
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

    <div class="block">
        <h2>Ваши данные</h2>

        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Имя пользователя:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label>Пароль (оставьте пустым, чтобы не менять):</label>
            <input type="password" name="password">

            <button type="submit">Сохранить</button>
        </form>
    </div>

    <div class="block">
        <h3>История заказов</h3>

        <?php if ($orders->num_rows === 0): ?>
            <p>У вас ещё нет заказов.</p>
        <?php else: ?>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Дата</th>
                </tr>

                <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= number_format($order['total'], 2, '.', '') ?> ₽</td>
                        <td class="status-<?= $order['status'] ?>">
                            <?= match ($order['status']) {
                                'new' => 'Новый',
                                'in_progress' => 'Готовится',
                                'completed' => 'Готов',
                                'cancelled' => 'Отменён',
                            } ?>
                        </td>
                        <td><?= $order['created_at'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>

        <?php endif; ?>
    </div>

</div>

</body>
</html>
