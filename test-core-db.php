<?php
require_once __DIR__ . '/app/core/Database.php';

try {
    $db = Database::getConnection();
    echo "Database connection successful!";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
