#!/bin/bash
chmod 775 /home/email/conf/app || return 1
chmod 775 /home/email/conf/smtp.config.ini || return 1
if [  $(pgrep -f /home/email/src/CURRENT/scripts/keepalive.sh | wc -l) -ne 0 ]
then
    pgrep -f /home/email/src/CURRENT/scripts/keepalive.sh | xargs kill -9 2>/dev/null || exit 1
fi
nohup /bin/sh /home/email/src/CURRENT/scripts/keepalive.sh  > /home/email/log/keepalive 2>&1 & 

