<?php
require_once('config.inc');

$records = array();
$fp = fopen($file, 'r');
while($row = fgetcsv($fp))
{
    $active = $row[6];
    if ($active == 0) continue;
    if (empty($row[3])) continue;
    $record = array
    (
          'id'    => $row[3]
        , 'name'  => $row[1]
        , 'email' => $row[0]
        , 'pin'   => str_pad($row[2], 6, "0", STR_PAD_LEFT)
    );
    $records[] = $record;
}
fclose($fp);

$pins = array();
$emails = array();
foreach($records as $record)
{
    $email = md5(trim($record['email']) . $md5_salt);
    $pin = md5($record['pin'] . $md5_salt);
    $read_only_record = array('id'=>$record['id'], 'name'=>$record['name']);

    if( !empty($email) )
    {
        $emails[$email] = $read_only_record;
    }
    if( !empty($pin) )
    {
        $pins[$pin] = $read_only_record;
    }
}
file_put_contents('pins.json', json_encode($pins));
file_put_contents('emails.json', json_encode($emails));

printf('Found %d records. Written %d to PIN, Written %d to EMAILS.', count($records), count($pins), count($emails));
echo "\n";