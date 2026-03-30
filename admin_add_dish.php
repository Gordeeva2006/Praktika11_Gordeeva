<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $available = isset($_POST['available']) ? 1 : 0;
    $imageName = null;

    if (!empty($_FILES['image']['name'])) {

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = time() . "_" . rand(1000, 9999) . "." . $ext;

        $uploadPath = __DIR__ . "/uploads/" . $imageName;

        move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
    }

    $stmt = $conn->prepare(
        "INSERT INTO dishes (name, description, price, image, available) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssdsi", $name, $description, $price, $imageName, $available);

    if ($stmt->execute()) {
        $message = "Блюдо успешно добавлено!";
    } else {
        $message = "Ошибка: " . $stmt->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить блюдо</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
        }
        input, textarea, button {
            width: 100%;
            margin: 10px 0;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background: #2c7a7b;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
        }
        button:hover {
            background: #285e61;
        }
        .message {
            text-align: center;
            color: green;
            font-weight: bold;
        }
        .back a {
            text-decoration: none;
            color: #2c7a7b;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>

<body>

<div class="container">
    <h2>Добавить новое блюдо</h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        
        <label>Название блюда:</label>
        <input type="text" name="name" required>

        <label>Описание:</label>
        <textarea name="description" rows="4"></textarea>

        <label>Цена (₽):</label>
        <input type="number" name="price" step="0.01" min="0" required>

        <label>Фото блюда:</label>
        <input type="file" name="image" accept="image/*">

        <label class="checkbox-label">
            <input type="checkbox" name="available" checked>
            Доступно для заказа
        </label>

        <button type="submit">Добавить блюдо</button>
    </form>

    <div class="back">
        <a href="admin_dishes.php">← Назад к списку блюд</a>
    </div>
</div>

</body>
</html>
