<?php
// Unified alert renderer - uses legacy session keys only
session_start();

$flashes = [];

// Collect old-style keys
if (isset($_SESSION['message'])) {
    $flashes[] = ['message' => (string)$_SESSION['message'], 'type' => 'danger'];
    unset($_SESSION['message']);
}
if (isset($_SESSION['success'])) {
    $flashes[] = ['message' => (string)$_SESSION['success'], 'type' => 'success'];
    unset($_SESSION['success']);
}

// Render alerts
foreach ($flashes as $f) {
    $msg  = htmlspecialchars($f['message'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $type = $f['type'] ?? 'info';

    // Map types to Bootstrap classes
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
