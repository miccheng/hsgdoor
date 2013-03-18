<?php
/**
 * Apache Env Variables:
 * ---------------------
 * SetEnv ACL_PINS "path/to/pins/file/"
 * SetEnv ACL_EMAILS "path/to/emails/file/"
 * SetEnv ENTRY_LOG "path/to/log/file/"
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
        CURLOPT_RETURNTRANSFER => true
    );

    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function checkPin($pin=null, $type='pin')
{
    $status = false;
    $file = null;
    switch ($type)
    {
        case 'pin':
            $file = getenv('ACL_PINS');
            break;
        case 'email':
            $file = getenv('ACL_EMAILS');
            break;
    }
    if (is_file($file))
    {
        $allowed_users = json_decode(file_get_contents($file), true);
    }

    if (isset($allowed_users[$pin]))
    {
        $status = true;
        $user = $allowed_users[$pin];
        writeLog('Authenticated for: UID(%s) via %s', json_encode($user), $type);
    }
    return $status;
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
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "on") {
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