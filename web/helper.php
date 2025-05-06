<?php
/* helper.php – tiny crypto helpers for our totally “military-grade” SSO */
function rand_salt(int $n = 2): string {
    return substr(str_shuffle(str_repeat(
        '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 4)), 0, $n);
}

function block_hash(string $text, string $salt): string {
    // classic DES crypt – 13 chars, first 2 == salt
    return crypt($text, $salt);
}

function make_auth_cookie(string $session,
                          string $user_agent,
                          string $key,
                          string $salt): string {
    // PAYLOAD:  session::UA::KEY
    $payload = "{$session}::{$user_agent}::{$key}";
    $token   = '';
    foreach (str_split($payload, 8) as $chunk) {
        $token .= block_hash($chunk, $salt);      // salt reused across chunks
    }
    return $token;
}

function cookies_present(): bool {
    return isset($_COOKIE['auth_token'], $_COOKIE['session']);
}
?>
