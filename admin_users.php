<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";

if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!empty($username) && !empty($email) && !empty($password)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hash, $role);
        if ($stmt->execute()) {
            $message = "Пользователь успешно добавлен!";
        } else {
            $message = "Ошибка добавления пользователя: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Заполните все поля!";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id !== $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $message = "Пользователь удалён.";
    } else {
        $message = "Вы не можете удалить свою учётную запись.";
    }
}

$result = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление пользователями</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 900px;
            margin: 40px auto;
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
            border-bottom: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #2c7a7b;
            color: white;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .actions a {
            margin-right: 10px;
            text-decoration: none;
            color: #2c7a7b;
            font-weight: bold;
        }
        .add-form {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        input, select {
            padding: 8px;
            margin: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background: #2c7a7b;
            color: white;
            border: none;
            padding: 8px 16px;
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
    <h2>Управление пользователями</h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Логин</th>
            <th>Email</th>
            <th>Роль</th>
            <th>Дата регистрации</th>
            <th>Действия</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['role'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td class="actions">
                    <a href="admin_edit_user.php?id=<?= $row['id'] ?>">✏️</a>
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Удалить пользователя?')">🗑️</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <div class="add-form">
        <h3>Добавить пользователя</h3>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Логин" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <select name="role" required>
                <option value="client">Клиент</option>
                <option value="admin">Администратор</option>
            </select>
            <button type="submit" name="add_user">Добавить</button>
        </form>
    </div>

    <div class="back">
        <a href="admin_dashboard.php">← Назад в панель администратора</a>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>