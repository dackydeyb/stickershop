<?php
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'cartCount' => 0];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'not_logged_in';
    echo json_encode($response);
    exit();
}

if (!isset($_POST['item_id'])) {
    $response['message'] = 'Item ID is not set.';
    echo json_encode($response);
    exit();
}

require 'connection.php';

$item_id = $_POST['item_id'];
$user_id = $_SESSION['user_id'];
$quantity_to_add = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$paper_type = isset($_POST['paper_type']) ? $_POST['paper_type'] : 'Vinyl';

if ($quantity_to_add < 1) {
    $quantity_to_add = 1;
}

// Validate paper type
$valid_paper_types = ['Vinyl', 'Holographic', 'Glitter', 'Paper', 'Die-Cut', 'Decal', 'Static-Cling'];
if (!in_array($paper_type, $valid_paper_types)) {
    $paper_type = 'Vinyl'; // Default to Vinyl if invalid
}

try {
    $conn->beginTransaction();

    // 0. Get stock for the specific paper type from item_stock table
    $stockStmt = $conn->prepare("
        SELECT stock 
        FROM item_stock 
        WHERE item_id = :item_id AND paper_type = :paper_type
    ");
    $stockStmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stockStmt->bindParam(':paper_type', $paper_type, PDO::PARAM_STR);
    $stockStmt->execute();
    $stockData = $stockStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stockData) {
        $conn->rollBack();
        $response['message'] = $paper_type . ' is not available for this item.';
        echo json_encode($response);
        exit();
    }
    
    $availableStock = (int)$stockData['stock'];

    if ($availableStock <= 0) {
        $conn->rollBack();
        $response['message'] = $paper_type . ' is out of stock.';
        echo json_encode($response);
        exit();
    }

    // 1. Check if item with same paper type already exists in cart for this user
    $checkStmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = :user_id AND item_id = :item_id AND paper_type = :paper_type");
    $checkStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $checkStmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $checkStmt->bindParam(':paper_type', $paper_type, PDO::PARAM_STR);
    $checkStmt->execute();
    $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingItem) {
        // 2a. If it exists, UPDATE the quantity (but check stock first)
        $new_quantity = $existingItem['quantity'] + $quantity_to_add;
        
        if ($new_quantity > $availableStock) {
            $conn->rollBack();
            $response['message'] = 'Cannot add more items. Only ' . $availableStock . ' ' . $paper_type . ' available in stock.';
            echo json_encode($response);
            exit();
        }
        
        $updateStmt = $conn->prepare("UPDATE cart SET quantity = :quantity WHERE id = :cart_id");
        $updateStmt->bindParam(':quantity', $new_quantity, PDO::PARAM_INT);
        $updateStmt->bindParam(':cart_id', $existingItem['id'], PDO::PARAM_INT);
        $updateStmt->execute();
        
    } else {
        // 2b. If it does not exist, INSERT a new row (but check stock first)
        if ($quantity_to_add > $availableStock) {
            $conn->rollBack();
            $response['message'] = 'Cannot add more items. Only ' . $availableStock . ' ' . $paper_type . ' available in stock.';
            echo json_encode($response);
            exit();
        }
        
        $stmt = $conn->prepare("INSERT INTO cart (user_id, item_id, quantity, paper_type) VALUES (:user_id, :item_id, :quantity, :paper_type)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity_to_add, PDO::PARAM_INT);
        $stmt->bindParam(':paper_type', $paper_type, PDO::PARAM_STR);
        $stmt->execute();
    }

    // 3. Get updated TOTAL cart count (SUM of quantities)
    $countStmt = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = :user_id");
    $countStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $countStmt->execute();
    $cartCount = $countStmt->fetchColumn();
    
    $conn->commit();

    $response['success'] = true;
    $response['message'] = 'Item successfully added to cart!';
    $response['cartCount'] = (int)$cartCount;
    
} catch (PDOException $e) {
    $conn->rollBack();
    $response['message'] = 'Error: ' . $e->getMessage();
}

$conn = null;
echo json_encode($response);
?>