<?php
// Flash message helper - supports multiple messages per session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Push a flash message to the session.
 * @param string $message
 * @param string $type one of: danger, success, info, warning
 */
function flash_set(string $message, string $type = 'danger'): void {
    if (!in_array($type, ['danger','success','info','warning'], true)) {
        $type = 'info';
    }
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

/**
 * Retrieve and clear all flash messages.
 * @return array list of ['message'=>string,'type'=>string]
 */
function flash_get(): array {
    if (empty($_SESSION['flash']) || !is_array($_SESSION['flash'])) return [];
    $out = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $out;
}
