#!/usr/bin/env python
# TODO:
# Eredmeny kuldese mailba
# Kereses regi logba
# Kereses automatikus frissitese percenkent
# Tobb nyelv tamogatasa
# Kereses eredmenyenek csoportositasa (GROUP BY msg)
# Szemelyes szuro listak keszitese (sablonok).
# ^^ SQL tabla: uid, pub(y/n), group, megnevezes, txt(kereses beallitasok)
# 

#

import sys, os, adodb, cgi
import cgitb; cgitb.enable()
from Cookie import SimpleCookie
from time import localtime, strftime, time, clock
import pickle, locale

cfg = {}
# globalis valtozok.
cfg['name'] = 'Web Log Monitor'
cfg['ver'] = '0.1.0'
cfg['link'] = 'link'
# karakter kodolas.
cfg['charset'] = 'ISO-8859-2'
# nyelv
cfg['mylocate'] = 'hu_HU'
#datum formatum
cfg['date_format'] = '%Y, %B %d %A - %H:%M:%S'
cfg['sql'] = {}
#SQL Konfiguracio:
cfg['sql']['server'] = 'localhost'
cfg['sql']['type'] = 'mysql'
cfg['sql']['user'] = 'syslog'
cfg['sql']['passwd'] = 'syslog'
cfg['sql']['db'] = 'syslog'

def connect(cfg):
	'adatbazis kapcsolat felepitese'
	try:
		conn = adodb.NewADOConnection(cfg['type'])
		conn.Connect(cfg['server'], cfg['user'], cfg['passwd'], cfg['db'])
	except:
		conn = None
	return conn

