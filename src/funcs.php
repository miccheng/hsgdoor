<?php
/**
 * Apache Env Variables:
 * ---------------------
 * SetEnv DB_HOST "localhost"
 * SetEnv DB_USER "root"
 * SetEnv DB_PASSWORD ""
 * SetEnv DB_NAME ""
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
    $url = 'http://hsgdoor-proxy.codersg.com/open.json';

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
    $mysqli = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASSWORD'), getenv('DB_NAME'));
    if (mysqli_connect_errno())
    {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }
    switch ($type)
    {
        case 'pin':
            $statement = 'SELECT `user_id` FROM `pins` WHERE `pin`=? AND `active`=1';
            break;
        case 'email':
            $statement = 'SELECT `id` FROM `users` WHERE `email`=? AND `active`=1';
            break;
    }

    if ($stmt = $mysqli->prepare($statement))
    {
        $stmt->bind_param('i', $pin);
        $stmt->execute();
        $stmt->bind_result($id);
        $stmt->fetch();
        if ($id > 0)
        {
            writeLog('Authenticated for: UID(%d) via %s', $id, $type);
            $status = true;
        }
        $stmt->close();
    }
    $mysqli->close();
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