<?php
/**
 * Populate item_stock table with default stock for all existing items
 * This script adds stock entries for all paper types for each item
 */

require_once 'connection.php';

try {
    echo "Starting stock population...\n\n";
    
    $conn->beginTransaction();
    
    // Get all items
    $stmt = $conn->query("SELECT id, name FROM items");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($items) === 0) {
        echo "No items found in database. Please add items first.\n";
        exit;
    }
    
    $paperTypes = ['Vinyl', 'Holographic', 'Glitter', 'Paper', 'Die-Cut', 'Decal', 'Static-Cling'];
    $defaultStock = 50; // Default stock per paper type
    
    $insertStmt = $conn->prepare("
        INSERT INTO item_stock (item_id, paper_type, stock) 
        VALUES (:item_id, :paper_type, :stock)
        ON DUPLICATE KEY UPDATE stock = :stock
    ");
    
    $totalInserted = 0;
    
    foreach ($items as $item) {
        echo "Processing item #{$item['id']}: {$item['name']}\n";
        
        foreach ($paperTypes as $paperType) {
            $insertStmt->execute([
                ':item_id' => $item['id'],
                ':paper_type' => $paperType,
                ':stock' => $defaultStock
            ]);
            $totalInserted++;
        }
    }
    
    $conn->commit();
    
    echo "\n===========================================\n";
    echo "Stock population completed successfully!\n";
    echo "===========================================\n\n";
    
    echo "Summary:\n";
    echo "- Processed " . count($items) . " items\n";
    echo "- Created $totalInserted stock entries\n";
    echo "- Default stock per paper type: $defaultStock\n\n";
    
    echo "All items now have stock for all paper types!\n";
    
} catch (PDOException $e) {
    $conn->rollBack();
    echo "ERROR: Stock population failed!\n";
    echo "Error message: " . $e->getMessage() . "\n";
}

$conn = null;
?>
