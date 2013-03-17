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
$type = 'pin';

if (empty($pin) && !empty($_SESSION['user']))
{
    $pin = $_SESSION['user'];
    $type = 'email';
}

if (checkPin($pin, $type))
{
    $result = openDoor();
    if ($result['status'] == 'OPEN')
    {
        $response['status'] = 'okay';
        $response['msg'] = 'Door Open!';
    }
}

echo json_encode($response);