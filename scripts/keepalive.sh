#!/bin/bash
while true
do
    status_num=$( ps auxw | grep -v 'grep' | grep -wc  "/home/email/src/CURRENT/rabbitmq/Dequeue.php" )
    if [ ${status_num} -eq 0 ]
    then
        nohup php /home/email/src/CURRENT/rabbitmq/Dequeue.php > /home/email/log/Dequeue 2>&1 &
        echo "start restart php Dequeue.php  $(date)" >>/home/email/log/Restart
    fi
    sleep 5
done
