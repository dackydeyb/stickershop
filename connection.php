<?php 

// Database connection
define("DSN", "mysql:host=localhost;dbname=stickershopdb");
define("USERNAME", "root");
define("PASSWORD", "");

try {
    $conn = new PDO(DSN, USERNAME, PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "<br>");
}

?>