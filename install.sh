#!/bin/sh

cp bin/* /usr/local/bin/

# debian base system
# init script telepitese:
cp etc/init.d/syslog-sql /etc/init.d/
cd /etc/init.d/
update-rc.d syslog-sql defaults
cd -

