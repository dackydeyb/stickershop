<?php
// item_add.php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

include 'connection.php';

// Check for success/error messages from item_process.php
$success_message = '';
$error_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// --- SERVER-SIDE PAGINATION, SEARCH, AND SORT LOGIC ---

// 1. Define pagination
$items_per_page = 20;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $items_per_page;

// 2. Define search
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// 3. Define sort
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'name'; // Default sort column
$sort_dir = isset($_GET['dir']) && in_array($_GET['dir'], ['asc', 'desc']) ? $_GET['dir'] : 'asc'; // Default sort direction
$next_dir = ($sort_dir == 'asc') ? 'desc' : 'asc'; // For table header links

// 4. Build the SQL query
$sql = "SELECT * FROM items";
$count_sql = "SELECT COUNT(*) FROM items";
$params = [];
$where_clauses = [];

if (!empty($search_term)) {
    $where_clauses[] = "(name LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search_term%";
}

if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
    $count_sql .= " WHERE " . implode(' AND ', $where_clauses);
}

// 5. Get total item count (for pagination)
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// 6. Add sorting and pagination to the main query
$sql .= " ORDER BY $sort_column $sort_dir";
$sql .= " LIMIT :limit OFFSET :offset";

// 7. Fetch the items for the current page
$stmt = $conn->prepare($sql);
$params[':limit'] = $items_per_page;
$params[':offset'] = $offset;
// Bind parameters
foreach ($params as $key => &$value) {
    if ($key == ':limit' || $key == ':offset') {
        $stmt->bindParam($key, $value, PDO::PARAM_INT);
    } else {
        $stmt->bindParam($key, $value, PDO::PARAM_STR);
    }
}
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to build query strings for links
function get_query_string($page) {
    global $search_term, $sort_column, $sort_dir;
    return "item_add.php?page=$page&sort=$sort_column&dir=$sort_dir&search=$search_term";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Edit Items - Admin Panel</title>
    <link rel="icon" type="image/png" href="./Sticker/March 7th_4.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Madimi+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
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
            padding-top: 0;
        }

        /* Hoverable Left Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 60px;
            height: 100vh;
            background: #323232;
            transition: width 0.3s ease;
            overflow-x: hidden;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar:hover {
            width: 400px;
        }

        .sidebar-header {
            padding: 20px;
            background: #fff;
            border-bottom: 2px solid #323232;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-header h2 {
            font-family: 'Rubik', sans-serif;
            font-size: 1.5rem;
            color: #313638;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s ease 0.1s;
        }

        .sidebar:hover .sidebar-header h2 {
            opacity: 1;
        }

        .sidebar-icon {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            position: absolute;
            left: 15px;
            top: 25px;
        }

        .sidebar-content {
            padding: 20px;
            opacity: 0;
            transition: opacity 0.3s ease 0.1s;
        }

        .sidebar:hover .sidebar-content {
            opacity: 1;
        }

        .sidebar-section {
            margin-bottom: 30px;
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            border: 2px solid #323232;
        }

        .sidebar-section h3 {
            font-family: 'Rubik', sans-serif;
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: #313638;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 60px;
            padding: 40px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .admin-header {
            background: #fff;
            color: #313638;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid #323232;
        }

        .admin-header h1 {
            font-family: 'Rubik', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .admin-header p {
            font-size: 1.1rem;
            color: #666;
        }

        .logout-link {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #323232;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
            font-weight: 600;
        }

        .logout-link:hover {
            background: #000;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #313638;
            font-size: 1rem;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="file"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            font-size: 1rem;
            border: 2px solid #323232;
            border-radius: 8px;
            transition: border-color 0.3s;
            font-family: 'Nunito', sans-serif;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #000;
            background: #f8f8f8;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .form-row .form-group {
                margin-bottom: 20px;
            }
        }

        .radio-group {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .radio-option input[type="radio"] {
            width: auto;
            margin: 0;
        }

        .radio-option label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
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
        }

        .btn-primary {
            background: #323232;
            color: white;
        }

        .btn-primary:hover {
            background: #000;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #fff;
            color: #f44336;
            border-color: #f44336;
        }

        .btn-danger:hover {
            background: #f44336;
            color: #fff;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #fff;
            color: #313638;
        }

        .btn-secondary:hover {
            background: #f8f8f8;
        }

        .bulk-link {
            display: inline-block;
            margin: 20px 0;
            padding: 12px 25px;
            background: #323232;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid #323232;
        }

        .bulk-link:hover {
            background: #000;
            transform: translateY(-2px);
        }

        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-form input[type="text"] {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #323232;
            border-radius: 8px;
            font-size: 1rem;
        }

        .search-form button {
            padding: 12px 25px;
            background: #323232;
            color: white;
            border: 2px solid #323232;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .search-form button:hover {
            background: #000;
        }

        .bulk-actions {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid #323232;
        }

        .bulk-actions-header {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .bulk-actions select {
            padding: 10px 15px;
            border: 2px solid #323232;
            border-radius: 8px;
            font-size: 1rem;
            min-width: 200px;
        }

        .bulk-update-field {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #323232;
        }

        .bulk-update-field.active {
            display: block;
        }

        .stock-generator-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border: 2px solid #323232;
        }

        .stock-generator-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .stock-generator-controls select {
            padding: 8px 12px;
            border: 2px solid #323232;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .stock-generator-controls button {
            padding: 8px 20px;
            background: #323232;
            color: white;
            border: 2px solid #323232;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .stock-generator-controls button:hover {
            background: #000;
        }

        .generated-stock-display {
            margin-top: 15px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            border: 2px solid #e0e0e0;
        }

        .generated-stock-display.active {
            display: block;
        }

        .generated-stock-display ul {
            list-style: none;
            padding: 0;
        }

        .generated-stock-display li {
            padding: 8px;
            border-bottom: 1px solid #e0e0e0;
        }

        .generated-stock-display li:last-child {
            border-bottom: none;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            border: 2px solid #323232;
            max-width: 1400px;
            margin: 0 auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th {
            background: #323232;
            color: white;
            padding: 15px 10px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
            border-bottom: 2px solid #000;
        }

        th.sortable a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        th.sortable a:hover {
            opacity: 0.8;
        }

        .arrow {
            font-size: 0.8rem;
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }

        tr:nth-child(even):hover {
            background-color: #f0f0f0;
        }

        table img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #323232;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-buttons input[type="submit"] {
            padding: 6px 12px;
            font-size: 0.85rem;
            border: 2px solid #323232;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .action-buttons input[type="submit"][name="update"] {
            background: #323232;
            color: white;
        }

        .action-buttons input[type="submit"][name="update"]:hover {
            background: #000;
        }

        .action-buttons input[type="submit"][name="delete"] {
            background: #fff;
            color: #f44336;
            border-color: #f44336;
        }

        .action-buttons input[type="submit"][name="delete"]:hover {
            background: #f44336;
            color: #fff;
        }

        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin-top: 30px;
            gap: 5px;
        }

        .pagination li a,
        .pagination li span {
            display: block;
            padding: 10px 15px;
            border: 2px solid #323232;
            text-decoration: none;
            color: #313638;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 600;
        }

        .pagination li.active span {
            background: #323232;
            color: white;
        }

        .pagination li.disabled span {
            color: #ccc;
            cursor: not-allowed;
            border-color: #e0e0e0;
        }

        .pagination li a:hover:not(.disabled) {
            background: #f8f9fa;
        }

        hr {
            border: none;
            border-top: 2px solid #e0e0e0;
            margin: 30px 0;
        }

        .info-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .info-box strong {
            color: #856404;
        }
    </style>
    
    <script>
        // JavaScript for bulk actions
        document.addEventListener('DOMContentLoaded', function() {
            
            // "Select All" checkbox logic
            const selectAllCheckbox = document.getElementById('select-all');
            const itemCheckboxes = document.querySelectorAll('input[name="item_ids[]"]');
            
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    itemCheckboxes.forEach(box => {
                        box.checked = selectAllCheckbox.checked;
                    });
                });
            }

            // Show/Hide conditional bulk update fields
            const bulkActionSelect = document.getElementById('bulk-action');
            const priceField = document.getElementById('bulk-price-field');
            const descriptionField = document.getElementById('bulk-description-field');
            const categoryField = document.getElementById('bulk-category-field');
            const pageField = document.getElementById('bulk-page-field');
            const stockField = document.getElementById('bulk-stock-field');
            
            // Add active class for styling
            function toggleBulkField(field, show) {
                if (field) {
                    if (show) {
                        field.style.display = 'block';
                        field.classList.add('active');
                    } else {
                        field.style.display = 'none';
                        field.classList.remove('active');
                    }
                }
            }
            
            // Price update mode elements
            const priceModeSame = document.getElementById('price-mode-same');
            const priceModeRandom = document.getElementById('price-mode-random');
            const samePriceSection = document.getElementById('same-price-section');
            const randomPricesSection = document.getElementById('random-prices-section');
            const newPriceInput = document.getElementById('new-price');
            const randomPricesJson = document.getElementById('random-prices-json');

            if (bulkActionSelect) {
                bulkActionSelect.addEventListener('change', function() {
                    toggleBulkField(priceField, this.value === 'update_price');
                    toggleBulkField(descriptionField, this.value === 'update_description');
                    toggleBulkField(categoryField, this.value === 'update_category');
                    toggleBulkField(pageField, this.value === 'update_page');
                    toggleBulkField(stockField, this.value === 'update_stock');
                });
            }

            // Price Update Mode Radio Buttons Handler
            if (priceModeSame && priceModeRandom) {
                function updatePriceModeDisplay() {
                    if (priceModeSame.checked) {
                        samePriceSection.style.display = 'block';
                        randomPricesSection.style.display = 'none';
                    } else {
                        samePriceSection.style.display = 'none';
                        randomPricesSection.style.display = 'block';
                    }
                }
                
                priceModeSame.addEventListener('change', updatePriceModeDisplay);
                priceModeRandom.addEventListener('change', updatePriceModeDisplay);
            }

            // Confirm bulk delete and validate price updates
            const bulkForm = document.getElementById('bulk-form');
            if (bulkForm) {
                bulkForm.addEventListener('submit', function(e) {
                    if (bulkActionSelect.value === 'delete') {
                        const confirmed = confirm('Are you sure you want to delete the selected items? This cannot be undone.');
                        if (!confirmed) {
                            e.preventDefault(); // Stop the form submission
                        }
                    } else if (bulkActionSelect.value === 'update_price') {
                        // Check if random mode is selected and prices are generated
                        if (priceModeRandom && priceModeRandom.checked) {
                            if (!randomPricesJson || !randomPricesJson.value) {
                                e.preventDefault();
                                alert('Please click "Generate Random Price(s)" to generate prices for selected items first!');
                                return false;
                            }
                        } else if (priceModeSame && priceModeSame.checked) {
                            // Check if price is entered for same price mode
                            if (!newPriceInput || !newPriceInput.value) {
                                e.preventDefault();
                                alert('Please enter a price or generate a random price!');
                                return false;
                            }
                        }
                    } else if (bulkActionSelect.value === 'update_description') {
                        // Check if description is entered
                        const newDescriptionInput = document.getElementById('new-description');
                        if (!newDescriptionInput || !newDescriptionInput.value.trim()) {
                            e.preventDefault();
                            alert('Please enter a description!');
                            return false;
                        }
                    } else if (bulkActionSelect.value === 'update_stock') {
                        // Check if stock is entered or random stock is generated
                        const newStockInput = document.getElementById('new-stock');
                        const randomStockJson = document.getElementById('random-stock-json');
                        
                        // Check if random stock is generated
                        if (randomStockJson && randomStockJson.value) {
                            // Random stock is generated, allow submission
                            return true;
                        }
                        
                        // Otherwise check if manual stock is entered
                        if (newStockInput && (newStockInput.value === '' || newStockInput.value < 0)) {
                            e.preventDefault();
                            alert('Please enter a valid stock value (0 or greater) or generate random stock values!');
                            return false;
                        }
                    }
                });
            }

            // Random Price Button Handler for Bulk Actions
            const bulkRandomPriceBtn = document.getElementById('bulk-random-price-btn');
            const bulkPriceRangeSelect = document.getElementById('bulk-price-range');
            const randomPricesDisplay = document.getElementById('random-prices-display');
            const randomPricesList = document.getElementById('random-prices-list');
            
            if (bulkRandomPriceBtn && bulkPriceRangeSelect) {
                bulkRandomPriceBtn.addEventListener('click', function() {
                    const range = bulkPriceRangeSelect.value;
                    let min, max;
                    
                    // Define price ranges
                    switch(range) {
                        case 'tens':
                            min = 10;
                            max = 99;
                            break;
                        case 'hundreds':
                            min = 100;
                            max = 999;
                            break;
                        case 'thousands':
                            min = 1000;
                            max = 9999;
                            break;
                        default:
                            min = 10;
                            max = 99;
                    }
                    
                    // Check which mode is selected
                    const isSameMode = priceModeSame && priceModeSame.checked;
                    
                    if (isSameMode && newPriceInput) {
                        // Same price mode: generate one price
                        const randomPrice = (Math.random() * (max - min) + min).toFixed(2);
                        newPriceInput.value = randomPrice;
                    } else {
                        // Random prices mode: generate for each checked item
                        const checkedItems = document.querySelectorAll('input[name="item_ids[]"]:checked');
                        
                        if (checkedItems.length === 0) {
                            alert('Please select at least one item first!');
                            return;
                        }
                        
                        const pricesMap = {};
                        let pricesListHtml = '<ul style="margin: 10px 0; padding-left: 20px;">';
                        
                        checkedItems.forEach(checkbox => {
                            const itemId = checkbox.value;
                            const randomPrice = (Math.random() * (max - min) + min).toFixed(2);
                            pricesMap[itemId] = randomPrice;
                            
                            // Find the item name from the table row
                            const row = checkbox.closest('tr');
                            const nameCell = row ? row.querySelector('td:nth-child(3)') : null;
                            const itemName = nameCell ? nameCell.textContent.trim() : `Item #${itemId}`;
                            
                            pricesListHtml += `<li><strong>${itemName}</strong> (ID: ${itemId}): ₱${randomPrice}</li>`;
                        });
                        
                        pricesListHtml += '</ul>';
                        
                        // Store prices as JSON
                        if (randomPricesJson) {
                            randomPricesJson.value = JSON.stringify(pricesMap);
                        }
                        
                        // Display the generated prices
                        if (randomPricesList) {
                            randomPricesList.innerHTML = pricesListHtml;
                        }
                        if (randomPricesDisplay) {
                            randomPricesDisplay.style.display = 'block';
                        }
                    }
                });
            }

            // Random Stock Generator Button Handler
            const bulkRandomStockBtn = document.getElementById('bulk-random-stock-btn');
            const bulkStockRangeSelect = document.getElementById('bulk-stock-range');
            const generatedStockDisplay = document.getElementById('generated-stock-display');
            const generatedStockList = document.getElementById('generated-stock-list');
            const randomStockJson = document.getElementById('random-stock-json');
            
            if (bulkRandomStockBtn && bulkStockRangeSelect) {
                bulkRandomStockBtn.addEventListener('click', function() {
                    const range = bulkStockRangeSelect.value;
                    let min, max;
                    
                    // Define stock ranges
                    switch(range) {
                        case 'tens':
                            min = 10;
                            max = 99;
                            break;
                        case 'hundreds':
                            min = 100;
                            max = 999;
                            break;
                        case 'thousands':
                            min = 1000;
                            max = 9999;
                            break;
                        default:
                            min = 10;
                            max = 99;
                    }
                    
                    // Get all checked items
                    const checkedItems = document.querySelectorAll('input[name="item_ids[]"]:checked');
                    
                    if (checkedItems.length === 0) {
                        alert('Please select at least one item first!');
                        return;
                    }
                    
                    const stockMap = {};
                    let stockListHtml = '';
                    
                    checkedItems.forEach(checkbox => {
                        const itemId = checkbox.value;
                        const randomStock = Math.floor(Math.random() * (max - min + 1)) + min;
                        stockMap[itemId] = randomStock;
                        
                        // Find the item name from the table row
                        const row = checkbox.closest('tr');
                        const nameCell = row ? row.querySelector('td:nth-child(3)') : null;
                        const itemName = nameCell ? nameCell.textContent.trim() : `Item #${itemId}`;
                        
                        stockListHtml += `<li><strong>${itemName}</strong> (ID: ${itemId}): ${randomStock.toLocaleString()} units</li>`;
                    });
                    
                    // Store stock values as JSON
                    if (randomStockJson) {
                        randomStockJson.value = JSON.stringify(stockMap);
                    }
                    
                    // Display the generated stock values
                    if (generatedStockList) {
                        generatedStockList.innerHTML = stockListHtml;
                    }
                    if (generatedStockDisplay) {
                        generatedStockDisplay.classList.add('active');
                    }
                    
                    // Clear the manual stock input since we're using random values
                    const newStockInput = document.getElementById('new-stock');
                    if (newStockInput) {
                        newStockInput.value = '';
                    }
                });
            }

            // ===================================================================
            // === DYNAMIC SORTING, SEARCHING, AND PAGINATION ===
            // ===================================================================
            
            // State management
            let currentState = {
                search: '<?php echo htmlspecialchars($search_term); ?>',
                sort: '<?php echo htmlspecialchars($sort_column); ?>',
                dir: '<?php echo htmlspecialchars($sort_dir); ?>',
                page: <?php echo $current_page; ?>
            };

            // Function to fetch and update table
            async function fetchTableData() {
                const tableBody = document.querySelector('#itemsTable tbody');
                const tableHeader = document.querySelector('#itemsTable thead tr');
                const paginationNav = document.querySelector('nav .pagination');
                
                if (!tableBody || !tableHeader || !paginationNav) return;

                // Show loading state
                tableBody.style.opacity = '0.6';
                
                try {
                    const url = `api-fetch-admin-items.php?search=${encodeURIComponent(currentState.search)}&sort=${currentState.sort}&dir=${currentState.dir}&page=${currentState.page}`;
                    const response = await fetch(url);
                    
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    
                    const data = await response.json();
                    
                    // Update table body
                    tableBody.innerHTML = data.table_rows_html;
                    
                    // Update table header
                    tableHeader.innerHTML = data.header_html;
                    
                    // Update pagination
                    paginationNav.innerHTML = data.pagination_html;
                    
                    // Re-attach event listeners
                    attachSortListeners();
                    attachPaginationListeners();
                    attachSelectAllListener();
                    
                } catch (error) {
                    console.error('Fetch error:', error);
                    tableBody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 20px; color: red;">Error loading items. Please try again.</td></tr>';
                } finally {
                    tableBody.style.opacity = '1';
                }
            }

            // Attach sort listeners
            function attachSortListeners() {
                const sortLinks = document.querySelectorAll('th.sortable a');
                sortLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const sort = this.getAttribute('data-sort');
                        
                        // If clicking the same column, toggle direction
                        if (currentState.sort === sort) {
                            currentState.dir = (currentState.dir === 'asc') ? 'desc' : 'asc';
                        } else {
                            // New column, start with ascending
                            currentState.sort = sort;
                            currentState.dir = 'asc';
                        }
                        
                        // Reset to page 1 when sorting
                        currentState.page = 1;
                        
                        fetchTableData();
                    });
                });
            }

            // Attach pagination listeners
            function attachPaginationListeners() {
                const paginationLinks = document.querySelectorAll('.pagination a.page-link');
                paginationLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const page = parseInt(this.getAttribute('data-page'));
                        if (page && page > 0) {
                            currentState.page = page;
                            fetchTableData();
                        }
                    });
                });
            }

            // Attach select all listener
            function attachSelectAllListener() {
                const selectAllCheckbox = document.getElementById('select-all');
                const itemCheckboxes = document.querySelectorAll('input[name="item_ids[]"]');
                
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        itemCheckboxes.forEach(box => {
                            box.checked = selectAllCheckbox.checked;
                        });
                    });
                }
            }

            // Dynamic search with debouncing
            let searchTimeout;
            const searchInput = document.querySelector('.search-form input[type="text"]');
            const searchForm = document.querySelector('.search-form');
            
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    currentState.search = searchInput.value;
                    currentState.page = 1; // Reset to page 1 when searching
                    fetchTableData();
                });
            }
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        currentState.search = this.value;
                        currentState.page = 1; // Reset to page 1 when searching
                        fetchTableData();
                    }, 500); // Wait 500ms after user stops typing
                });
            }

            // Initialize listeners on page load
            attachSortListeners();
            attachPaginationListeners();
            attachSelectAllListener();
        });

        // Random stock generator function
        function generateRandomStock() {
            const stockInputs = [
                'stock_vinyl', 'stock_holographic', 'stock_glitter', 
                'stock_paper', 'stock_diecut', 'stock_decal', 'stock_staticcling'
            ];
            
            // Generate random stock between 10-99
            stockInputs.forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.value = Math.floor(Math.random() * 90) + 10;
                }
            });
        }

        // Edit Modal Functions
        function openEditModal(id) {
            const modal = document.getElementById('edit-modal');
            
            // Fetch item details
            fetch(`api-get-item.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    // Populate form fields
                    document.getElementById('edit-id').value = data.id;
                    document.getElementById('edit-name').value = data.name;
                    document.getElementById('edit-description').value = data.description;
                    document.getElementById('edit-price').value = data.price;
                    document.getElementById('edit-category').value = data.category;
                    
                    // Handle page radio buttons
                    if (data.page) {
                        const radio = document.getElementById(`edit-page-${data.page}`);
                        if (radio) radio.checked = true;
                    }

                    // Populate stock fields
                    const paperTypes = ['Vinyl', 'Holographic', 'Glitter', 'Paper', 'Die-Cut', 'Decal', 'Static-Cling'];
                    paperTypes.forEach(type => {
                        const idSuffix = type.toLowerCase().replace(/[- ]/g, '');
                        const input = document.getElementById(`edit-stock-${idSuffix}`);
                        if (input) {
                            // Use stock from item_stock table if available, otherwise default to 0
                            input.value = (data.stocks && data.stocks[type]) ? data.stocks[type] : 0;
                        }
                    });

                    // Show current image preview if needed
                    const preview = document.getElementById('current-image-preview');
                    if (data.image) {
                        preview.innerHTML = `<img src="images/${data.image}" alt="Current Image" style="max-height: 100px;">`;
                    } else {
                        preview.innerHTML = '';
                    }

                    modal.style.display = "block";
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to fetch item details');
                });
        }

        function closeEditModal() {
            document.getElementById('edit-modal').style.display = "none";
        }

        function deleteItem(id) {
            if (confirm('Are you sure you want to delete this item?')) {
                document.getElementById('delete-id').value = id;
                document.getElementById('delete-form').submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('edit-modal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</head>

<body>
    <!-- Hoverable Left Sidebar -->
    <div class="sidebar">
        <div class="sidebar-icon">☰</div>
        <div class="sidebar-header">
            <h2>Control Panel</h2>
        </div>
        <div class="sidebar-content">
            <!-- Add New Item Section -->
            <div class="sidebar-section">
                <h3>Add New Item</h3>
                <form id="itemadd" action="item_process.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="image">Image:</label>
                        <input type="file" name="image" id="image" required>
                    </div>

                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" name="name" id="name" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea name="description" id="description" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="number" step="0.01" name="price" id="price" required>
                    </div>

                    <div class="form-group">
                        <label>Stock by Paper Type:</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                            <div>
                                <label for="stock_vinyl" style="font-size: 0.9rem; font-weight: normal;">Vinyl:</label>
                                <input type="number" name="stock_vinyl" id="stock_vinyl" min="0" value="50" style="margin-top: 5px;">
                            </div>
                            <div>
                                <label for="stock_holographic" style="font-size: 0.9rem; font-weight: normal;">Holographic:</label>
                                <input type="number" name="stock_holographic" id="stock_holographic" min="0" value="50" style="margin-top: 5px;">
                            </div>
                            <div>
                                <label for="stock_glitter" style="font-size: 0.9rem; font-weight: normal;">Glitter:</label>
                                <input type="number" name="stock_glitter" id="stock_glitter" min="0" value="50" style="margin-top: 5px;">
                            </div>
                            <div>
                                <label for="stock_paper" style="font-size: 0.9rem; font-weight: normal;">Paper:</label>
                                <input type="number" name="stock_paper" id="stock_paper" min="0" value="50" style="margin-top: 5px;">
                            </div>
                            <div>
                                <label for="stock_diecut" style="font-size: 0.9rem; font-weight: normal;">Die-Cut:</label>
                                <input type="number" name="stock_diecut" id="stock_diecut" min="0" value="50" style="margin-top: 5px;">
                            </div>
                            <div>
                                <label for="stock_decal" style="font-size: 0.9rem; font-weight: normal;">Decal:</label>
                                <input type="number" name="stock_decal" id="stock_decal" min="0" value="50" style="margin-top: 5px;">
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <label for="stock_staticcling" style="font-size: 0.9rem; font-weight: normal;">Static-Cling:</label>
                                <input type="number" name="stock_staticcling" id="stock_staticcling" min="0" value="50" style="margin-top: 5px;">
                            </div>
                        </div>
                        <button type="button" onclick="generateRandomStock()" style="margin-top: 15px; padding: 8px 15px; background: #323232; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 0.9rem;">
                            🎲 Generate Random Stock
                        </button>
                    </div>

                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select name="category" id="category" required>
                            <option value="">Select Category</option>
                            <option value="Genshin Impact">Genshin Impact</option>
                            <option value="Honkai: Star Rail">Honkai: Star Rail</option>
                            <option value="Zenless Zone Zero">Zenless Zone Zero</option>
                            <option value="Wuthering Waves">Wuthering Waves</option>
                            <option value="Punishing Gray Raven">Punishing Gray Raven</option>
                            <option value="Other">Other / Uncategorized</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Page:</label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" name="page" id="page-shop" value="shop" required>
                                <label for="page-shop">Shop</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="page" id="page-bestseller" value="bestseller">
                                <label for="page-bestseller">Best Seller</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="page" id="page-newarrivals" value="newarrivals">
                                <label for="page-newarrivals">New Arrivals</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="add" class="btn btn-primary">Add Item</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </form>
            </div>

            <!-- Bulk Upload Link -->
            <div class="sidebar-section">
                <a href="item_add_bulk.php" class="bulk-link">📤 Bulk Upload Items</a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="admin-header">
            <h1>Admin Panel - Item Management</h1>
            <p>Add, edit, and manage your sticker inventory</p>
            <a href="logout.php" class="logout-link">Log Out</a>
        </div>

        <!-- Search Form -->
        <form class="search-form">
            <input type="text" name="search" placeholder="Search items by name or description..." value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Bulk Actions Form -->
        <form id="bulk-form" method="POST" action="item_bulk_process.php">
            <div class="bulk-actions">
                <div class="bulk-actions-header">
                    <label for="bulk-action"><strong>Bulk Actions:</strong></label>
                    <select name="bulk_action" id="bulk-action">
                        <option value="">-- Select Action --</option>
                        <option value="delete">Delete Selected</option>
                        <option value="update_price">Update Price</option>
                        <option value="update_description">Update Description</option>
                        <option value="update_category">Update Category</option>
                        <option value="update_page">Update Page</option>
                        <option value="update_stock">Update Stock</option>
                        <option value="generate_stock">🎲 Generate Random Stock (All Paper Types)</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
                
                <div id="bulk-price-field" class="bulk-update-field">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 10px; font-weight: bold;">Price Update Mode:</label>
                        <div style="display: flex; gap: 20px;">
                            <label style="display: flex; align-items: center; font-weight: normal; cursor: pointer;">
                                <input type="radio" name="price_update_mode" value="same" id="price-mode-same" checked style="margin-right: 5px;">
                                Same price for all selected
                            </label>
                            <label style="display: flex; align-items: center; font-weight: normal; cursor: pointer;">
                                <input type="radio" name="price_update_mode" value="random" id="price-mode-random" style="margin-right: 5px;">
                                Random prices for each selected
                            </label>
                        </div>
                    </div>
                    
                    <div id="same-price-section">
                        <label for="new-price">New Price:</label>
                        <input type="number" step="0.01" name="new_price" id="new-price">
                    </div>
                    
                    <div id="random-prices-section" style="display: none;">
                        <div style="padding: 10px; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; margin-bottom: 10px;">
                            <strong>Random prices will be generated for each selected item when you click "Generate Random Prices"</strong>
                        </div>
                        <div id="random-prices-display" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px; background-color: #f9f9f9; display: none;">
                            <strong>Generated Prices:</strong>
                            <div id="random-prices-list"></div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 10px; padding: 10px; background-color: #e8f4f8; border-radius: 5px; border: 1px solid #b3d9e6;">
                        <label for="bulk-price-range" style="display: inline-block; margin-right: 10px; font-size: 14px;">Price Range:</label>
                        <select id="bulk-price-range" style="width: auto; display: inline-block; margin-right: 15px; font-size: 14px; padding: 5px;">
                            <option value="tens">Tens (10-99)</option>
                            <option value="hundreds">Hundreds (100-999)</option>
                            <option value="thousands">Thousands (1000-9999)</option>
                        </select>
                        <button type="button" id="bulk-random-price-btn" style="padding: 8px 15px; font-size: 14px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">
                            Generate Random Price(s)
                        </button>
                    </div>
                    
                    <!-- Hidden input to store random prices as JSON -->
                    <input type="hidden" name="random_prices_json" id="random-prices-json" value="">
                </div>
                <div id="bulk-description-field" class="bulk-update-field">
                    <label for="new-description">New Description:</label>
                    <textarea name="new_description" id="new-description" rows="4" style="width: calc(100% - 22px); padding: 10px; font-size: 16px; border: 1px solid #ccc; border-radius: 5px; font-family: Arial, sans-serif;"></textarea>
                </div>
                <div id="bulk-category-field" class="bulk-update-field">
                    <label for="new-category">New Category:</label>
                    <select name="new_category" id="new-category">
                        <option value="Genshin Impact">Genshin Impact</option>
                        <option value="Honkai: Star Rail">Honkai: Star Rail</option>
                        <option value="Zenless Zone Zero">Zenless Zone Zero</option>
                        <option value="Wuthering Waves">Wuthering Waves</option>
                        <option value="Punishing Gray Raven">Punishing Gray Raven</option>
                        <option value="Other">Other / Uncategorized</option>
                    </select>
                </div>
                <div id="bulk-page-field" class="bulk-update-field">
                    <label for="new-page">New Page:</label>
                    <select name="new_page" id="new-page">
                        <option value="shop">Shop</option>
                        <option value="bestseller">Bestseller</option>
                        <option value="newarrivals">New Arrivals</option>
                    </select>
                </div>
                <div id="bulk-stock-field" class="bulk-update-field">
                    <label for="new-stock">New Stock:</label>
                    <input type="number" name="new_stock" id="new-stock" min="0" value="0">
                    
                    <div class="stock-generator-section">
                        <strong>Random Stock Generator</strong>
                        <p style="margin: 10px 0; font-size: 0.9rem; color: #666;">Generate random stock values for selected items</p>
                        <div class="stock-generator-controls">
                            <label for="bulk-stock-range" style="font-weight: normal;">Stock Range:</label>
                            <select id="bulk-stock-range">
                                <option value="tens">Tens (10-99)</option>
                                <option value="hundreds">Hundreds (100-999)</option>
                                <option value="thousands">Thousands (1000-9999)</option>
                            </select>
                            <button type="button" id="bulk-random-stock-btn">Generate Random Stock</button>
                        </div>
                        <div class="generated-stock-display" id="generated-stock-display">
                            <strong>Generated Stock Values:</strong>
                            <ul id="generated-stock-list"></ul>
                        </div>
                        <!-- Hidden input to store random stock values as JSON -->
                        <input type="hidden" name="random_stock_json" id="random-stock-json" value="">
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table id="itemsTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th class="sortable">
                                <a href="#" data-sort="image" data-dir="<?php echo $next_dir; ?>">Image
                                    <?php if ($sort_column == 'image') echo $sort_dir == 'asc' ? '<span class="arrow">▲</span>' : '<span class="arrow">▼</span>'; ?>
                                </a>
                            </th>
                            <th class="sortable">
                                <a href="#" data-sort="name" data-dir="<?php echo $next_dir; ?>">Name
                                    <?php if ($sort_column == 'name') echo $sort_dir == 'asc' ? '<span class="arrow">▲</span>' : '<span class="arrow">▼</span>'; ?>
                                </a>
                            </th>
                            <th class="sortable">
                                <a href="#" data-sort="description" data-dir="<?php echo $next_dir; ?>">Description
                                    <?php if ($sort_column == 'description') echo $sort_dir == 'asc' ? '<span class="arrow">▲</span>' : '<span class="arrow">▼</span>'; ?>
                                </a>
                            </th>
                            <th class="sortable">
                                <a href="#" data-sort="price" data-dir="<?php echo $next_dir; ?>">Price
                                    <?php if ($sort_column == 'price') echo $sort_dir == 'asc' ? '<span class="arrow">▲</span>' : '<span class="arrow">▼</span>'; ?>
                                </a>
                            </th>
                            <th class="sortable">
                                <a href="#" data-sort="stock" data-dir="<?php echo $next_dir; ?>">Stock
                                    <?php if ($sort_column == 'stock') echo $sort_dir == 'asc' ? '<span class="arrow">▲</span>' : '<span class="arrow">▼</span>'; ?>
                                </a>
                            </th>
                            <th class="sortable">
                                <a href="#" data-sort="category" data-dir="<?php echo $next_dir; ?>">Category
                                    <?php if ($sort_column == 'category') echo $sort_dir == 'asc' ? '<span class="arrow">▲</span>' : '<span class="arrow">▼</span>'; ?>
                                </a>
                            </th>
                            <th class="sortable">
                                <a href="#" data-sort="page" data-dir="<?php echo $next_dir; ?>">Page
                                    <?php if ($sort_column == 'page') echo $sort_dir == 'asc' ? '<span class="arrow">▲</span>' : '<span class="arrow">▼</span>'; ?>
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item) : ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="item_ids[]" value="<?php echo $item['id']; ?>">
                                </td>
                                <td><img src="images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>"></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['description']); ?></td>
                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo number_format($item['stock'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td><?php echo htmlspecialchars($item['page']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="btn-edit" onclick="openEditModal(<?php echo $item['id']; ?>)" style="background-color: #4CAF50; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-right: 5px;">Update</button>
                                        <button type="button" class="btn-delete" onclick="deleteItem(<?php echo $item['id']; ?>)" style="background-color: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Edit Item Modal -->
        <div id="edit-modal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
            <div class="modal-content" style="background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 800px; border-radius: 8px;">
                <span class="close" onclick="closeEditModal()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
                <h2>Edit Item</h2>
                <form id="edit-form" action="item_processUpdate.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit-id">
                    
                    <div class="form-group">
                        <label for="edit-name">Name:</label>
                        <input type="text" name="name" id="edit-name" required style="width: 100%; padding: 8px; margin-bottom: 10px;">
                    </div>

                    <div class="form-group">
                        <label for="edit-description">Description:</label>
                        <textarea name="description" id="edit-description" required style="width: 100%; padding: 8px; margin-bottom: 10px; height: 100px;"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit-price">Price:</label>
                        <input type="number" step="0.01" name="price" id="edit-price" required style="width: 100%; padding: 8px; margin-bottom: 10px;">
                    </div>

                    <div class="form-group">
                        <label>Stock by Paper Type:</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                            <?php 
                            $paperTypes = ['Vinyl', 'Holographic', 'Glitter', 'Paper', 'Die-Cut', 'Decal', 'Static-Cling'];
                            foreach ($paperTypes as $type) {
                                $id_suffix = strtolower(str_replace(['-', ' '], '', $type));
                                echo "<div>
                                    <label for='edit-stock-$id_suffix' style='font-size: 0.9rem;'>$type:</label>
                                    <input type='number' name='stock_$id_suffix' id='edit-stock-$id_suffix' min='0' value='0' style='width: 100%; padding: 5px;'>
                                </div>";
                            }
                            ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit-category">Category:</label>
                        <select name="category" id="edit-category" required style="width: 100%; padding: 8px; margin-bottom: 10px;">
                            <option value="Genshin Impact">Genshin Impact</option>
                            <option value="Honkai: Star Rail">Honkai: Star Rail</option>
                            <option value="Zenless Zone Zero">Zenless Zone Zero</option>
                            <option value="Wuthering Waves">Wuthering Waves</option>
                            <option value="Punishing Gray Raven">Punishing Gray Raven</option>
                            <option value="Other">Other / Uncategorized</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Page:</label>
                        <div style="margin-bottom: 10px;">
                            <input type="radio" name="page" id="edit-page-shop" value="shop"> <label for="edit-page-shop">Shop</label>
                            <input type="radio" name="page" id="edit-page-bestseller" value="bestseller"> <label for="edit-page-bestseller">Bestseller</label>
                            <input type="radio" name="page" id="edit-page-newarrivals" value="newarrivals"> <label for="edit-page-newarrivals">New Arrivals</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit-image">Image (Leave empty to keep current):</label>
                        <input type="file" name="image" id="edit-image" style="margin-bottom: 10px;">
                        <div id="current-image-preview" style="margin-top: 5px;"></div>
                    </div>

                    <button type="submit" name="update" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Update Item</button>
                </form>
            </div>
        </div>

        <form id="delete-form" action="item_delete.php" method="post" style="display: none;">
            <input type="hidden" name="id" id="delete-id">
            <input type="hidden" name="delete" value="Delete">
        </form>

        <nav>
            <ul class="pagination">
                <li class="page-item <?php if ($current_page <= 1) echo 'disabled'; ?>">
                    <?php if ($current_page > 1): ?>
                        <a class="page-link" href="#" data-page="<?php echo $current_page - 1; ?>">«</a>
                    <?php else: ?>
                        <span class="page-link">«</span>
                    <?php endif; ?>
                </li>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $current_page) echo 'active'; ?>">
                        <?php if ($i == $current_page): ?>
                            <span class="page-link"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a class="page-link" href="#" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php if ($current_page >= $total_pages) echo 'disabled'; ?>">
                    <?php if ($current_page < $total_pages): ?>
                        <a class="page-link" href="#" data-page="<?php echo $current_page + 1; ?>">»</a>
                    <?php else: ?>
                        <span class="page-link">»</span>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
        </form>
    </div>

    <!-- Notification Popup -->
    <div id="notification-popup" style="position: fixed; top: 20px; right: 20px; background-color: #4CAF50; color: white; padding: 15px 25px; border-radius: 5px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); z-index: 9999; display: none; opacity: 0; transition: opacity 0.3s ease-in-out;">
        <span id="notification-message">Action Successful!</span>
    </div>

    <script>
    // Notification Function
    function showNotification(message, isError = false) {
        const popup = document.getElementById('notification-popup');
        const msgSpan = document.getElementById('notification-message');
        
        if (!popup || !msgSpan) return;

        msgSpan.textContent = message;
        popup.style.backgroundColor = isError ? '#f44336' : '#4CAF50';
        popup.style.display = 'block';
        
        // Trigger reflow
        void popup.offsetWidth;
        
        popup.style.opacity = '1';
        
        setTimeout(() => {
            popup.style.opacity = '0';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 300);
        }, 3000);
    }

    // Bulk Form AJAX
    document.addEventListener('DOMContentLoaded', function() {
        // Show success/error messages from PHP session if they exist
        <?php if (!empty($success_message)): ?>
            showNotification('<?php echo addslashes($success_message); ?>', false);
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            showNotification('<?php echo addslashes($error_message); ?>', true);
        <?php endif; ?>
        const bulkForm = document.getElementById('bulk-form');
        if (bulkForm) {
            bulkForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('item_bulk_process.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message);
                        // Reload page content after short delay to show updated data
                        setTimeout(() => {
                            // Save scroll position
                            sessionStorage.setItem('scrollPos', window.scrollY);
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification(data.message, true);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred.', true);
                });
            });
        }

        // Edit Form AJAX
        const editForm = document.getElementById('edit-form');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                // Add 'update' key because the PHP script checks for isset($_POST['update'])
                formData.append('update', 'true');
                
                fetch('item_processUpdate.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message);
                        closeEditModal();
                        // Refresh table data without reloading page
                        fetchTableData();
                    } else {
                        showNotification(data.message, true);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred.', true);
                });
            });
        }

        // Restore scroll position
        const scrollPos = sessionStorage.getItem('scrollPos');
        if (scrollPos) {
            window.scrollTo(0, parseInt(scrollPos));
            sessionStorage.removeItem('scrollPos');
        }
        
        // Check for URL msg parameter
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');
        if (msg) {
            const isError = msg.toLowerCase().includes('error') || msg.toLowerCase().includes('invalid') || msg.toLowerCase().includes('no items');
            showNotification(msg, isError);
            
            // Clean URL
            const newUrl = window.location.pathname + window.location.search.replace(/[\?&]msg=[^&]+/, '').replace(/^&/, '?');
            window.history.replaceState({}, document.title, newUrl);
        }
    });
    </script>
</body>

</html>