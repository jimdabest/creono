<?php
function setFlash(string $key, string $message, string $type = 'success'): void {
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlash(string $key): ?array {
    if (isset($_SESSION['flash'][$key])) {
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $flash;
    }
    return null;
}

function displayFlash(string $key): void {
    $flash = getFlash($key);
    if ($flash) {
        $class = $flash['type'] === 'success' ? 'alert-success' : 'alert-danger';
        echo "<div class='alert {$class}'>{$flash['message']}</div>";
    }
}