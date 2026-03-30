<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_users.php");
    exit;
}

$id = intval($_GET['id']);
$message = "";

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("Пользователь не найден!");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt_update = $conn->prepare("UPDATE users SET username=?, email=?, password=?, role=? WHERE id=?");
        $stmt_update->bind_param("ssssi", $username, $email, $hash, $role, $id);
    } else {
        $stmt_update = $conn->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
        $stmt_update->bind_param("sssi", $username, $email, $role, $id);
    }

    if ($stmt_update->execute()) {
        $message = "Данные пользователя обновлены успешно!";
    } else {
        $message = "Ошибка обновления: " . $stmt_update->error;
    }

    $stmt_update->close();

    $stmt_select = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $user = $stmt_select->get_result()->fetch_assoc();
    $stmt_select->close();
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование пользователя</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 500px;
            margin: 40px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin: 6px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background: #2c7a7b;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #285e61;
        }
        .message {
            text-align: center;
            color: #2e7d32;
            font-weight: bold;
        }
        .back {
            text-align: center;
            margin-top: 15px;
        }
        .back a {
            color: #2c7a7b;
            text-decoration: none;
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Редактирование пользователя</h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Логин:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Пароль (оставьте пустым, если не нужно менять):</label>
        <input type="password" name="password" placeholder="Новый пароль">

        <label>Роль:</label>
        <select name="role" required>
            <option value="client" <?= $user['role'] === 'client' ? 'selected' : '' ?>>Клиент</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
        </select>

        <button type="submit">Сохранить изменения</button>
    </form>

    <div class="back">
        <a href="admin_users.php">← Назад к списку пользователей</a>
    </div>
</div>

</body>
</html>
