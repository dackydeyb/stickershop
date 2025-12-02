<?php
session_start();
ob_start(); // Start output buffering to prevent any accidental output
include 'connection.php';

if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = $_POST['id'];

    try {
        // Fetch the image file name
        $sql = "SELECT image FROM items WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            $image = $item['image'];
            $imagePath = 'images/' . $image;

            // Delete the image file from the images folder
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            // Delete stock records for this item first (foreign key constraint)
            $deleteStockStmt = $conn->prepare("DELETE FROM item_stock WHERE item_id = :id");
            $deleteStockStmt->bindParam(':id', $id);
            $deleteStockStmt->execute();

            // Delete the record from the database
            $deleteStmt = $conn->prepare("DELETE FROM items WHERE id = :id");
            $deleteStmt->bindParam(':id', $id);

            if ($deleteStmt->execute()) {
                ob_clean(); // Clear any output
                $_SESSION['success_message'] = "Item deleted successfully!";
                header("Location: item_add.php");
                exit();
            } else {
                ob_clean();
                $_SESSION['error_message'] = "Error deleting item from database.";
                header("Location: item_add.php");
                exit();
            }
        } else {
            ob_clean();
            $_SESSION['error_message'] = "Item not found.";
            header("Location: item_add.php");
            exit();
        }
    } catch (PDOException $e) {
        ob_clean();
        $_SESSION['error_message'] = "Error deleting item: " . $e->getMessage();
        header("Location: item_add.php");
        exit();
    }
} else {
    ob_clean();
    $_SESSION['error_message'] = "Invalid delete request.";
    header("Location: item_add.php");
    exit();
}
ob_end_flush();
?>