class SqlSession(object):
#	import adodb
#	import pickle
#	from time import localtime, strftime, time

	def __init__(self, cfg, ip, sid='', uid=0):
		"Sql base session. Szukseges modulok: time, adodb, pickle"
		self._error = ''
		self.__con = None
		self.__NoError()

		self._cfg = cfg
		self.__Connect()
		
		self._uid = uid
		self._ip = ip

		if sid == '':
			sid = self.__GenerateHash()
			self._sid = sid
			self.__New()
		else:
			self._sid = sid
			self.__Refresh()

	def __repr__(self):
		return str(self.GetData())

	def __Connect(self):
		"Adatbazis kapcsolat felepitese"
		self.__NoError()
		try:
			self.__con = adodb.NewADOConnection(self._cfg['type'])
			self.__con.Connect(self._cfg['server'], self._cfg['user'], self._cfg['passwd'], self._cfg['db'])
			#self.__con.useExceptions = False
		except:
			self.__con = None
			self._error = 'Connection error!!!'

	def __GenerateHash(self):
		"session azonosito generalasa"
		import sha, time
		self.__NoError()
		return sha.new(str(time.time())).hexdigest()

	def __New(self):
		"uj session beszurasa az adat bazisba"
		self.__NoError()
		datetime = strftime('%Y-%m-%d %H:%M:%S', localtime())
		curs = self.__con.Execute( "INSERT INTO sessions (sid, uid, host, date) VALUES (%s, %s, %s, %s)", (self._sid, self._uid, self._ip, datetime) )
		if curs == None:
			self._error = self.__con.ErrorMsg()
		curs.Close()

	def __Refresh(self):
		"session datum frissites"
		self.__NoError()
		sql = "SELECT uid FROM sessions WHERE sid = %s AND uid = %s"
		curs_exist = self.__con.Execute(sql, (self._sid, self._uid))
		if curs_exist == None:
			self._error = self.__con.ErrorMsg()
		else:
			if curs_exist.Affected_Rows() == 0:
				self.__New()
			else:
				datetime = strftime('%Y-%m-%d %H:%M:%S', localtime())
				sql = "UPDATE sessions SET date = %s WHERE sid=%s AND uid = %s"
				curs = self.__con.Execute( sql, ( datetime, self._sid, self._uid ) )
				if curs == None:
					self._error = self.__con.ErrorMsg()
				curs.Close()
			curs_exist.Close()

	def Set(self, lists):
		"session tartalom feltoltese"
		self.__NoError()
		sql = "UPDATE sessions SET sql = %s WHERE sid=%s AND uid = %s"
		curs = self.__con.Execute( sql, ( pickle.dumps(lists), self._sid, self._uid ) )
		if curs == None:
			self._error = self.__con.ErrorMsg()
		curs.Close()

	def Next(self, lists = {}):
		"sessionhoz a kovetkezo adat hozza fuzese"
		self.__NoError()
		sql = "SELECT uid FROM sessions WHERE sid = %s AND sql = %s"
		curs = self.__con.Execute( sql, (self._sid, pickle.dumps(lists)) )
		if curs == None:
			self._error = self.__con.ErrorMsg()
		else:
			if curs.Affected_Rows() != 0:
				rs = curs.GetRowAssoc(0)
				uid = rs['uid']
			else:
				sql = "SELECT uid, sql FROM sessions WHERE sid = %s AND sql = '' ORDER BY uid"
				curs_empty = self.__con.Execute( sql, (self._sid) )
				if curs_empty == None:
					self._error = self.__con.ErrorMsg()
				else:
					if curs_empty.Affected_Rows() != 0:
						rs = curs_empty.GetRowAssoc(0)
						uid = rs['uid']
					else:
						sql = "SELECT max(uid) AS uid_max FROM sessions WHERE sid = %s"
						curs_uid_max = self.__con.Execute(sql, (self._sid))
						if curs_uid_max == None:
							self._error = self.__con.ErrorMsg()
						else:
							if curs_uid_max.Affected_Rows() != 0:
								rs = curs_uid_max.GetRowAssoc(0)
								if rs['uid_max'] != None:
									uid = int(rs['uid_max'])+1
								else:
									uid = 0
							else:
								uid = 0
							self._uid = uid
							self.__New()
							curs_uid_max.Close()
					curs_empty.Close()
			curs.Close()
			self._uid=uid
			self.Set(lists)
			self.__Refresh()

	def GetData(self):
		"sessionhoz tartozo adat lekerdezese az adatbazisbol"
		self.__NoError()
		lists = {}
		sql = "SELECT host, date, sql FROM sessions WHERE sid = %s AND uid = %s"
		curs = self.__con.Execute(sql, (self._sid, self._uid))
		if curs == None:
			self._error = self.__con.ErrorMsg()
		else:
			if curs.Affected_Rows() != 0:
				rs = curs.GetRowAssoc(0)
				lists = pickle.loads(rs['sql'])
			curs.Close()
		return lists

	def GetSid(self):
		self.__NoError()
		return self._sid

	def GetUid(self):
		self.__NoError()
		return self._uid

	def __NoError(self):
		self._error = ''
		
	def Error(self):
		"Error!!!"
		return self._error

	sid = property(GetSid)
	uid = property(GetUid)
	data = property(GetData, Next)
	error = property(Error)

import libxml2
import libxslt
import types
from cStringIO import StringIO
from xml.sax.saxutils import escape

class mkpage:
#	import libxml2
#	import libxslt
#	import types
#	import os
#	from cStringIO import StringIO
#	from xml.sax.saxutils import escape
	def __init__(self):
		self.__base = []
		self._error = 1
		self._ErrorMsg = ''
		self.__page = StringIO()

	def Add(self, xslt, data, charset = 'utf-8'):
		"xslt ez az adatok hozza adasa"
		self.__NoError()
		self.__base.append({'xslt': xslt, 'data': data, 'charset': charset})

	def GetPage(self):
		"Legeneralja az oldalt"
		self.__NoError()
		for item in self.__base:
			try:
				xml = self.__mkxml(item['data'])
				doc = libxml2.parseDoc('<?xml version="1.0" encoding="%(charset)s"?>\n<source>%(xml)s\n</source>\n' % {'charset': item['charset'] , 'xml': xml})
				if os.path.isfile(item['xslt']):
					styledoc = libxml2.parseFile(item['xslt'])
				else:
					styledoc = libxml2.parseDoc(str(item['xslt']))
				style = libxslt.parseStylesheetDoc(styledoc)
				result = style.applyStylesheet(doc, None)
				self.__page.write( style.saveResultToString(result) )
				style.freeStylesheet()
				doc.freeDoc()
				result.freeDoc()
			except:
				self._error = 1
				self._ErrorMsg += "Error, generation template: %s " % item["xslt"]
		return self.__page.getvalue()

	def __gen_xml(self, id, value, tab):
