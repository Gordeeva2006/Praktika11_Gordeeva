<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_dishes.php");
    exit;
}

$id = intval($_GET['id']);
$message = "";

$stmt = $conn->prepare("SELECT * FROM dishes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$dish = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$dish) {
    die("Блюдо с таким ID не найдено.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $available = isset($_POST['available']) ? 1 : 0;
    $imageName = $dish['image']; 

    if (!empty($_FILES['image']['name'])) {

        if (!empty($dish['image']) && file_exists(__DIR__ . "/uploads/" . $dish['image'])) {
            unlink(__DIR__ . "/uploads/" . $dish['image']);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = time() . "_" . rand(1000, 9999) . "." . $ext;

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            __DIR__ . "/uploads/" . $imageName
        );
    }

    $stmt_update = $conn->prepare("
        UPDATE dishes 
        SET name=?, description=?, price=?, image=?, available=? 
        WHERE id=?
    ");
    $stmt_update->bind_param("ssdsii",
        $name,
        $description,
        $price,
        $imageName,
        $available,
        $id
    );

    if ($stmt_update->execute()) {
        $message = "Блюдо успешно обновлено!";

        $stmt = $conn->prepare("SELECT * FROM dishes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $dish = $stmt->get_result()->fetch_assoc();
        $stmt->close();

    } else {
        $message = "Ошибка при обновлении: " . $stmt_update->error;
    }

    $stmt_update->close();
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование блюда</title>
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
        h2 { text-align: center; margin-bottom: 20px; }
        input, textarea, button {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background: #2c7a7b;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #285e61;
        }
        img {
            max-width: 150px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .message {
            text-align: center;
            font-weight: bold;
            color: green;
        }
        .back {
            text-align: center;
            margin-top: 10px;
        }
        .back a {
            color: #2c7a7b;
            text-decoration: none;
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
    <h2>Редактирование блюда</h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">

        <label>Название блюда:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($dish['name']) ?>" required>

        <label>Описание:</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($dish['description']) ?></textarea>

        <label>Цена (₽):</label>
        <input type="number" name="price" step="0.01" min="0"
               value="<?= number_format($dish['price'], 2, '.', '') ?>" required>

        <label>Текущее изображение:</label><br>
        <?php if (!empty($dish['image'])): ?>
            <img src="uploads/<?= htmlspecialchars($dish['image']) ?>" alt="dish">
        <?php else: ?>
            <p>Нет изображения</p>
        <?php endif; ?>

        <label>Загрузить новое изображение:</label>
        <input type="file" name="image" accept="image/*">

        <label class="checkbox-label">
            <input type="checkbox" name="available" <?= $dish['available'] ? 'checked' : '' ?>>
            Доступно
        </label>

        <button type="submit">Сохранить изменения</button>
    </form>

    <div class="back">
        <a href="admin_dishes.php">← Назад к списку блюд</a>
    </div>

</div>

</body>
</html>
