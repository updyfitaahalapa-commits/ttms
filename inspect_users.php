<?php
require_once 'config/db.php';

try {
    echo "Users:\n";
    $stmt = $pdo->query("SELECT * FROM users");
    while ($row = $stmt->fetch()) {
        print_r($row);
    }

    echo "\nRoles:\n";
    $stmt = $pdo->query("SELECT * FROM roles");
    while ($row = $stmt->fetch()) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