#		return ("\n<%(id)s>%(value)s</%(id)s>" % {'ftab': '\t' * tab, 'id': id, 'ntab': '\t' * (tab + 1), 'value': value})
		return ("\n%(ftab)s<%(id)s>%(value)s</%(id)s>" % {'ftab': '\t' * tab, 'id': id, 'value': value})

	def __mkxml(self, list, tab = 0):
		"XML-t general"
		out = StringIO()
		if type(list) == types.DictType:
			for data in list.keys():
				out.write( self.__gen_xml(data, self.__mkxml(list[data], tab+1), tab) )
		elif type(list) == types.ListType:
			for data in list:
				out.write( self.__gen_xml('id', self.__mkxml(data, tab+1), tab) )
		else:
			out.write(escape(str(list)))
		return out.getvalue()

	def __NoError(self):
		self._error = 0
		self._ErrorMsg = ''
		
	def Error(self):
		"Error!!!"
		return self._ErrorMsg

#fejlec kiiratasa:
class header:
	def __init__(self):
		self.headers = []
	def add(self, header):
		self.headers.append(header)
	def out(self):
		for header in self.headers:
			print header

#Cookie letrehozas
def set_cookie(Cookie, sid):
	Cookie['sid'] = sid
	Cookie['sid']['max-age'] = 3600
	myheader.add(Cookie)

#feldolgozza a kereses parametereit es vissza add egy listat
def form2list(form):
	lists = {}
	elements=['host', 'facility', 'priority']
	for var in elements:
		if form.has_key(var) and form.getlist(var) and str(form.getlist(var)) != "['']" and str(form.getlist(var)) != "['*']" :
			lists[var] = form.getlist(var)
			
	elements = ['date', 'date2', 'time', 'time2', 'limit', 'orderby', 'format']
	for var in elements:
		if form.has_key(var):
			value = form[var].value
			if value != '' and value !='*':
				lists[var] = form[var].value
	
	if form.has_key('msg1') and form['msg1'].value != '':
		lists['msg1'] = form['msg1'].value
		if form.has_key('msg1_not'):
			lists['msg1_not'] = form['msg1_not'].value
		
		if form.has_key('msg2') and form['msg2'].value != '' and form.has_key('msg2_op'):
			lists['msg2'] = form['msg2'].value
			lists['msg2_op'] =  form['msg2_op'].value
			if form.has_key('msg2_not'):
				lists['msg2_not'] = form['msg2_not'].value
		if form.has_key('msg3') and form['msg3'].value != '' and form.has_key('msg3_op'):
			lists['msg3'] = form['msg3'].value
			lists['msg3_op'] =  form['msg3_op'].value
			if form.has_key('msg3_not'):
				lists['msg3_not'] = form['msg3_not'].value

	if not lists.has_key('limit') or lists['limit'] == '':
		lists['limit'] = 100

	if not lists.has_key('format') or lists['format'] == '':
		lists['format'] = 'off'

	if not lists.has_key('orderby') or lists['orderby'] == '':
		lists['orderby'] = "DESC"

	return lists

