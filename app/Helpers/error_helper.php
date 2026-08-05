<?php
function logError(string $message, array $context = []): void {
    $log = date('Y-m-d H:i:s') . " - " . $message;
    if (!empty($context)) {
        $log .= " - " . json_encode($context);
    }
    $logFile = dirname(APPROOT) . '/logs/error.log';
    error_log($log . PHP_EOL, 3, $logFile);
}

function handleException(Throwable $exception): void {
    logError($exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    if ((defined('APP_ENV') ? APP_ENV : 'production') === 'development') {
        die($exception->getMessage());
    } else {
        header('location: ' . URLROOT . '/pages/error');
        exit();
    }
}