<?php
session_start();

$response = ['success' => false];

if (isset($_SESSION['user_id']) && isset($_POST['id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_id = $_POST['id'];
    
    // Database connection
    include 'connection.php';
    
    // Delete item from cart
    $delete = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    if ($delete->execute([$cart_id, $user_id])) {
        $response['success'] = true;
    }
}

echo json_encode($response);
?>
