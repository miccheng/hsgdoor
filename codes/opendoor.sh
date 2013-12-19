#!/bin/bash
hash=$1
port=$(curl -s http://pi.dabase.com/mcheng/ | tail -n1 | awk '{print $3}')
if test "$port"; then
    ssh pi@pi.dabase.com -p $port -o "StrictHostKeyChecking no" -o UserKnownHostsFile=/dev/null -af "curl -s -H 'Authorization: Basic $hash' http://door-arduino.hackerspace.sg/open.json"
else
    echo '{"status":"FAIL"}'
fi