<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$cartId = isset($data['cartId']) ? intval($data['cartId']) : 0;
$paperType = isset($data['paperType']) ? $data['paperType'] : '';

if ($cartId <= 0 || empty($paperType)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    // Verify cart item belongs to user
    $stmt = $conn->prepare("SELECT item_id, quantity FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cartId, $_SESSION['user_id']]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cartItem) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }

    // Check stock for new paper type
    $stockStmt = $conn->prepare("SELECT stock FROM item_stock WHERE item_id = ? AND paper_type = ?");
    $stockStmt->execute([$cartItem['item_id'], $paperType]);
    $stockData = $stockStmt->fetch(PDO::FETCH_ASSOC);
    
    $newStock = $stockData ? (int)$stockData['stock'] : 0;

    // Update cart
    $updateStmt = $conn->prepare("UPDATE cart SET paper_type = ? WHERE id = ?");
    $updateStmt->execute([$paperType, $cartId]);
    
    // If current quantity > new stock, update quantity?
    // For now, we just return the new stock and let frontend handle it, 
    // or we could cap it here. Let's cap it here to be safe.
    $newQuantity = $cartItem['quantity'];
    if ($newQuantity > $newStock) {
        $newQuantity = $newStock > 0 ? $newStock : 1; // Keep at least 1 if possible, or 0? 
        // Actually if stock is 0, we can't have it in cart? 
        // For now let's just cap at stock.
        if ($newStock == 0) $newQuantity = 0; // Or handle out of stock gracefully
        
        $qtyUpdateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $qtyUpdateStmt->execute([$newQuantity, $cartId]);
    }

    echo json_encode([
        'success' => true, 
        'newStock' => $newStock,
        'newQuantity' => $newQuantity
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
