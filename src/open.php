<?php
require_once('funcs.php');

header('Content-Type: application/json');
$response = array(
      'status' => 'error'
    , 'msg'    => 'Invalid Pin. Please try again.'
);

$user = isVisitorAuth();
if ($user)
{
    $result = openDoor();
    if ($result['status'] == 'OPEN')
    {
        $response['status'] = 'okay';
        $response['msg'] = 'Door Open!';
    }
    else
    {
        $response['status'] = 'error';
        $response['msg'] = 'Sorry, door would not open.';
    }
}

echo json_encode($response);