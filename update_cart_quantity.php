<?php
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'cartCount' => 0];

if (!isset($_SESSION['user_id'])) {
    echo json_encode($response);
    exit;
}

require 'connection.php';

$user_id = $_SESSION['user_id'];
$item_id = $_POST['item_id'] ?? null;
$new_quantity = $_POST['quantity'] ?? 1;

if ($item_id === null || !is_numeric($new_quantity) || $new_quantity < 1) {
    $response['message'] = 'Invalid quantity';
    echo json_encode($response);
    exit;
}

$new_quantity = (int)$new_quantity;

try {
    $conn->beginTransaction();

    // Check item stock availability
    $stockStmt = $conn->prepare("SELECT stock FROM items WHERE id = :item_id");
    $stockStmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stockStmt->execute();
    $itemStock = $stockStmt->fetchColumn();
    $itemStock = $itemStock !== null ? (int)$itemStock : 0;

    if ($itemStock <= 0) {
        $conn->rollBack();
        $response['message'] = 'Item is out of stock.';
        echo json_encode($response);
        exit;
    }

    if ($new_quantity > $itemStock) {
        $conn->rollBack();
        $response['message'] = 'Cannot update quantity. Only ' . $itemStock . ' available in stock.';
        $response['maxStock'] = $itemStock;
        echo json_encode($response);
        exit;
    }

    // Update the quantity for this specific item
    $updateStmt = $conn->prepare("UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND item_id = :item_id");
    $updateStmt->execute([
        ':quantity' => $new_quantity,
        ':user_id' => $user_id,
        ':item_id' => $item_id
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