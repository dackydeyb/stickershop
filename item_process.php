<?php
session_start();
ob_start(); // Start output buffering to prevent any accidental output
include 'connection.php';

if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $page = $_POST['page'];
    $image = $_FILES['image']['name'];
    $target = "images/" . basename($image);
    
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

    // Move the uploaded file to the images directory
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        try {
            $conn->beginTransaction();
            
            // Insert item
            $sql = "INSERT INTO items (name, description, price, stock, category, image, page) VALUES (:name, :description, :price, :stock, :category, :image, :page)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock', $total_stock, PDO::PARAM_INT);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':page', $page);
            $stmt->execute();
            
            // Get the inserted item ID
            $item_id = $conn->lastInsertId();
            
            // Insert stock for each paper type
            $stockInsertStmt = $conn->prepare("INSERT INTO item_stock (item_id, paper_type, stock) VALUES (:item_id, :paper_type, :stock)");
            
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
                $stockInsertStmt->execute([
                    ':item_id' => $item_id,
                    ':paper_type' => $paperType,
                    ':stock' => $stock
                ]);
            }
            
            $conn->commit();
            ob_clean(); // Clear any output
            $_SESSION['success_message'] = "Item added successfully!";
            header("Location: item_add.php");
            exit();
        } catch (PDOException $e) {
            $conn->rollBack();
            ob_clean(); // Clear any output
            $_SESSION['error_message'] = "Error adding item: " . $e->getMessage();
            header("Location: item_add.php");
            exit();
        }
    } else {
        ob_clean(); // Clear any output
        $_SESSION['error_message'] = "Error uploading image. Please check file permissions and try again.";
        header("Location: item_add.php");
        exit();
    }
} else {
    // If form wasn't submitted properly, redirect back
    ob_clean();
    $_SESSION['error_message'] = "Invalid form submission.";
    header("Location: item_add.php");
    exit();
}
ob_end_flush();
