<?php
include 'connection.php';

if (isset($_POST['update'])) {
    $id = $_POST['id'];

    // Fetch item details
    $sql = "SELECT * FROM items WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        // Display update form with existing item details
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Update Item</title>
            <style>
                .container {
                    width: 50%;
                    margin: auto;
                    font-family: Arial, sans-serif;
                }
                form {
                    border: 2px solid #000;
                    border-radius: 10px;
                    padding: 20px;
                    background-color: #f9f9f9;
                }
                form label {
                    font-size: 18px;
                    display: block;
                    margin-bottom: 10px;
                }
                /* --- ADDED select --- */
                form input[type="text"], 
                form input[type="number"], 
                form input[type="file"], 
                form textarea,
                form select {
                    width: calc(100% - 22px);
                    padding: 10px;
                    margin-bottom: 20px;
                    font-size: 16px;
                    border: 1px solid #ccc;
                    border-radius: 5px;
                }
                form textarea {
                    height: 100px;
                }
                .buttons {
                    display: flex;
                    justify-content: space-between;
                }
                .buttons input {
                    padding: 10px 20px;
                    font-size: 18px;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                }
                .buttons input[type="submit"] {
                    background-color: #4CAF50;
                    color: white;
                }
                .buttons input[type="reset"] {
                    background-color: #f44336;
                    color: white;
                }
                .buttons input[type="button"] {
                    background-color: #008CBA;
                    color: white;
                }
                .radio-group {
                    display: flex;
                    justify-content: space-around;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
        <div class="container">
            <form action="item_processUpdate.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">

                <label for="image">Image:</label>
                <input type="file" name="image" id="image"><br>

                <label for="name">Name:</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($item['name']); ?>" required><br>

                <label for="description">Description:</label>
                <textarea name="description" id="description" required><?php echo htmlspecialchars($item['description']); ?></textarea><br>

                <label for="price">Price:</label>
                <input type="number" step="0.01" name="price" id="price" value="<?php echo htmlspecialchars($item['price']); ?>" required><br>

                <label for="stock">Stock:</label>
                <input type="number" name="stock" id="stock" min="0" value="<?php echo htmlspecialchars($item['stock'] ?? 0); ?>" required><br>

                <label for="category">Game Category:</label>
                <select name="category" id="category" required>
                    <option value="">-- Select a Game --</option>
                    <option value="Genshin Impact" <?php echo $item['category'] == 'Genshin Impact' ? 'selected' : ''; ?>>Genshin Impact</option>
                    <option value="Honkai: Star Rail" <?php echo $item['category'] == 'Honkai: Star Rail' ? 'selected' : ''; ?>>Honkai: Star Rail</option>
                    <option value="Zenless Zone Zero" <?php echo $item['category'] == 'Zenless Zone Zero' ? 'selected' : ''; ?>>Zenless Zone Zero</option>
                    <option value="Wuthering Waves" <?php echo $item['category'] == 'Wuthering Waves' ? 'selected' : ''; ?>>Wuthering Waves</option>
                    <option value="Punishing Gray Raven" <?php echo $item['category'] == 'Punishing Gray Raven' ? 'selected' : ''; ?>>Punishing Gray Raven</option>
                    <option value="Other" <?php echo $item['category'] == 'Other' ? 'selected' : ''; ?>>Other / Uncategorized</option>
                </select><br>
                <label for="page">Add to page:</label><br>
                <div class="radio-group">
                    <input type="radio" name="page" value="shop" id="shop" <?php echo $item['page'] == 'shop' ? 'checked' : ''; ?>>
                    <label for="shop">Shop</label><br>
                    <input type="radio" name="page" value="bestseller" id="bestseller" <?php echo $item['page'] == 'bestseller' ? 'checked' : ''; ?>>
                    <label for="bestseller">Bestseller</label><br>
                    <input type="radio" name="page" value="newarrivals" id="newarrivals" <?php echo $item['page'] == 'newarrivals' ? 'checked' : ''; ?>>
                    <label for="newarrivals">New Arrivals</label><br>
                </div>

                <div class="buttons">
                    <input type="button" value="Back" onclick="window.location.href='item_add.php'">
                    <input type="reset" value="Clear">
                    <input type="submit" name="update" value="Update Item">
                </div>
            </form>
        </div>
        </body>
        </html>
        <?php
    } else {
        echo "Item not found.";
    }
}
?>