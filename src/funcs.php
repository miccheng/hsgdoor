<?php
/**
 * Apache Env Variables:
 * ---------------------
 * SetEnv ENTRY_LOG "path/to/log/file/"
 * SetEnv DOOR_USER ""
 * SetEnv DOOR_PASS ""
 * SetEnv PASS_SALT ""
 * SetEnv AUTH_COOKIE_SALT ""
 */
session_start();

function writeLog($msg)
{
    $_msg = $msg;
    if (func_num_args() > 1)
    {
        $args = func_get_args();
        array_shift($args);
        $_msg = vsprintf($msg, $args);
    }
    $ts = '[' . date('r') . ']:';
    error_log($ts . $_msg . "\n", 3, getenv('ENTRY_LOG'));
}

function openDoor()
{
    $url = 'http://door-arduino.hackerspace.sg/open.json';

    $ch = curl_init();
    $options = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => sprintf('%s:%s',  getenv('DOOR_USER'), getenv('DOOR_PASS'))
    );

    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function saveAuthCookie($user=array())
{
    if (empty($user)) return;
    $user['date'] = date('Y-m-d H:i:s');
    $cookie_var = md5(time() . json_encode($user) . getenv('AUTH_COOKIE_SALT'));
    file_put_contents(dirname(__DIR__) . '/codes/auth/' . $cookie_var, json_encode($user));
    setCookie('hsgdoor_auth', $cookie_var, time() + 1576800000); // 50 years
}

function isVisitorAuth()
{
    if (!empty($_COOKIE['hsgdoor_auth']))
    {
        $cookie_var = $_COOKIE['hsgdoor_auth'];
        $path = dirname(__DIR__) . '/codes/auth/' . $cookie_var;
        if ( is_file($path) )
        {
            $user = json_decode(file_get_contents($path), true);
            return $user;
        }
        else
        {
            setCookie('hsgdoor_auth', '', time() - 86400);
        }
    }
    return false;
}

function checkPin($pin=null, $type='pin')
{
    $file = null;
    switch ($type)
    {
        case 'pin':
            $file = dirname(__DIR__) . '/codes/pins.json';
            break;
        case 'email':
            $file = dirname(__DIR__) . '/codes/emails.json';
            break;
    }
    if (is_file($file))
    {
        $allowed_users = json_decode(file_get_contents($file), true);
    }

    $pin = md5($pin . getenv('PASS_SALT'));
    if (isset($allowed_users[$pin]))
    {
        $user = $allowed_users[$pin];
        writeLog('Authenticated for: UID(%s) via %s', json_encode($user), $type);
        saveAuthCookie($user);
        return $user;
    }
    return false;
}

function PersonaVerify()
{
    $url = 'https://verifier.login.persona.org/verify';

    $assert = filter_input(
        INPUT_POST,
        'assertion',
        FILTER_UNSAFE_RAW,
        FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH
    );

    $scheme = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "on")
    {
        $scheme = 'https';
    }
    $audience = sprintf(
        '%s://%s:%s',
        $scheme,
        $_SERVER['SERVER_NAME'],
        $_SERVER['SERVER_PORT']
    );

    $params = 'assertion=' . urlencode($assert) . '&audience='
        . urlencode($audience);

    $ch = curl_init();
    $options = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => 2,
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_SSL_VERIFYPEER => true
    );

    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}