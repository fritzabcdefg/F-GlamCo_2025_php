<?php
// Unified alert renderer - prefers flash helper, falls back to legacy session keys for compatibility
require_once __DIR__ . '/flash.php';

// If old-style keys exist, convert them into flash entries (backwards compatibility)
if (isset($_SESSION['message'])) {
    flash_set((string)$_SESSION['message'], 'danger');
    unset($_SESSION['message']);
}
if (isset($_SESSION['success'])) {
    flash_set((string)$_SESSION['success'], 'success');
    unset($_SESSION['success']);
}

$flashes = flash_get();
foreach ($flashes as $f) {
    $msg = htmlspecialchars($f['message'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $type = $f['type'] ?? 'info';
    // map our types to bootstrap classes
    $class = 'alert-info';
    switch ($type) {
        case 'danger': $class = 'alert-danger'; break;
        case 'success': $class = 'alert-success'; break;
        case 'warning': $class = 'alert-warning'; break;
        case 'info': default: $class = 'alert-info'; break;
    }
    echo "<div class='alert {$class} alert-dismissible fade show' role='alert'>\n";
    echo "  <div>{$msg}</div>\n";
    echo "  <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>\n";
    echo "</div>\n";
}
?>