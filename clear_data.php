<?php
require_once 'config/db.php';

try {
    // Disable foreign key checks to allow truncation/deletion of related tables
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // Tables to truncate (completely clear)
    $tablesToTruncate = [
        'vehicles',
        'owners',
        'tax_types',
        'payments',
        'payments_log',
        'taxes',
        'taxes_log',
        'vehicles_owner'
    ];

    foreach ($tablesToTruncate as $table) {
        $pdo->exec("TRUNCATE TABLE `$table`;");
        echo "Table `$table` cleared.\n";
    }

    // Clear users except the 'admin' or 'Admin' (role_id = 1)
    // Based on inspection, role_id 1 is Admin.
    $pdo->exec("DELETE FROM users WHERE role_id != 1;");
    echo "Users table cleared (except Admin).\n";

    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "Data deletion complete. Districts, Roles, and Admin user preserved.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
