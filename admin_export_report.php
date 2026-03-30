<?php
session_start();
require_once "db_connect.php";
require_once "vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator("Restaurant Admin")
    ->setTitle("Отчёт ресторанной системы")
    ->setDescription("Автоматический отчёт, дата: " . date('d.m.Y H:i'));

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'c217bc']
    ]
];

$sheetUsers = $spreadsheet->getActiveSheet();
$sheetUsers->setTitle("Пользователи");

$sheetUsers->fromArray(
    ['ID', 'Имя', 'Email', 'Дата регистрации'],
    null,
    'A1'
);

$result = $conn->query("SELECT id, username, email, created_at FROM users");
$row = 2;
while ($u = $result->fetch_assoc()) {
    $sheetUsers->fromArray([
        $u['id'],
        $u['username'],
        $u['email'],
        $u['created_at']
    ], null, "A$row");
    $row++;
}

$sheetUsers->getStyle("A1:E1")->applyFromArray($headerStyle);
foreach (range('A','E') as $col) {
    $sheetUsers->getColumnDimension($col)->setAutoSize(true);
}

$sheetDishes = $spreadsheet->createSheet();
$sheetDishes->setTitle("Блюда");

$sheetDishes->fromArray(
    ['ID', 'Название', 'Описание', 'Цена', 'Изображение', 'Дата добавления'],
    null,
    'A1'
);

$result = $conn->query("SELECT id, name, description, price, image, created_at FROM dishes");
$row = 2;
while ($d = $result->fetch_assoc()) {
    $sheetDishes->fromArray([
        $d['id'],
        $d['name'],
        $d['description'],
        $d['price'],
        $d['image'],
        $d['created_at']
    ], null, "A$row");
    $row++;
}

$sheetDishes->getStyle("A1:F1")->applyFromArray($headerStyle);
foreach (range('A','F') as $col) {
    $sheetDishes->getColumnDimension($col)->setAutoSize(true);
}

$sheetOrders = $spreadsheet->createSheet();
$sheetOrders->setTitle("Заказы");

$sheetOrders->fromArray(
    ['ID', 'ID пользователя', 'Сумма', 'Статус', 'Дата'],
    null,
    'A1'
);

$result = $conn->query("SELECT id, user_id, total, status, created_at FROM orders");
$row = 2;
while ($o = $result->fetch_assoc()) {
    $sheetOrders->fromArray([
        $o['id'],
        $o['user_id'],
        $o['total'],
        $o['status'],
        $o['created_at']
    ], null, "A$row");
    $row++;
}

$sheetOrders->getStyle("A1:E1")->applyFromArray($headerStyle);
foreach (range('A','E') as $col) {
    $sheetOrders->getColumnDimension($col)->setAutoSize(true);
}

$sheetItems = $spreadsheet->createSheet();
$sheetItems->setTitle("Позиции заказов");

$sheetItems->fromArray(
    ['ID', 'ID заказа', 'ID блюда', 'Цена', 'Количество'],
    null,
    'A1'
);

$result = $conn->query("
    SELECT id, order_id, dish_id, price, quantity
    FROM order_items
");

$row = 2;
while ($i = $result->fetch_assoc()) {
    $sheetItems->fromArray([
        $i['id'],
        $i['order_id'],
        $i['dish_id'],
        $i['price'],
        $i['quantity']
    ], null, "A$row");
    $row++;
}

$sheetItems->getStyle("A1:E1")->applyFromArray($headerStyle);
foreach (range('A','E') as $col) {
    $sheetItems->getColumnDimension($col)->setAutoSize(true);
}


$filename = 'Отчёт_Ресторан_' . date('Y-m-d_H-i-s') . '.xlsx';

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: max-age=0");

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
