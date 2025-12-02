<?php
include 'connection.php';

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $page = $_POST['page'];
    
    // Get paper type stocks
    $stock_vinyl = isset($_POST['stock_vinyl']) ? (int)$_POST['stock_vinyl'] : 0;
    $stock_holographic = isset($_POST['stock_holographic']) ? (int)$_POST['stock_holographic'] : 0;
    $stock_glitter = isset($_POST['stock_glitter']) ? (int)$_POST['stock_glitter'] : 0;
    $stock_paper = isset($_POST['stock_paper']) ? (int)$_POST['stock_paper'] : 0;
    $stock_diecut = isset($_POST['stock_diecut']) ? (int)$_POST['stock_diecut'] : 0;
    $stock_decal = isset($_POST['stock_decal']) ? (int)$_POST['stock_decal'] : 0;
    $stock_staticcling = isset($_POST['stock_staticcling']) ? (int)$_POST['stock_staticcling'] : 0;
    
    // Calculate total stock
    $total_stock = $stock_vinyl + $stock_holographic + $stock_glitter + $stock_paper + $stock_diecut + $stock_decal + $stock_staticcling;

    try {
        $conn->beginTransaction();

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $image = $_FILES['image']['name'];
            $target = "images/" . basename($image);
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
            
            $sql = "UPDATE items SET name=:name, description=:description, price=:price, stock=:stock, category=:category, page=:page, image=:image WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':image', $image);
        } else {
            $sql = "UPDATE items SET name=:name, description=:description, price=:price, stock=:stock, category=:category, page=:page WHERE id=:id";
            $stmt = $conn->prepare($sql);
        }

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $total_stock, PDO::PARAM_INT);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':page', $page);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Update stock for each paper type
        $stockUpdateStmt = $conn->prepare("
            INSERT INTO item_stock (item_id, paper_type, stock) 
            VALUES (:item_id, :paper_type, :stock)
            ON DUPLICATE KEY UPDATE stock = :stock
        ");
        
        $paperTypeStocks = [
            'Vinyl' => $stock_vinyl,
            'Holographic' => $stock_holographic,
            'Glitter' => $stock_glitter,
            'Paper' => $stock_paper,
            'Die-Cut' => $stock_diecut,
            'Decal' => $stock_decal,
            'Static-Cling' => $stock_staticcling
        ];
        
        foreach ($paperTypeStocks as $paperType => $stock) {
            $stockUpdateStmt->execute([
                ':item_id' => $id,
                ':paper_type' => $paperType,
                ':stock' => $stock
            ]);
        }

        $conn->commit();
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
            exit;
        } else {
            header("Location: item_add.php?msg=Item updated successfully");
            exit();
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error updating item: ' . $e->getMessage()]);
            exit;
        } else {
            echo "Error updating item: " . $e->getMessage();
        }
    }
}
?>