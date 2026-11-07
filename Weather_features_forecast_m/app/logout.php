<?php
require_once __DIR__ . '/common/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken()) {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookieParameters = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $cookieParameters['path'],
            $cookieParameters['domain'],
            $cookieParameters['secure'],
            $cookieParameters['httponly']
        );
    }

    session_destroy();
    header('Location: home.php');
    exit;
}

http_response_code($_SERVER['REQUEST_METHOD'] === 'POST' ? 403 : 405);
header('Location: home.php');
exit;