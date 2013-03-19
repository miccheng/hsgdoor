<?php
require_once('funcs.php');

header('Content-Type: application/json');
$response = array(
      'status' => 'error'
    , 'msg'    => 'Invalid Pin. Please try again.'
);

$pin = filter_input(
    INPUT_POST,
    'pin',
    FILTER_UNSAFE_RAW,
    FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH
);
$type = filter_input(
    INPUT_POST,
    'type',
    FILTER_UNSAFE_RAW,
    FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH
);

if ($type == 'personas')
{
    $raw_reponse = PersonaVerify();
    $result = json_decode($raw_reponse, true);

    if ($result['status'] != 'okay') goto PUSH_RESPONSE;

    $pin = $result['email'];
    $type = 'email';
    writeLog('Personans Logged in as: %s, Response: %s', $email, $raw_reponse);
}

if (checkPin($pin, $type))
{
    $response['status'] = 'okay';
    $response['msg'] = 'Authorized';

    $result = openDoor();
    if ($result['status'] == 'OPEN')
    {
        $response['msg'] = 'Door Open!';
    }
    else
    {
        $response['status'] = 'error';
        $response['msg'] = 'Sorry, door would not open.';
    }
}

PUSH_RESPONSE:
echo json_encode($response);