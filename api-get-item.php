<?php
require_once 'connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Item ID is required']);
    exit;
}

$id = $_GET['id'];

try {
    // Fetch item details
    $stmt = $conn->prepare("SELECT * FROM items WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode(['error' => 'Item not found']);
        exit;
    }

    // Fetch stock by paper type
    $stockStmt = $conn->prepare("SELECT paper_type, stock FROM item_stock WHERE item_id = :id");
    $stockStmt->bindParam(':id', $id);
    $stockStmt->execute();
    $stocks = $stockStmt->fetchAll(PDO::FETCH_KEY_PAIR); // Returns ['Vinyl' => 50, 'Holographic' => 30, ...]

    // Add stocks to item array
    $item['stocks'] = $stocks;

    echo json_encode($item);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
