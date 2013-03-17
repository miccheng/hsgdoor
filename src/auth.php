<?php
require_once('funcs.php');
header('Content-Type: application/json');

$response = PersonaVerify();

$result = json_decode($response, true);
if ('okay' == $result['status']) {
    $email = $result['email'];
    writeLog('Logged in as: %s, Response: %s', $email, $response);
    $_SESSION['user'] = $email;
}

echo $response;