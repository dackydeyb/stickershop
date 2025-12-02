<?php
session_start();
// Redirect to login page if not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Add Items - Admin Panel</title>
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
            padding: 20px;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
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

        .form-card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid #323232;
        }

        .form-card h2 {
            font-family: 'Rubik', sans-serif;
            font-size: 1.8rem;
            margin-bottom: 25px;
            color: #313638;
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
            transition: all 0.3s;
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

        .form-row > div {
            flex: 1;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }

        .checkbox-group {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #323232;
        }

        .checkbox-group label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: normal;
            cursor: pointer;
            margin: 0;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
            cursor: pointer;
        }

        /* Price Generator Section - Light Blue */
        .price-generator-section {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #90caf9;
        }

        .price-generator-section label {
            display: inline-block;
            margin-right: 10px;
            font-weight: 600;
        }

        .price-generator-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .price-generator-controls select {
            padding: 10px 15px;
            border: 2px solid #323232;
            border-radius: 8px;
            font-size: 1rem;
            min-width: 200px;
        }

        .price-generator-controls button {
            padding: 10px 25px;
            background: #323232;
            color: white;
            border: 2px solid #323232;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .price-generator-controls button:hover {
            background: #000;
        }

        /* Stock Generator Section - Light Orange */
        .stock-generator-section {
            background: #fff3e0;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #ffb74d;
        }

        .stock-generator-section .price-generator-controls button {
            background: #ff9800;
            border-color: #ff9800;
        }

        .stock-generator-section .price-generator-controls button:hover {
            background: #f57c00;
            border-color: #f57c00;
        }

        /* Image Preview Area */
        #image-preview-container {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px dashed #323232;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .preview-card {
            border: 2px solid #323232;
            border-radius: 8px;
            padding: 15px;
            background: white;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .preview-card:hover {
            border-color: #000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .preview-card img {
            width: 100%;
            height: 180px;
            object-fit: contain;
            border-radius: 8px;
            margin-bottom: 12px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
        }

        .preview-card .price-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .preview-card input[type="number"] {
            width: 100%;
            font-size: 0.95rem;
            padding: 10px;
            margin-bottom: 0;
            border: 2px solid #323232;
            border-radius: 6px;
        }

        .preview-card input[type="number"]:focus {
            border-color: #000;
            background: #f8f8f8;
            outline: none;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
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
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #323232;
            color: white;
        }

        .btn-primary:hover {
            background: #000;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #fff;
            color: #313638;
        }

        .btn-secondary:hover {
            background: #f8f8f8;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #323232;
            color: white;
        }

        .btn-success:hover {
            background: #000;
            transform: translateY(-2px);
        }

        hr {
            border: none;
            border-top: 2px solid #e0e0e0;
            margin: 30px 0;
        }

        .file-input-wrapper {
            position: relative;
            margin-bottom: 20px;
        }

        .file-input-label {
            display: inline-block;
            padding: 12px 25px;
            background: #323232;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid #323232;
        }

        .file-input-label:hover {
            background: #000;
            transform: translateY(-2px);
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-info {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #666;
        }

        .file-selected-info {
            margin-top: 10px;
            padding: 10px;
            background: #e8f5e9;
            border: 2px solid #4CAF50;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #2e7d32;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Bulk Add Items</h1>
            <p>Upload multiple items at once with ease</p>
            <a href="item_add.php" class="back-link">← Back to Single Item/Edit</a>
        </div>

        <div class="form-card">
            <h2>Item Details</h2>
            <form id="bulk-form" action="item_process_bulk_add.php" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Game Category:</label>
                        <select name="category" id="category" required>
                            <option value="">-- Select a Game --</option>
                            <option value="Genshin Impact">Genshin Impact</option>
                            <option value="Honkai: Star Rail">Honkai: Star Rail</option>
                            <option value="Zenless Zone Zero">Zenless Zone Zero</option>
                            <option value="Wuthering Waves">Wuthering Waves</option>
                            <option value="Punishing Gray Raven">Punishing Gray Raven</option>
                            <option value="Other">Other / Uncategorized</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="page">Page:</label>
                        <select name="page" id="page" required>
                            <option value="shop">Shop</option>
                            <option value="bestseller">Bestseller</option>
                            <option value="newarrivals">New Arrivals</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description (Same for all):</label>
                    <textarea name="description" id="description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="base_name">Base Name:</label>
                    <input type="text" name="base_name" id="base_name" required>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="append_name_number" id="append_name_number" value="1" checked>
                    <label for="append_name_number">Append number to name (e.g., "Name [1]", "Name [2]")</label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="same-price-check" name="same_price" value="1">
                    <label for="same-price-check">Use same price for all items</label>
                </div>
                
                <div id="base-price-field" style="display: none;">
                    <div class="form-group">
                        <label for="base_price">Base Price (for all):</label>
                        <input type="number" step="0.01" name="base_price" id="base_price">
                    </div>
                </div>

                <div class="price-generator-section">
                    <strong>Random Price Generator</strong>
                    <p style="margin: 10px 0; font-size: 0.9rem; color: #666;">Generate random prices for all items</p>
                    <div class="price-generator-controls">
                        <label for="price-range">Price Range:</label>
                        <select id="price-range">
                            <option value="tens">Tens (10-99)</option>
                            <option value="hundreds">Hundreds (100-999)</option>
                            <option value="thousands">Thousands (1000-9999)</option>
                        </select>
                        <button type="button" id="random-price-btn" class="btn btn-success">
                            Generate Random Prices
                        </button>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="same-stock-check" name="same_stock" value="1">
                    <label for="same-stock-check">Use same stock for all items</label>
                </div>
                
                <div id="base-stock-field" style="display: none;">
                    <div class="form-group">
                        <label for="base_stock">Base Stock (for all):</label>
                        <input type="number" name="base_stock" id="base_stock" min="0" value="0">
                    </div>
                </div>

                <div class="price-generator-section stock-generator-section">
                    <strong>Random Stock Generator</strong>
                    <p style="margin: 10px 0; font-size: 0.9rem; color: #666;">Generate random stock values for all items</p>
                    <div class="checkbox-group" style="margin-bottom: 15px;">
                        <input type="checkbox" id="generate-paper-stock-check" name="generate_paper_stock" value="1">
                        <label for="generate-paper-stock-check">Generate random stock for each paper type (Vinyl, Holographic, Glitter, etc.)</label>
                    </div>
                    <div class="price-generator-controls">
                        <label for="stock-range">Stock Range:</label>
                        <select id="stock-range">
                            <option value="tens">Tens (10-99)</option>
                            <option value="hundreds">Hundreds (100-999)</option>
                            <option value="thousands">Thousands (1000-9999)</option>
                        </select>
                        <button type="button" id="random-stock-btn" class="btn btn-success">
                            Generate Random Stock
                        </button>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label>Select Images:</label>
                    <div class="file-input-wrapper">
                        <label for="images" class="file-input-label">Choose Images</label>
                        <input type="file" name="images[]" id="images" multiple required accept="image/*">
                        <div class="file-input-info">You can select multiple images at once</div>
                        <div id="file-selected-info" class="file-selected-info" style="display: none;"></div>
                    </div>
                </div>

                <div id="image-preview-container"></div>
                
                <div class="form-actions">
                    <a href="item_add.php" class="btn btn-secondary">Cancel</a>
                    <input type="submit" name="add_bulk" value="Add All Items" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('images');
            const previewContainer = document.getElementById('image-preview-container');
            const samePriceCheck = document.getElementById('same-price-check');
            const basePriceField = document.getElementById('base-price-field');

            // Toggle the "Base Price" field
            const sameStockCheck = document.getElementById('same-stock-check');
            const baseStockField = document.getElementById('base-stock-field');
            
            samePriceCheck.addEventListener('change', function() {
                // If "Same Price" is checked, show base price and hide individuals
                const showBase = this.checked;
                basePriceField.style.display = showBase ? 'block' : 'none';
                
                // Toggle visibility of individual price inputs
                const individualPrices = document.querySelectorAll('.individual-price-input');
                individualPrices.forEach(input => {
                    input.style.display = showBase ? 'none' : 'block';
                });
            });

            // Toggle the "Base Stock" field
            sameStockCheck.addEventListener('change', function() {
                // If "Same Stock" is checked, show base stock and hide individuals
                const showBase = this.checked;
                baseStockField.style.display = showBase ? 'block' : 'none';
                
                // Toggle visibility of individual stock inputs
                const individualStocks = document.querySelectorAll('.individual-stock-input');
                individualStocks.forEach(input => {
                    input.style.display = showBase ? 'none' : 'block';
                });
            });

            // Random Price Button Handler
            const randomPriceBtn = document.getElementById('random-price-btn');
            const priceRangeSelect = document.getElementById('price-range');
            
            randomPriceBtn.addEventListener('click', function() {
                const range = priceRangeSelect.value;
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
                
                // Generate random price
                const randomPrice = (Math.random() * (max - min) + min).toFixed(2);
                
                if (samePriceCheck.checked) {
                    // If "same price" is checked, set the base price
                    const basePriceInput = document.getElementById('base_price');
                    if (basePriceInput) {
                        basePriceInput.value = randomPrice;
                    }
                } else {
                    // If individual prices, set random price for each input
                    const individualPriceInputs = document.querySelectorAll('.individual-price-input[type="number"]');
                    individualPriceInputs.forEach(input => {
                        // Generate a new random price for each input
                        const individualRandomPrice = (Math.random() * (max - min) + min).toFixed(2);
                        input.value = individualRandomPrice;
                    });
                }
            });

            // Random Stock Button Handler
            const randomStockBtn = document.getElementById('random-stock-btn');
            const stockRangeSelect = document.getElementById('stock-range');
            const generatePaperStockCheck = document.getElementById('generate-paper-stock-check');
            
            randomStockBtn.addEventListener('click', function() {
                const range = stockRangeSelect.value;
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
                
                // Check if generating paper type stocks
                const generatePaperStock = generatePaperStockCheck.checked;
                
                if (generatePaperStock) {
                    // Generate random stock for each paper type for each item
                    const paperTypes = ['Vinyl', 'Holographic', 'Glitter', 'Paper', 'Die-Cut', 'Decal', 'Static-Cling'];
                    const individualStockInputs = document.querySelectorAll('.individual-stock-input[type="number"]');
                    
                    individualStockInputs.forEach((input, index) => {
                        // Calculate total stock from all paper types
                        let totalStock = 0;
                        const paperStockData = {};
                        
                        paperTypes.forEach(paperType => {
                            const randomStock = Math.floor(Math.random() * (max - min + 1)) + min;
                            paperStockData[paperType] = randomStock;
                            totalStock += randomStock;
                        });
                        
                        // Store paper stock data in a hidden input
                        let paperStockInput = input.parentElement.querySelector('input[name="paper_stocks[]"]');
                        if (!paperStockInput) {
                            paperStockInput = document.createElement('input');
                            paperStockInput.type = 'hidden';
                            paperStockInput.name = 'paper_stocks[]';
                            input.parentElement.appendChild(paperStockInput);
                        }
                        paperStockInput.value = JSON.stringify(paperStockData);
                        
                        // Set total stock in the visible input
                        input.value = totalStock;
                    });
                    
                    // If same stock is checked, also set base stock
                    if (sameStockCheck.checked) {
                        const baseStockInput = document.getElementById('base_stock');
                        if (baseStockInput) {
                            let totalStock = 0;
                            const paperTypes = ['Vinyl', 'Holographic', 'Glitter', 'Paper', 'Die-Cut', 'Decal', 'Static-Cling'];
                            paperTypes.forEach(() => {
                                totalStock += Math.floor(Math.random() * (max - min + 1)) + min;
                            });
                            baseStockInput.value = totalStock;
                            
                            // Store paper stock data for base stock
                            let basePaperStockInput = document.getElementById('base_paper_stocks');
                            if (!basePaperStockInput) {
                                basePaperStockInput = document.createElement('input');
                                basePaperStockInput.type = 'hidden';
                                basePaperStockInput.id = 'base_paper_stocks';
                                basePaperStockInput.name = 'base_paper_stocks';
                                baseStockInput.parentElement.appendChild(basePaperStockInput);
                            }
                            const paperStockData = {};
                            paperTypes.forEach(paperType => {
                                paperStockData[paperType] = Math.floor(Math.random() * (max - min + 1)) + min;
                            });
                            basePaperStockInput.value = JSON.stringify(paperStockData);
                        }
                    }
                } else {
                    // Original behavior: generate total stock only
                    const randomStock = Math.floor(Math.random() * (max - min + 1)) + min;
                    
                    if (sameStockCheck.checked) {
                        // If "same stock" is checked, set the base stock
                        const baseStockInput = document.getElementById('base_stock');
                        if (baseStockInput) {
                            baseStockInput.value = randomStock;
                        }
                    } else {
                        // If individual stocks, set random stock for each input
                        const individualStockInputs = document.querySelectorAll('.individual-stock-input[type="number"]');
                        individualStockInputs.forEach(input => {
                            // Generate a new random stock for each input
                            const individualRandomStock = Math.floor(Math.random() * (max - min + 1)) + min;
                            input.value = individualRandomStock;
                        });
                    }
                }
            });

            // Handle file selection
            const fileSelectedInfo = document.getElementById('file-selected-info');
            
            imageInput.addEventListener('change', function(event) {
                // Clear old previews
                previewContainer.innerHTML = '';
                
                const files = event.target.files;
                if (files.length === 0) {
                    fileSelectedInfo.style.display = 'none';
                    return;
                }

                // Show file count
                fileSelectedInfo.textContent = `${files.length} image(s) selected`;
                fileSelectedInfo.style.display = 'block';

                const showBasePrice = samePriceCheck.checked;
                const showBaseStock = sameStockCheck.checked;

                // Loop through each selected file
                Array.from(files).forEach((file, index) => {
                    // Check if it's an image
                    if (!file.type.startsWith('image/')){ return; }

                    const reader = new FileReader();

                    reader.onload = function(e) {
                        // Create the preview card
                        const card = document.createElement('div');
                        card.className = 'preview-card';
                        
                        // Create the image preview
                        const img = document.createElement('img');
                        img.src = e.target.result;

                        // Create the individual price label
                        const priceLabel = document.createElement('label');
                        priceLabel.className = 'price-label individual-price-input';
                        priceLabel.textContent = `Price for "${file.name}"`;
                        
                        // Create the individual price input
                        // IMPORTANT: The name "prices[]" creates an array for PHP
                        const priceInput = document.createElement('input');
                        priceInput.type = 'number';
                        priceInput.step = '0.01';
                        priceInput.name = 'prices[]';
                        priceInput.placeholder = '0.00';
                        priceInput.className = 'individual-price-input';
                        priceInput.required = true;

                        // Hide/show based on the "Same Price" checkbox
                        if (showBasePrice) {
                            priceLabel.style.display = 'none';
                            priceInput.style.display = 'none';
                            priceInput.required = false; // Not required if hidden
                        }

                        // Create the individual stock label
                        const stockLabel = document.createElement('label');
                        stockLabel.className = 'price-label individual-stock-input';
                        stockLabel.textContent = `Stock for "${file.name}"`;
                        stockLabel.style.marginTop = '10px';
                        
                        // Create the individual stock input
                        // IMPORTANT: The name "stocks[]" creates an array for PHP
                        const stockInput = document.createElement('input');
                        stockInput.type = 'number';
                        stockInput.min = '0';
                        stockInput.name = 'stocks[]';
                        stockInput.placeholder = '0';
                        stockInput.className = 'individual-stock-input';
                        stockInput.value = '0';
                        stockInput.required = true;

                        // Hide/show based on the "Same Stock" checkbox
                        if (showBaseStock) {
                            stockLabel.style.display = 'none';
                            stockInput.style.display = 'none';
                            stockInput.required = false; // Not required if hidden
                        }
                        
                        // Add elements to card
                        card.appendChild(img);
                        card.appendChild(priceLabel);
                        card.appendChild(priceInput);
                        card.appendChild(stockLabel);
                        card.appendChild(stockInput);
                        
                        // Add card to container
                        previewContainer.appendChild(card);
                    }
                    
                    // Read the file as a Data URL to trigger the 'onload' event
                    reader.readAsDataURL(file);
                });
            });
        });
    </script>
</body>
</html>