#lista adataibol general sql-t	
def list2sql(lists):
	elements = ['host', 'facility', 'priority']
	data = {}
	data['sql'] = ''
	data['value'] = ()

	for var in elements:
		if lists.has_key(var):
			tmp = ''
			tmp_src = ()
			for i in lists[var]:
				tmp += var+" = %s OR "
				tmp_src += i,
			if tmp != '':
				tmp = tmp.rstrip(' OR ')
				data['sql'] += '(' + tmp + ') AND '
				data['value'] += tmp_src

	if lists.has_key('date'):
		tmp = ''
		tmp_src = ()
		if lists.has_key('date2'):
			if lists['date'] > lists['date2']:
				lists['date'], lists['date2'] = (lists['date2'], lists['date'])
			tmp += "date >= %s AND date <= %s AND "
			tmp_src += lists['date'], lists['date2'],

		else:
			tmp += "date = %s AND "
			tmp_src += lists['date'],

		if tmp != '':
			data['sql'] += tmp
			data['value'] += tmp_src

	if lists.has_key('time'):
		tmp = ''
		tmp_src = ()
		if lists.has_key('time2'):
			if lists['time'] > lists['time2']:
				lists['time'], lists['time2'] = (lists['time2'], lists['time'])
			tmp += "HOUR(time) between %s AND %s AND "
			tmp_src += lists['time'], lists['time2'],
		
		else:
			tmp += "HOUR(time) =  %s AND "
			tmp_src += lists['time'],
		
		if tmp != '':
			data['sql'] += tmp
			data['value'] += tmp_src

	if lists.has_key('msg1'):
		msg = lists['msg1']
		tmp = ''
		tmp_src = ()
		if lists.has_key('msg1_not') and lists['msg1_not'] == '1':
			msg_not = 'NOT '
		else:
			msg_not = ''
		tmp += '(msg ' + msg_not + "LIKE %s)"
		tmp_src += '%'+ msg.replace('*','%%') +'%',

		if lists.has_key('msg2'):
			msg = lists['msg2']
			if lists.has_key('msg2_not') and lists['msg2_not'] == '1':
				msg_not = 'NOT '
			else:
				msg_not = ''

			if lists['msg2_op'] == '1':
				msg_op = ' OR '
			else:
				msg_op = ' AND '

			tmp += msg_op + '(msg ' + msg_not + "LIKE %s)"
			tmp_src += '%'+ msg.replace('*','%%') +'%',
		if lists.has_key('msg3'):
			msg = lists['msg3']
			if lists.has_key('msg3_not') and lists['msg3_not'] == '1':
				msg_not = 'NOT '
			else:
				msg_not = ''

			if lists['msg3_op'] == '1':
				msg_op = ' OR '
			else:
				msg_op = ' AND '

			tmp += msg_op + '(msg ' + msg_not + "LIKE %s)"
			tmp_src += '%'+ msg.replace('*','%%') +'%',
		data['sql'] += tmp
		data['value'] += tmp_src

	data['sql'] = data['sql'].rstrip(' AND ')

	return data

# lekerdezes szamat adja vissza
def sql_num(sql):
	cursor = conn.Execute(sql, data['value'])
	rs = cursor.GetRowAssoc(0)
	rows = rs['num']
	cursor.Close()
	return rows

# az sql lekerdezesbol csinal tal szamara hasznos listat
def sql2list(sql, limit, start, value):
	cursor = conn.SelectLimit(sql, limit, start, value)
	list = []
	color = "row2"
	color_tmp = "row1"
	while not cursor.EOF:
		rs = cursor.GetRowAssoc(0) # 0 is lower, 1 is upper-case
		color,color_tmp=(color_tmp,color)
		list.append({"class": color,"seq": rs['seq'], "host": rs['host'], "host_link": "index.py?uid=%s&host=%s" % (sess.uid, rs['host']) , "priority": rs['priority'], "priority_class": 'priority_'+rs['priority'], "priority_link": "index.py?uid=%s&priority=%s" % (sess.uid, rs['priority']) ,"date": str(rs['date']) + ' ' + str(rs['time']) , "facility": rs['facility'], "facility_link": "index.py?uid=%s&facility=%s" % (sess.uid, rs['facility']) ,"msg": rs['msg']})
		cursor.MoveNext()
	cursor.Close()
	return list

