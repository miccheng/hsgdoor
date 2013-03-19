<?php
require_once('funcs.php');

$user = isVisitorAuth();
if ($user)
{
    writeLog('Logging out of: %s', json_encode($user));
    $cookie_var = $_COOKIE['hsgdoor_auth'];
    $path = __DIR__ . '/codes/auth/' . $cookie_var;
    if ( is_file($path) )
    {
        unlink($path);
    }
}
setCookie('hsgdoor_auth', '', time() - 86400);

session_destroy();