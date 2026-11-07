<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(): bool
{
    $submittedToken = $_POST['csrf_token'] ?? '';

    return is_string($submittedToken)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $submittedToken);
}

function getDatabaseConnection(): PDO
{
    static $connection;

    if (!$connection instanceof PDO) {
        $connection = require __DIR__ . '/DBconnect.php';
    }

    return $connection;
}

function requireLogin(): void
{
    if (!isset($_SESSION['user']['address'])) {
        header('Location: login.php');
        exit;
    }
}

function setFlashMessage(string $message, string $type = 'success'): void
{
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type,
    ];
}

function consumeFlashMessage(): ?array
{
    $message = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);

    return is_array($message) ? $message : null;
}