# nyito oldal generalasa:
def mk_src_page():
	#Minden ami SQL-bol kell:
	#Servers, Facility Priority Date:
	selects = {}
	lists=['host', 'facility', 'priority', 'date']
	for i in lists:
		sql = "SELECT value FROM cache WHERE id = %s"
		if i == 'date':
			sql += " ORDER BY value DESC"
		cursor = conn.Execute(sql, (i))
		list = ["*"]
		while not cursor.EOF:
			rs = cursor.GetRowAssoc(0) # 0 is lower, 1 is upper-case
			list.append(rs['value'])
			cursor.MoveNext()
		selects[i] = list
		cursor.Close()
	#Time:
	list = ["*"]
	for i in range(24):
		if i < 10:
			time_value='0%d:00'%(i)
		else:
			time_value='%d:00'%(i)
		list.append(time_value)
	selects['time'] = list
	selects['submit'] = "Search"
	selects['reset'] = "Reset"
	return selects

# java script generalasa a nyito oldalra, beallitja az aktualis keresesnek megfeleloen a formot:
def mk_js_page(selects):
	out = "\n<!--\nbase = 0 \n"

	elements = ['host', 'facility', 'priority']
	j = 0
	for var in elements:
		if lists.has_key(var):
			for i in lists[var]:
				out += "\ndocument.forms[base].elements[%d].options[%d].selected = true;" % (j,  selects[var].index(i))
		j += 1

	elements = ['date', 'date2', 'time', 'time2']
	for var in elements:
		if lists.has_key(var):
			if selects.has_key(var):
				out += "\ndocument.forms[base].elements[%d].options[%d].selected = true;" % (j,  selects[var].index(lists[var]))
			else:
				out += "\ndocument.forms[base].elements[%d].options[%d].selected = true;" % (j,  selects[var[:-1]].index(lists[var]))

		j += 1
	elements = ['msg1_not', 'msg1', 'skip', 'msg2_op', 'msg2_not', 'msg2', 'skip', 'msg3_op', 'msg3_not', 'msg3']
	for var in elements:
		if lists.has_key(var):
			if var == 'msg1' or var == 'msg2' or var == 'msg3':
				out += "\ndocument.forms[base].elements[%d].value = '%s';" % (j, lists[var])
			else:
				out += "\ndocument.forms[base].elements[%d].checked = true;" % (j)
		j += 1

	tmp = ['25', '50', '100', '200', '500', '1000']
	if lists.has_key('limit'):
		out += "\ndocument.forms[base].elements[%d].options[%d].selected = true;" % (j,  tmp.index(lists['limit']))
	j += 1

	tmp = ['ASC', 'DESC']
	if lists.has_key('orderby'):
		out += "\ndocument.forms[base].elements[%d].options[%d].selected = true;" % (j,  tmp.index(lists['orderby']))
	j += 1

	tmp = ['off', 'on', 'txt']
	if lists.has_key('format'):
		out += "\ndocument.forms[base].elements[%d].options[%d].selected = true;" % (j,  tmp.index(lists['format']))

	out += "\n-->"	
	return out

# a kereses eredmenyet jeleniti meg:
def mk_rs_page():
	global site

	rows = 0
	list = []
	if data['sql'] != '':
		sql = "SELECT count(seq) as num FROM logs WHERE " + data['sql']
		rows = sql_num(sql)
		sql = "SELECT * FROM logs WHERE " + data['sql']
		sql += ' ORDER BY seq '+lists['orderby']
		list = sql2list(sql, lists['limit'], (page-1)*long(lists['limit']), data['value'])

	pages = long(rows) / long(lists['limit'])
	pages, p = divmod (long(rows), long(lists['limit']))
	if p > 0:
		pages += 1

	if pages > 20:
		pages = 20

	site['pages'] = {}
	site['pages']['prev'] = []
	for i in range(1, page):
		site['pages']['prev'].append({"txt": i,"link": "index.py?uid=%s&page=%d" % (sess.uid, i)})

	site['pages']['next'] = []
	for i in range(page+1, pages+1):
		site['pages']['next'].append({"txt": i,"link": "index.py?uid=%s&page=%d" % (sess.uid, i)})

	site['back'] = {}
	site['back']['txt'] = 'BACK TO SEARCH'
	site['back']['link'] = 'index.py?uid=%s' % (sess.uid)
	site['search'] = list
	site['wrap'] = lists['format']
	site['rows'] = {}
	site['rows']['txt'] = 'Number of Syslog Entries: '
	site['rows']['num'] = rows
	site['pages']['now'] = '1'

