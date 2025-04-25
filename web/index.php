<?php
/*  military-grade-enc © 2025
    This SSO cookie is protected with TRADITIONAL military-grade en|crypt|ion.
    Absolutely unbreakable.  Trust us.
*/
include 'config.php';

// ---------------------------------------------
// Helpers
// ---------------------------------------------
function rand_salt(int $n = 2): string {
    $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $s = '';
    for ($i = 0; $i < $n; $i++) {
        $s .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $s;
}

function block_hash(string $text, string $salt): string {
    // classic DES-based crypt → 13-char output, first two chars are the salt
    return crypt($text, $salt);
}

function make_auth_cookie(string $session, string $client_ip, string $key, string $salt): string {
    // string we will split:  session|ip|key
    $payload = "{$session}|{$client_ip}|{$key}";
    $token = '';
    foreach (str_split($payload, 8) as $chunk) {
        $token .= block_hash($chunk, $salt);
    }
    return $token;
}

function cookies_exist(): bool {
    return isset($_COOKIE['auth_token']) && isset($_COOKIE['session']);
}

function get_client_ip(): string {
    // We *trust* X-Real-IP (oops)
    return $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
}

// ---------------------------------------------
// Main logic
// ---------------------------------------------
if (!cookies_exist()) {

    // -------- initial visit ---------
    $salt = rand_salt();                      // 2 bytes
    $client_ip = get_client_ip();
    $session   = 'guest';                     // hard-coded first role

    $auth = make_auth_cookie($session, $client_ip, $ENC_KEY, $salt);

    setcookie('auth_token', $auth,   time()+3600, '/', '', false, false);
    setcookie('session',    $session, time()+3600, '/', '', false, false);

    header('Location: /');                    // refresh with cookies
    exit;

} else {

    // -------- verification path -----
    $cookie_auth = $_COOKIE['auth_token'];
    $session     = $_COOKIE['session'];
    $salt        = substr($cookie_auth, 0, 2);           // salt reused for *all* chunks
    $client_ip   = get_client_ip();

    $expected = make_auth_cookie($session, $client_ip, $ENC_KEY, $salt);

    if ($cookie_auth === $expected) {
        if ($session === 'admin') {
            echo "<h1>Welcome, commander!</h1>";
            echo "<p>Here's your first flag: <code>{$FLAG_PHASE1}</code></p>";
            echo "<p>But we need the **encryption key** for the next operation.</p>";
        } else {
            $masked = str_repeat('*', strlen($client_ip));
            echo "<p>You are logged in as <strong>{$session}</strong> from {$masked}</p>";
            echo "<p>Cookie is protected with <em>traditional military-grade en<b>crypt</b>ion</em>.</p>";
        }
    } else {
        echo "<h1>Invalid cookie</h1><p>Nice try.</p>";
    }
}
?>
