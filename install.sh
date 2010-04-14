#!/bin/sh

cp bin/* /usr/local/bin/

#web telepitese
mkdir /usr/lib/cgi-bin/wlm
cp web/* /usr/lib/cgi-bin/wlm

# init script telepitese:
cp etc/init.d/syslog-sql /etc/init.d/
cd /etc/init.d/
update-rc.d syslog-sql defaults
cd -

