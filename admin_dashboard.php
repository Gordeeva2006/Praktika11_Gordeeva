<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard {
            max-width: 700px;
            margin: 50px auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        .nav a {
            text-decoration: none;
            background: #2c7a7b;
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .nav a:hover {
            background: #285e61;
        }
        .logout {
            display: block;
            text-align: center;
            margin-top: 30px;
        }
        .logout a {
            color: #d32f2f;
            text-decoration: none;
        }
        .btn-report {
            display:inline-block;
            padding:10px 15px;
            background:#2c7a7b;
            color:white;
            border-radius:5px;
            text-decoration:none;
        }
        .btn-report:hover {
            background:#285e61;
        }
    </style>
</head>
<body>

<div class="dashboard">
    <h1>Добро пожаловать, <?= $username ?>!</h1>
    <h3 style="text-align:center; color:#555;">Панель администратора</h3>

    <div class="nav">
        <a href="admin_users.php">Управление пользователями</a>
        <a href="admin_dishes.php">Управление блюдами</a>
        <a href="admin_orders.php">Управление заказами</a>
        <a href="admin_export_report.php" class="btn-report">Сформировать Excel-отчёт</a>
    </div>

    <div class="logout">
        <a href="logout.php">Выйти из системы</a>
    </div>
</div>

</body>
</html>
