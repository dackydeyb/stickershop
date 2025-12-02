<?php
include 'connection.php';

// Check if a form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Make sure bulk_action is set
    if (isset($_POST['bulk_action'])) {
        $action = $_POST['bulk_action'];
        $item_ids = isset($_POST['item_ids']) ? $_POST['item_ids'] : [];

        // Stop if no items were selected
        if (empty($item_ids)) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'No items selected']);
                exit;
            } else {
                header('Location: item_add.php?msg=No items selected');
                exit;
            }
        }

        // Create the "IN (?, ?, ?)" string for the SQL query
        $placeholders = implode(',', array_fill(0, count($item_ids), '?'));
        
        try {
            switch ($action) {
                case 'delete':
                    $sql = "DELETE FROM items WHERE id IN ($placeholders)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($item_ids);
                    break;

                case 'update_category':
                    $new_category = $_POST['new_category'];
                    $sql = "UPDATE items SET category = ? WHERE id IN ($placeholders)";
                    $stmt = $conn->prepare($sql);
                    
                    $params = array_merge([$new_category], $item_ids);
                    $stmt->execute($params);
                    break;

                case 'update_description':
                    if (!isset($_POST['new_description']) || trim($_POST['new_description']) === '') {
                        throw new Exception('Description is required');
                    }
                    
                    $new_description = $_POST['new_description'];
                    $sql = "UPDATE items SET description = ? WHERE id IN ($placeholders)";
                    $stmt = $conn->prepare($sql);
                    
                    $params = array_merge([$new_description], $item_ids);
                    $stmt->execute($params);
                    break;

                case 'update_price':
                    // Check if random prices mode is used
                    if (isset($_POST['random_prices_json']) && !empty($_POST['random_prices_json'])) {
                        $random_prices = json_decode($_POST['random_prices_json'], true);
                        
                        if ($random_prices && is_array($random_prices)) {
                            $update_sql = "UPDATE items SET price = ? WHERE id = ?";
                            $update_stmt = $conn->prepare($update_sql);
                            
                            foreach ($item_ids as $item_id) {
                                if (isset($random_prices[$item_id])) {
                                    $update_stmt->execute([$random_prices[$item_id], $item_id]);
                                }
                            }
                        } else {
                            throw new Exception('Invalid random prices data');
                        }
                    } else {
                        if (!isset($_POST['new_price']) || $_POST['new_price'] === '') {
                            throw new Exception('Price is required');
                        }
                        
                        $new_price = $_POST['new_price'];
                        $sql = "UPDATE items SET price = ? WHERE id IN ($placeholders)";
                        $stmt = $conn->prepare($sql);
                        
                        $params = array_merge([$new_price], $item_ids);
                        $stmt->execute($params);
                    }
                    break;
                
                case 'update_page':
                    $new_page = $_POST['new_page'];
                    $sql = "UPDATE items SET page = ? WHERE id IN ($placeholders)";
                    $stmt = $conn->prepare($sql);
                    
                    $params = array_merge([$new_page], $item_ids);
                    $stmt->execute($params);
                    break;

                case 'update_stock':
                    if (isset($_POST['random_stock_json']) && !empty($_POST['random_stock_json'])) {
                        $random_stock = json_decode($_POST['random_stock_json'], true);
                        
                        if ($random_stock && is_array($random_stock)) {
                            $update_sql = "UPDATE items SET stock = ? WHERE id = ?";
                            $update_stmt = $conn->prepare($update_sql);
                            
                            foreach ($item_ids as $item_id) {
                                if (isset($random_stock[$item_id])) {
                                    $stock_value = (int)$random_stock[$item_id];
                                    if ($stock_value < 0) {
                                        throw new Exception('Stock cannot be negative for item ID: ' . $item_id);
                                    }
                                    $update_stmt->execute([$stock_value, $item_id]);
                                }
                            }
                        } else {
                            throw new Exception('Invalid random stock data');
                        }
                    } else {
                        if (!isset($_POST['new_stock']) || $_POST['new_stock'] === '') {
                            throw new Exception('Stock is required');
                        }
                        
                        $new_stock = (int)$_POST['new_stock'];
                        if ($new_stock < 0) {
                            throw new Exception('Stock cannot be negative');
                        }
                        
                        $sql = "UPDATE items SET stock = ? WHERE id IN ($placeholders)";
                        $stmt = $conn->prepare($sql);
                        
                        $params = array_merge([$new_stock], $item_ids);
                        $stmt->execute($params);
                    }
                    break;

                case 'generate_stock':
                    // Generate random stock for all paper types for selected items
                    $paperTypes = ['Vinyl', 'Holographic', 'Glitter', 'Paper', 'Die-Cut', 'Decal', 'Static-Cling'];
                    
                    $conn->beginTransaction();
                    
                    try {
                        $stockInsertStmt = $conn->prepare("
                            INSERT INTO item_stock (item_id, paper_type, stock) 
                            VALUES (:item_id, :paper_type, :stock)
                            ON DUPLICATE KEY UPDATE stock = :stock
                        ");
                        
                        $totalStockUpdateStmt = $conn->prepare("UPDATE items SET stock = :total_stock WHERE id = :item_id");
                        
                        foreach ($item_ids as $item_id) {
                            $totalStock = 0;
                            
                            // Generate random stock for each paper type
                            foreach ($paperTypes as $paperType) {
                                $randomStock = rand(10, 99); // Random stock between 10-99
                                $totalStock += $randomStock;
                                
                                $stockInsertStmt->execute([
                                    ':item_id' => $item_id,
                                    ':paper_type' => $paperType,
                                    ':stock' => $randomStock
                                ]);
                            }
                            
                            // Update total stock in items table
                            $totalStockUpdateStmt->execute([
                                ':total_stock' => $totalStock,
                                ':item_id' => $item_id
                            ]);
                        }
                        
                        $conn->commit();
                    } catch (Exception $e) {
                        $conn->rollBack();
                        throw $e;
                    }
                    break;

            }

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Bulk action successful!']);
                exit;
            } else {
                header('Location: item_add.php?msg=Bulk action successful!');
                exit;
            }

        } catch (Exception $e) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                exit;
            } else {
                header('Location: item_add.php?msg=Error: ' . $e->getMessage());
                exit;
            }
        }
    } else {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
        } else {
            header('Location: item_add.php?msg=Invalid action');
            exit;
        }
    }
} else {
    header('Location: item_add.php');
    exit;
}
?>