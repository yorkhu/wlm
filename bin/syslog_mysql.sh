#!/bin/bash
  
if [ ! -e /var/log/mysql.pipe ]; then
	mkfifo /var/log/mysql.pipe
fi
while [ -e /var/log/mysql.pipe ]
do
	mysql -u syslogadmin -psyslogadmin syslog < /var/log/mysql.pipe
done
