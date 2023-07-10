#!/bin/bash

trap 'kill -- $(jobs -p); exit 0' SIGINT SIGTERM EXIT

socat TCP-LISTEN:1337,fork,reuseaddr,bind=0.0.0.0,su=ctf \
    EXEC:"timeout 60 /service/mail /service/mailbox $OWNER_EMAIL",stderr &

while :
do
    find /service/mailbox -type f -name 'tmp.*' -mmin +1 -delete
    sleep 10 &
    wait $!
done
