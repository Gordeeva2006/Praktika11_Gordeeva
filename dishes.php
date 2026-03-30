<?php
session_start();
require_once "db_connect.php";

$result = $conn->query("SELECT * FROM dishes ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Меню блюд</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            flex-direction:column;
            align-items:stretch;
            justify-content: normal;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .dishes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
        }

        .dish {
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            transition: 0.2s;
        }

        .dish:hover {
            transform: scale(1.02);
        }

        .dish img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
        }

        .dish-title {
            font-size: 20px;
            font-weight: bold;
            margin: 10px 0 5px;
        }

        .price {
            font-size: 18px;
            font-weight: bold;
            color: #2c7a7b;
        }

        .description {
            font-size: 14px;
            color: #555;
            min-height: 50px;
        }

        .btn {
            display: inline-block;
            padding: 10px;
            background: #2c7a7b;
            color: white;
            border-radius: 6px;
            margin-top: 10px;
            text-align: center;
            text-decoration: none;
        }

        .btn:hover {
            background: #285e61;
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
    </style>
</head>

<body>

<div class="nav">
    <div><strong>Ресторан</strong></div>
    <div>
        <a href="dishes.php">Меню</a>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="login.php">Войти</a>
            <a href="register.php">Регистрация</a>
        <?php else: ?>
            <a href="profile.php">Личный кабинет</a>
            <a href="logout.php">Выход</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <h2>Наше меню</h2>

    <div class="dishes-grid">

        <?php while ($dish = $result->fetch_assoc()): ?>

            <div class="dish">

                <?php if (!empty($dish['image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($dish['image']) ?>" alt="<?= htmlspecialchars($dish['name']) ?>">
                <?php else: ?>
                    <img src="no_image.png" alt="Нет изображения">
                <?php endif; ?>

                <div class="dish-title"><?= htmlspecialchars($dish['name']) ?></div>

                <div class="price"><?= number_format($dish['price'], 2, '.', '') ?> ₽</div>

                <div class="description">
                    <?= nl2br(htmlspecialchars($dish['description'])) ?>
                </div>

                <a href="add_to_cart.php?id=<?= $dish['id'] ?>" class="btn">Добавить в корзину</a>

            </div>

        <?php endwhile; ?>

    </div>
</div>

</body>
</html>
