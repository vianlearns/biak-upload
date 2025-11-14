<?php
require_once 'config.php';

// Set unlimited execution time untuk cleanup besar
set_time_limit(0);

try {
    echo "=== Cleanup Script Started ===\n";
    echo "Time: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Jalankan cleanup dari fungsi yang sudah ada di config.php
    $deletedCount = cleanupExpiredFiles($pdo);
    
    echo "Cleanup completed successfully!\n";
    echo "Files deleted: $deletedCount\n";
    echo "Time: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "Error during cleanup: " . $e->getMessage() . "\n";
    exit(1);
}