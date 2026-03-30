<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// === КОНСТАНТЫ ШИФРОВАНИЯ ===
$secretKey = "KrisGo";
$keyHash   = md5($secretKey);
$keyBytes  = hex2bin($keyHash);
$ivSize    = 16;

// === ФУНКЦИИ ДЛЯ РАСШИФРОВКИ (для страницы) ===
function decryptUserField($b64data, $keyBytes, $ivSize) {
    $rawCrypto = base64_decode($b64data);
    $ivStored  = substr($rawCrypto, 0, $ivSize);
    $encStored = substr($rawCrypto, $ivSize);
    return openssl_decrypt($encStored, 'aes-128-cbc', $keyBytes, OPENSSL_RAW_DATA, $ivStored);
}

// === ЧТЕНИЕ ДАННЫХ ПОЛЬЗОВАТЕЛЯ ===
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data   = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    die("Пользователь не найден.");
}

// Расшифровываем username и email для отображения
$decUsername = decryptUserField($data['username'], $keyBytes, $ivSize);
$decEmail    = decryptUserField($data['email'],    $keyBytes, $ivSize);

// === ОБНОВЛЕНИЕ ДАННЫХ ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newUsername = trim($_POST['username']);
    $newEmail    = trim($_POST['email']);
    $newPassword = trim($_POST['password']);

    // Шифруем новые логин и email, как в register.php
    $iv             = openssl_random_pseudo_bytes($ivSize);
    $encUsername    = openssl_encrypt($newUsername, 'aes-128-cbc', $keyBytes, OPENSSL_RAW_DATA, $iv);
    $encUsernameB64 = base64_encode($iv . $encUsername);

    $iv2            = openssl_random_pseudo_bytes($ivSize);
    $encEmail       = openssl_encrypt($newEmail, 'aes-128-cbc', $keyBytes, OPENSSL_RAW_DATA, $iv2);
    $encEmailB64    = base64_encode($iv2 . $encEmail);

    if (!empty($newPassword)) {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt_update = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
        $stmt_update->bind_param("sssi", $encUsernameB64, $encEmailB64, $hash, $user_id);
    } else {
        $stmt_update = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
        $stmt_update->bind_param("ssi", $encUsernameB64, $encEmailB64, $user_id);
    }

    if ($stmt_update->execute()) {
        $message = "Данные успешно обновлены!";
        // Обновляем расшифрованные значения, чтобы не было рассогласования в форме
        $decUsername = $newUsername;
        $decEmail    = $newEmail;
    } else {
        $message = "Ошибка обновления: " . $stmt_update->error;
    }

    $stmt_update->close();
}

// === ЗАКАЗЫ ===
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
        h2, h3 { text-align: center; }
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
        button:hover { background:#285e61; }

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
        th { background: #2c7a7b; color: white; }

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
            <input type="text" name="username" value="<?= htmlspecialchars($decUsername) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($decEmail) ?>" required>

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