<?php
include "config.php";

function generate_cookie($user, $ENC_SECRET_KEY) {
    $SALT = generatesalt(2);
    $secure_cookie_string = $user . ":" . $_SERVER["HTTP_USER_AGENT"] . ":" . $ENC_SECRET_KEY;
    $secure_cookie = make_secure_cookie($secure_cookie_string, $SALT);
    setcookie("secure_cookie", $secure_cookie, time() + 3600, "/", "", false);
    setcookie("user", "$user", time() + 3600, "/", "", false);
}

function cryptstring($what, $SALT) {
    return crypt($what, $SALT);
}

function make_secure_cookie($text, $SALT) {
    $secure_cookie = "";
    foreach (str_split($text, 8) as $el) {
        $secure_cookie .= cryptstring($el, $SALT);
    }
    return $secure_cookie;
}

function generatesalt($n) {
    $randomString = "";
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return $randomString;
}

function verify_cookie($ENC_SECRET_KEY) {
    $crypted_cookie = $_COOKIE["secure_cookie"];
    $user = $_COOKIE["user"];
    $string = $user . ":" . $_SERVER["HTTP_USER_AGENT"] . ":" . $ENC_SECRET_KEY;
    $salt = substr($crypted_cookie, 0, 2);
    if (make_secure_cookie($string, $salt) === $crypted_cookie) {
        return true;
    } else {
        return false;
    }
}

if (isset($_COOKIE["secure_cookie"]) && isset($_COOKIE["user"])) {
    $user = $_COOKIE["user"];
    if (verify_cookie($ENC_SECRET_KEY)) {
        if ($user === "admin") {
            echo "congrats: " . file_get_contents("/flag1.txt") . ". But can you get the key?";
        } else {
            $length = strlen($_SERVER["HTTP_USER_AGENT"]);
            print "<p>Hello! You are currently logged in as " . $user;
            print "<p>Please remember that this cookie is protected with state-of-the-art military grade encryption and cannot be decrypted!!";
            print "<!-- DEV Ali: which one of you idiots left the .bak files?? Remove them Immediately -->";
        }
    } else {
        print "<p>You are not logged in";
    }
} else {
    generate_cookie("guest", $ENC_SECRET_KEY);
    header("Location: /");
}
?>
