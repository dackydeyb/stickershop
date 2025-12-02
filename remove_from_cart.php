<?php
session_start();
header('Content-Type: application/json'); // Changed to JSON for consistency

$response = ['success' => false, 'cartCount' => 0];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

require 'connection.php';

$user_id = $_SESSION['user_id'];
$cart_id = $_POST['cart_id'] ?? null; // This is the 'id' from the 'cart' table

if ($cart_id === null) {
    echo json_encode($response);
    exit;
}

try {
    $conn->beginTransaction();

    // Delete the item from the cart
    $deleteStmt = $conn->prepare("DELETE FROM cart WHERE id = :cart_id AND user_id = :user_id");
    $deleteStmt->execute([
        ':cart_id' => $cart_id,
        ':user_id' => $user_id
    ]);

    // Get the new TOTAL cart count
    $countStmt = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = :user_id");
    $countStmt->execute([':user_id' => $user_id]);
    $cartCount = $countStmt->fetchColumn();

    $conn->commit();

    $response['success'] = true;
    $response['cartCount'] = (int)$cartCount;

} catch (PDOException $e) {
    $conn->rollBack();
}

echo json_encode($response);
?>