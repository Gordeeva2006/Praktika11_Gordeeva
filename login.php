<?php
session_start();
require_once 'db_connect.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = "Введите логин и пароль.";
    } else {
        $secretKey = "KrisGo";
        $keyHash = md5($secretKey);
        $keyBytes = hex2bin($keyHash);

        $stmt = $conn->prepare("SELECT id, username, password, role FROM users");
        $stmt->execute();
        $result = $stmt->get_result();

        $user = null;
        $ivSize = 16;

        while ($row = $result->fetch_assoc()) {
            $rawCrypto = base64_decode($row['username']);
            $ivStored = substr($rawCrypto, 0, $ivSize);
            $encStored = substr($rawCrypto, $ivSize);
            $decUsername = openssl_decrypt($encStored, 'aes-128-cbc', $keyBytes, OPENSSL_RAW_DATA, $ivStored);

            if ($decUsername === $username) {
                $user = $row;
                break;
            }
        }
        $stmt->close();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: dishes.php");
                }
                exit;
            } else {
                $message = "Неверный пароль!";
            }
        } else {
            $message = "Пользователь не найден!";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<form method="POST" action="">
    <h2>Вход</h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <input type="text" name="username" placeholder="Логин" required>
    <input type="password" name="password" placeholder="Пароль" required>

    <button type="submit">Войти</button>

    <p style="text-align:center; margin-top:10px;">
        Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
    </p>
</form>

</body>
</html>