#erdemes megnezni...
#def hum():
#	for k,v in os.environ.items():
#		print k + ' - ' + v + '<br>'

# nyelvi kornyezet beallitasa.
locale.setlocale(locale.LC_ALL, cfg['mylocate'])

# template inicializalasa.
mypage = mkpage()
myheader = header()
site = {}
site["ver"] = cfg["ver"]
site["name"] = cfg["name"]
#klien ip cime
site["ip"] = os.environ['REMOTE_ADDR']
site["date"] = strftime(cfg['date_format'], localtime())
site["link"] = "http://openproject.hu/wlm/"

#cgi inditas
form = cgi.FieldStorage()

#Start SQL connect:
conn = connect(cfg['sql'])
if conn != None:
	start_pgen = time()
	if os.environ.has_key('REQUEST_METHOD') and os.environ['REQUEST_METHOD'] == 'POST':
		#POST
		if form:
			if os.environ.has_key('HTTP_COOKIE') and SimpleCookie(os.environ['HTTP_COOKIE']).has_key('sid'):
				Cookie = SimpleCookie(os.environ['HTTP_COOKIE'])
				sid = Cookie['sid'].value
			else:
				Cookie = SimpleCookie()

			sess = SqlSession(cfg["sql"], site["ip"])
			set_cookie(Cookie, sess.sid)
			myheader.add("Content-type: text/html\n")

			lists = form2list(form)
			data = list2sql(lists)
			page = 1
			sess.data = lists
			uid = sess.uid
			mk_rs_page()
			end_pgen = time()
	elif os.environ.has_key('REQUEST_METHOD') and os.environ['REQUEST_METHOD'] == 'GET':
		#GET
		if form:
			myheader.add("Content-type: text/html\n")
			if form.has_key('uid') and str(form['uid'].value) != '' and long(form['uid'].value) >= 0:
				if os.environ.has_key('HTTP_COOKIE') and SimpleCookie(os.environ['HTTP_COOKIE']).has_key('sid'):
					sid = SimpleCookie(os.environ['HTTP_COOKIE'])['sid'].value
					uid = form['uid'].value
					sess = SqlSession(cfg["sql"], site["ip"], sid)
					lists = sess.data
					#LAPOZAS
					if form.has_key('page') and str(form['page'].value) != '' and long(form['page'].value) >= 1 and long(form['page'].value) <= 20:
						page = long(form['page'].value)
						data = list2sql(lists)
						mk_rs_page()
						site['pages']['now'] = int(page)
					#HOST
					elif form.has_key('host') and str(form['host'].value) != '':
						page = 1
						lists['host'] = form.getlist('host')
						data = list2sql(lists)
						sess.data = lists
						mk_rs_page()
					#PRIORITY
					elif form.has_key('priority') and str(form['priority'].value) != '':
						page = 1
						lists['priority'] = form.getlist('priority')
						data = list2sql(lists)
						sess.data = lists
						mk_rs_page()
					#FACILITY
					elif form.has_key('facility') and str(form['facility'].value) != '':
						page = 1
						lists['facility'] = form.getlist('facility')
						data = list2sql(lists)
						sess.data = lists
						mk_rs_page()
					#VISSZA A KEZDO LAPRA
					elif os.environ['QUERY_STRING'] == 'uid='+str(sess.uid):
						site['form'] = mk_src_page()
						js = mk_js_page(site['form'])
						site["js"] = js
					end_pgen = time()
		#KEZDO OLDAL
		else:
			myheader.add("Content-type: text/html\n")
			site["form"] = mk_src_page()
	try:
		site['pgen_time'] = "%.5f" % (end_pgen-start_pgen)
	except:
		pass
	mypage.Add('master.xsl', site, cfg["charset"])
	conn.Close()
# oldal megjelenitese:

myheader.out()
print mypage.GetPage()

