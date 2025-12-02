<?php
/**
 * Database Migration Script
 * Adds paper type stock tracking functionality
 * 
 * This script:
 * 1. Adds stock_by_paper_type (JSON) column to items table
 * 2. Adds paper_type column to cart table
 * 3. Migrates existing stock data to new format
 */

require_once 'connection.php';

try {
    echo "Starting database migration...\n\n";
    
    // Start transaction
    $conn->beginTransaction();
    
    // ============================================
    // Step 1: Add stock_by_paper_type to items table
    // ============================================
    echo "Step 1: Adding stock_by_paper_type column to items table...\n";
    
    // Check if column already exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM items LIKE 'stock_by_paper_type'");
    if ($checkColumn->rowCount() > 0) {
        echo "⚠ Column already exists, skipping...\n\n";
    } else {
        $sql = "ALTER TABLE items ADD COLUMN stock_by_paper_type TEXT DEFAULT NULL AFTER stock";
        $conn->exec($sql);
        echo "✓ Column added successfully\n\n";
    }
    
    // ============================================
    // Step 2: Migrate existing stock data
    // ============================================
    echo "Step 2: Migrating existing stock data...\n";
    
    // Get all items
    $stmt = $conn->query("SELECT id, stock FROM items");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $paperTypes = ['Vinyl', 'Holographic', 'Glitter', 'Paper', 'Die-Cut', 'Decal', 'Static-Cling'];
    
    foreach ($items as $item) {
        // Distribute existing stock evenly across all paper types
        $stockPerType = ceil($item['stock'] / count($paperTypes));
        
        $stockData = [];
        foreach ($paperTypes as $type) {
            $stockData[$type] = $stockPerType;
        }
        
        // Update item with JSON stock data
        $updateStmt = $conn->prepare("UPDATE items SET stock_by_paper_type = :stock_json WHERE id = :id");
        $updateStmt->execute([
            ':stock_json' => json_encode($stockData),
            ':id' => $item['id']
        ]);
    }
    
    echo "✓ Migrated " . count($items) . " items\n\n";
    
    // ============================================
    // Step 3: Add paper_type to cart table
    // ============================================
    echo "Step 3: Adding paper_type column to cart table...\n";
    
    // Check if column already exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM cart LIKE 'paper_type'");
    if ($checkColumn->rowCount() > 0) {
        echo "⚠ Column already exists, skipping...\n\n";
    } else {
        $sql = "ALTER TABLE cart ADD COLUMN paper_type VARCHAR(50) DEFAULT 'Vinyl' AFTER quantity";
        $conn->exec($sql);
        echo "✓ Column added successfully\n\n";
    }
    
    // ============================================
    // Step 4: Set default paper type for existing cart items
    // ============================================
    echo "Step 4: Setting default paper type for existing cart items...\n";
    
    $sql = "UPDATE cart SET paper_type = 'Vinyl' WHERE paper_type IS NULL OR paper_type = ''";
    $affected = $conn->exec($sql);
    echo "✓ Updated $affected cart items\n\n";
    
    // Commit transaction
    $conn->commit();
    
    echo "===========================================\n";
    echo "Migration completed successfully!\n";
    echo "===========================================\n\n";
    
    echo "Summary:\n";
    echo "- Added stock_by_paper_type column to items table\n";
    echo "- Migrated $items stock data to new format\n";
    echo "- Added paper_type column to cart table\n";
    echo "- Updated $affected cart items with default paper type\n\n";
    
    echo "Next steps:\n";
    echo "1. Update item_add.php to use new stock structure\n";
    echo "2. Update shop pages to show stock per paper type\n";
    echo "3. Update cart.php to allow paper type selection\n";
    
} catch (PDOException $e) {
    // Rollback on error
    $conn->rollBack();
    echo "ERROR: Migration failed!\n";
    echo "Error message: " . $e->getMessage() . "\n";
    echo "No changes were made to the database.\n";
}

$conn = null;
?>
