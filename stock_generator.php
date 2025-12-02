<?php
session_start();
// Redirect to login page if not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once 'connection.php';

// Handle stock generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_stock'])) {
    $item_id = $_POST['item_id'];
    $action = $_POST['action'];
    $stock_range = $_POST['stock_range'];
    
    try {
        $conn->beginTransaction();
        
        $paperTypes = ['Vinyl', 'Holographic', 'Glitter', 'Paper', 'Die-Cut', 'Decal', 'Static-Cling'];
        
        // Determine stock range
        switch($stock_range) {
            case 'tens':
                $min = 10;
                $max = 99;
                break;
            case 'hundreds':
                $min = 100;
                $max = 999;
                break;
            case 'thousands':
                $min = 1000;
                $max = 9999;
                break;
            default:
                $min = 10;
                $max = 99;
        }
        
        if ($action === 'all_items') {
            // Generate for all items
            $items = $conn->query("SELECT id FROM items")->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($items as $id) {
                foreach ($paperTypes as $paperType) {
                    $randomStock = rand($min, $max);
                    $stmt = $conn->prepare("
                        INSERT INTO item_stock (item_id, paper_type, stock) 
                        VALUES (:item_id, :paper_type, :stock)
                        ON DUPLICATE KEY UPDATE stock = :stock
                    ");
                    $stmt->execute([
                        ':item_id' => $id,
                        ':paper_type' => $paperType,
                        ':stock' => $randomStock
                    ]);
                }
            }
            $message = "Stock generated for all items!";
        } else {
            // Generate for single item
            foreach ($paperTypes as $paperType) {
                $randomStock = rand($min, $max);
                $stmt = $conn->prepare("
                    INSERT INTO item_stock (item_id, paper_type, stock) 
                    VALUES (:item_id, :paper_type, :stock)
                    ON DUPLICATE KEY UPDATE stock = :stock
                ");
                $stmt->execute([
                    ':item_id' => $item_id,
                    ':paper_type' => $paperType,
                    ':stock' => $randomStock
                ]);
            }
            $message = "Stock generated for item #$item_id!";
        }
        
        $conn->commit();
    } catch (PDOException $e) {
        $conn->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}

// Get all items for dropdown
$items = $conn->query("SELECT id, name FROM items ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Generator - Admin Panel</title>
    <link rel="icon" type="image/png" href="./Sticker/March 7th_4.png">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #F8F8FF;
            color: #313638;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid #323232;
        }

        .header h1 {
            font-family: 'Rubik', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #323232;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 600;
        }

        .back-link:hover {
            background: #000;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid #323232;
        }

        .card h2 {
            font-family: 'Rubik', sans-serif;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            font-size: 1rem;
            border: 2px solid #323232;
            border-radius: 8px;
            font-family: 'Nunito', sans-serif;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #000;
            background: #f8f8f8;
        }

        .btn {
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            border: 2px solid #323232;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Nunito', sans-serif;
            background: #323232;
            color: white;
        }

        .btn:hover {
            background: #000;
            transform: translateY(-2px);
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #e8f5e9;
            border: 2px solid #4CAF50;
            color: #2e7d32;
            font-weight: 600;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .radio-group input[type="radio"] {
            width: auto;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Stock Generator</h1>
            <p>Generate random stock for items across all paper types</p>
            <a href="item_add.php" class="back-link">← Back to Item Management</a>
        </div>

        <?php if (isset($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Generate Stock</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Action:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="action" value="single_item" checked onchange="toggleItemSelect()">
                            Single Item
                        </label>
                        <label>
                            <input type="radio" name="action" value="all_items" onchange="toggleItemSelect()">
                            All Items
                        </label>
                    </div>
                </div>

                <div class="form-group" id="item-select-group">
                    <label for="item_id">Select Item:</label>
                    <select name="item_id" id="item_id">
                        <?php foreach ($items as $item): ?>
                            <option value="<?php echo $item['id']; ?>">
                                #<?php echo $item['id']; ?> - <?php echo htmlspecialchars($item['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="stock_range">Stock Range:</label>
                    <select name="stock_range" id="stock_range">
                        <option value="tens">Tens (10-99)</option>
                        <option value="hundreds">Hundreds (100-999)</option>
                        <option value="thousands">Thousands (1000-9999)</option>
                    </select>
                </div>

                <button type="submit" name="generate_stock" class="btn">Generate Random Stock</button>
            </form>
        </div>

        <div class="card">
            <h2>How It Works</h2>
            <ul style="line-height: 1.8; padding-left: 20px;">
                <li>Generates random stock for all 7 paper types (Vinyl, Holographic, Glitter, Paper, Die-Cut, Decal, Static-Cling)</li>
                <li>Each paper type gets a different random stock value within the selected range</li>
                <li>Can generate for a single item or all items at once</li>
                <li>Existing stock values will be overwritten</li>
            </ul>
        </div>
    </div>

    <script>
        function toggleItemSelect() {
            const action = document.querySelector('input[name="action"]:checked').value;
            const itemSelectGroup = document.getElementById('item-select-group');
            const itemSelect = document.getElementById('item_id');
            
            if (action === 'all_items') {
                itemSelectGroup.style.display = 'none';
                itemSelect.required = false;
            } else {
                itemSelectGroup.style.display = 'block';
                itemSelect.required = true;
            }
        }
    </script>
</body>
</html>
