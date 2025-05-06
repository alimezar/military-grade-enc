<?php
/* index.php – production file */
include 'config.php';
include 'helper.php';

$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'n/a';

if (!cookies_present()) {
    /* -------- FIRST VISIT -------- */
    $salt    = rand_salt();
    $session = 'guest';
    $auth    = make_auth_cookie($session, $user_agent, $ENC_KEY, $salt);

    setcookie('auth_token', $auth,   0, '/', '', false, false);
    setcookie('session',    $session, 0, '/', '', false, false);
    header('Location: /');
    exit;
}

/* -------- VALIDATION PATH -------- */
$session     = $_COOKIE['session'];
$cookie_auth = $_COOKIE['auth_token'];
$salt        = substr($cookie_auth, 0, 2);               // weak: same salt
$expected    = make_auth_cookie($session, $user_agent, $ENC_KEY, $salt);

if ($cookie_auth === $expected) {
    if ($session === 'admin') {
        echo "<h1>Welcome, commander!</h1>";
        echo "<p>Phase-1 flag: <code>{$FLAG_PHASE1}</code></p>";
        echo "<p>HQ still needs the <em>encryption key</em> used in these cookies!</p>";
    } else {
        echo "<p>Logged in as <b>{$session}</b>.</p>";
        echo "<p>This cookie uses <em>traditional military-grade en<b>crypt</b>ion</em>.</p>";
        print "<!-- Who keeps backup files in prod?  DELETE any .bak ASAP -->";
    }
} else {
    echo "<h1>Invalid cookie</h1><p>Nice try.</p>";
}
?>
