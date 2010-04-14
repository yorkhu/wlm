#! /usr/bin/env python
#

import sys, adodb
from time import localtime, strftime, time

#Konfiguracio:
cfg = {}
cfg['sql'] = {}
cfg['sql']['type'] = 'mysql'
cfg['sql']['server'] = 'localhost'
cfg['sql']['user'] = 'syslog'
cfg['sql']['passwd'] = 'syslog'
cfg['sql']['db'] = 'syslog'

# kapcsolodas
def connect():
 conn = adodb.NewADOConnection(cfg['sql']['type'])
 conn.Connect(cfg['sql']['server'], cfg['sql']['user'], cfg['sql']['passwd'], cfg['sql']['db'])
 return conn

conn = connect()

day = strftime("%d", localtime())
hour = strftime("%H", localtime())
minute = int(strftime("%M", localtime()))

if day == '01' and hour == '00' and (minute >= 0 and minute < 5):
	if cfg['sql']['type'] == 'mysql':
		date = strftime("%Y_%m", localtime(time()-600))
		
		sqls = []
		# rename logs table
		sqls.append("ALTER TABLE logs RENAME logs_%s" % date)
		
		# create logs table
		sqls.append("CREATE TABLE logs (host varchar(32) default NULL, facility varchar(10) default NULL, priority varchar(10) default NULL, level varchar(10) default NULL, tag varchar(10) default NULL, date date default NULL, time time default NULL,program varchar(15) default NULL, msg text, seq int(10) unsigned NOT NULL auto_increment, PRIMARY KEY (seq), KEY host (host), KEY seq (seq), KEY program (program), KEY time (time), KEY date (date), KEY priority (priority), KEY facility (facility))")

		# rename cache table
		sqls.append("ALTER TABLE cache RENAME cache_%s" % date)
		
		# create cache table
		sqls.append("CREATE TABLE cache ( id varchar(20) NOT NULL default '', value varchar(50) NOT NULL default '', PRIMARY KEY (`id`,`value`) )")

		for sql in sqls:
			cursor = conn.Execute(sql)
			cursor.Close()

#Minden ami SQL-bol kell:
sql = "SELECT * FROM visited WHERE type = 'log_check'"
cursor = conn.Execute(sql)
prev = cursor.GetRowAssoc(0)
cursor.Close()

#Servers:
lists=['host', 'facility', 'priority', 'date']
for i in lists:
	sql = "SELECT %(field)s FROM logs WHERE date >= %%s AND time >= %%s GROUP BY %(field)s" % {"field": i}
	cursor = conn.Execute(sql, (prev['date'], prev['time']))
	while not cursor.EOF:
		rs = cursor.GetRowAssoc(0) # 0 is lower, 1 is upper-case
		_cursor = conn.Execute ("SELECT id FROM cache WHERE value = %s", (rs[i]))
		if _cursor.EOF:
			conn.Execute ("INSERT INTO cache (id, value) VALUES (%s, %s)", (i, rs[i]))
		cursor.MoveNext()
		_cursor.Close()
	cursor.Close()

conn.Execute ("UPDATE visited SET date = now() , time = now() WHERE type = 'log_check'")

conn.Close()
