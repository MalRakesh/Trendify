<?php
// backend/get-products.php - Get all active products

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include 'config.php';

$sql = "SELECT id, name, description, price, image, category_id, stock, featured FROM products WHERE is_active = TRUE ORDER BY created_at DESC";
$result = $conn->query($sql);

$products = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode(['status' => 'success', 'products' => $products]);

$conn->close();
?>