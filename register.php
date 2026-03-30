<?php
require_once 'db_connect.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "Пароли не совпадают!";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "Такой логин или email уже зарегистрирован!";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'client')");
            $stmt->bind_param("sss", $username, $email, $hash);

            if ($stmt->execute()) {
                $message = "Регистрация успешна! Теперь можете войти.";
            } else {
                $message = "Ошибка регистрации: " . $stmt->error;
            }

            $stmt->close();
        }
        $check->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<form method="POST" action="">
    <h2>Регистрация</h2>

    <?php if (!empty($message)): ?>
        <p class="message <?= strpos($message, 'успешн') ? 'success' : '' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <input type="text" name="username" placeholder="Логин" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Пароль" required>
    <input type="password" name="confirm" placeholder="Повторите пароль" required>
    <button type="submit">Зарегистрироваться</button>

    <p style="text-align:center; margin-top:10px;">
        Уже есть аккаунт? <a href="login.php">Войти</a>
    </p>
</form>

</body>
</html>
