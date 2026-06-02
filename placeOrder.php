<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(['error'=>'Not logged in']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || empty($data['cart'])) { echo json_encode(['error'=>'No cart data']); exit; }

$buyer_id = (int)$_SESSION['user_id'];
$total    = (float)($data['total'] ?? 0);
$created  = [];

foreach ($data['cart'] as $item) {
    $product_id = (int)($item['id'] ?? 0);
    $item_total = (float)($item['price'] ?? 0) * max(1,(int)($item['qty']??1));
    if (!$product_id) continue;

    $sql = "INSERT INTO orders (buyer_id, product_id, total, status)
            VALUES ($buyer_id, $product_id, $item_total, 'pending')";
    if (mysqli_query($conn, $sql)) {
        $created[] = mysqli_insert_id($conn);
    }
}

echo json_encode(['success' => true, 'order_ids' => $created]);