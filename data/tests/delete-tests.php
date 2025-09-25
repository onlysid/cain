<?php
/**
 * delete_3_0x.php
 * Deletes all results where master_results.version = '3.0x' (and their child rows in results).
 * Run: php delete_3_0x.php
 */

ini_set('memory_limit', '512M');
set_time_limit(0);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';

try {
    // Pre-counts
    $masters = (int)$cainDB->selectAll("SELECT COUNT(*) FROM master_results WHERE version = '3.0x'");
    $children = (int)$cainDB->selectAll("
        SELECT COUNT(*)
        FROM results res
        JOIN master_results r ON r.id = res.master_result
        WHERE r.version = '3.9999999'
    ");

    echo "About to delete: masters={$masters}, children={$children}\n";

    if ($masters === 0 && $children === 0) {
        echo "Nothing to delete.\n";
        exit(0);
    }

    $cainDB->beginTransaction();

    // Delete children first
    $stmt1 = $cainDB->query("
        DELETE res
        FROM results res
        JOIN master_results r ON r.id = res.master_result
        WHERE r.version = '3.9999999'
    ");

    // Delete masters
    $stmt2 = $cainDB->query("DELETE FROM master_results WHERE version = '3.9999999'");

    $cainDB->commit();

    echo "Deleted tests!\n";

} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
