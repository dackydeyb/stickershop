<?php
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'You must be logged in to checkout.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

require 'connection.php';

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['payment_method']) || !isset($input['items']) || empty($input['items'])) {
    $response['message'] = 'Invalid checkout data.';
    echo json_encode($response);
    exit;
}

$payment_method = $input['payment_method'];
$items = $input['items'];

try {
    $conn->beginTransaction();

    // Fetch cart items directly from database to ensure data integrity
    $cartStmt = $conn->prepare("SELECT item_id, quantity, paper_type FROM cart WHERE user_id = ?");
    $cartStmt->execute([$user_id]);
    $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartItems)) {
        throw new Exception("Cart is empty.");
    }
    
    foreach ($cartItems as $item) {
        $item_id = (int)$item['item_id'];
        $quantity = (int)$item['quantity'];
        $paper_type = $item['paper_type'];
        
        // Check stock in item_stock
        $stockStmt = $conn->prepare("SELECT stock FROM item_stock WHERE item_id = ? AND paper_type = ? FOR UPDATE");
        $stockStmt->execute([$item_id, $paper_type]);
        $stockData = $stockStmt->fetch(PDO::FETCH_ASSOC);
        
        $current_stock = $stockData ? (int)$stockData['stock'] : 0;
        
        // Check if enough stock is available
        if ($current_stock < $quantity) {
            throw new Exception("Insufficient stock for item ID $item_id ($paper_type). Only $current_stock available, but $quantity requested.");
        }
        
        // Update item_stock
        $new_stock = $current_stock - $quantity;
        $updateStockStmt = $conn->prepare("UPDATE item_stock SET stock = ? WHERE item_id = ? AND paper_type = ?");
        $updateStockStmt->execute([$new_stock, $item_id, $paper_type]);
        
        // Update total stock in items table
        $updateTotalStockStmt = $conn->prepare("UPDATE items SET stock = stock - ? WHERE id = ?");
        $updateTotalStockStmt->execute([$quantity, $item_id]);
    }
    
    // Clear the entire cart for this user
    $deleteCartStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $deleteCartStmt->execute([$user_id]);
    
    $conn->commit();
    
    $response['success'] = true;
    $response['message'] = 'Order placed successfully!';
    
} catch (Exception $e) {
    $conn->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>

