<?php
include 'connection.php';

if (isset($_POST['add_bulk'])) {

    // --- 1. Get all the "global" data ---
    $category = $_POST['category'];
    $page = $_POST['page'];
    $description = $_POST['description'];
    $base_name = $_POST['base_name'];
    
    // Check if checkboxes were ticked
    $append_name = isset($_POST['append_name_number']);
    $same_price = isset($_POST['same_price']);
    $same_stock = isset($_POST['same_stock']);
    
    // Get prices
    $base_price = isset($_POST['base_price']) ? $_POST['base_price'] : 0;
    $individual_prices = isset($_POST['prices']) ? $_POST['prices'] : [];
    
    // Get stocks
    $base_stock = isset($_POST['base_stock']) ? (int)$_POST['base_stock'] : 0;
    $individual_stocks = isset($_POST['stocks']) ? $_POST['stocks'] : [];

    // --- 2. Re-structure the $_FILES array ---
    // PHP's default $_FILES array is hard to loop through. We fix it.
    $files_rearranged = [];
    if (isset($_FILES['images'])) {
        foreach ($_FILES['images']['name'] as $index => $name) {
            // Check for upload errors
            if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
                $files_rearranged[] = [
                    'name' => $name,
                    'tmp_name' => $_FILES['images']['tmp_name'][$index],
                    // We can add more properties here if needed
                ];
            }
        }
    }

    if (empty($files_rearranged)) {
        echo "No valid images were uploaded.";
        exit;
    }

    // --- 3. Prepare the SQL query ONCE ---
    // Using prepared statements inside a loop is very efficient.
    $sql = "INSERT INTO items (name, description, price, stock, category, image, page) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // --- 4. Loop through each file and insert it ---
    try {
        $conn->beginTransaction(); // Start a transaction

        foreach ($files_rearranged as $index => $file) {
            
            // A. Determine the item's name
            $item_name = $base_name;
            if ($append_name) {
                // $index is 0-based, so we add 1
                $item_name = $base_name . " [" . ($index + 1) . "]";
            }

            // B. Determine the item's price
            $item_price = 0.00;
            if ($same_price) {
                $item_price = $base_price;
            } else {
                // Use the price from the individual textboxes
                // This relies on the file order and price[] order matching
                if (isset($individual_prices[$index])) {
                    $item_price = $individual_prices[$index];
                }
            }
            
            // C. Determine the item's stock
            $item_stock = 0;
            if ($same_stock) {
                $item_stock = $base_stock;
            } else {
                // Use the stock from the individual textboxes
                // This relies on the file order and stocks[] order matching
                if (isset($individual_stocks[$index])) {
                    $item_stock = (int)$individual_stocks[$index];
                }
            }
            
            // D. Handle the file upload
            // Create a unique name to prevent overwriting files
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $unique_image_name = uniqid('item_', true) . '.' . $file_extension;
            $target_path = "images/" . basename($unique_image_name);

            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                
                // E. Execute the query with all our data
                $stmt->execute([
                    $item_name,
                    $description,
                    $item_price,
                    $item_stock,
                    $category,
                    $unique_image_name,
                    $page
                ]);
                
                // F. Get the inserted item ID
                $item_id = $conn->lastInsertId();
                
                // G. Handle paper type stocks
                $paperTypes = ['Vinyl', 'Holographic', 'Glitter', 'Paper', 'Die-Cut', 'Decal', 'Static-Cling'];
                $paperStockInsertStmt = $conn->prepare("INSERT INTO item_stock (item_id, paper_type, stock) VALUES (?, ?, ?)");
                
                // Check if paper_stocks data exists
                $paperStockData = null;
                if ($same_stock && isset($_POST['base_paper_stocks']) && !empty($_POST['base_paper_stocks'])) {
                    // Use base paper stocks for all items
                    $paperStockData = json_decode($_POST['base_paper_stocks'], true);
                } else if (!$same_stock && isset($_POST['paper_stocks']) && isset($_POST['paper_stocks'][$index])) {
                    // Use individual paper stocks
                    $paperStockData = json_decode($_POST['paper_stocks'][$index], true);
                }
                
                if ($paperStockData && is_array($paperStockData)) {
                    // Insert stock for each paper type from the generated data
                    foreach ($paperTypes as $paperType) {
                        $paperStock = isset($paperStockData[$paperType]) ? (int)$paperStockData[$paperType] : 0;
                        $paperStockInsertStmt->execute([$item_id, $paperType, $paperStock]);
                    }
                } else {
                    // If no paper stock data, distribute total stock evenly or set to 0
                    // Option 1: Set all to 0
                    // Option 2: Distribute evenly
                    $stockPerType = floor($item_stock / count($paperTypes));
                    $remainder = $item_stock % count($paperTypes);
                    
                    foreach ($paperTypes as $idx => $paperType) {
                        $stockValue = $stockPerType + ($idx < $remainder ? 1 : 0);
                        $paperStockInsertStmt->execute([$item_id, $paperType, $stockValue]);
                    }
                }
                
            } else {
                // If one file fails, stop everything
                throw new Exception("Failed to move uploaded file: " . $file['name']);
            }
        }

        // If all files processed without error, commit the changes
        $conn->commit();
        
        // Redirect back to the admin page
        header("Location: item_add.php?msg=Bulk add successful!");
        exit();

    } catch (Exception $e) {
        // If any error occurred, roll back all database changes
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>