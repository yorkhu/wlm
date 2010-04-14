<?php
/*
 * Created on 2006.01.10.
 */
//include_once 'DB.php';

//if (arg(0) == 'wlm') {
//	global $wlm_server;
//	$wlm_server = array(
//		'phptype'  => variable_get('wlm_server_type', 'mysql'),
//		'hostspec' => variable_get('wlm_server', 'localhost'),
//		'port'     => variable_get('wlm_port', '3306'),
//		'database' => variable_get('wlm_db', 'syslog'),
//		'username' => variable_get('wlm_user', 'user'),
//		'password' => variable_get('wlm_pwd', 'password'),
//	);
//}

/**
 * Display help and module information
 * @param section which section of the site we're displaying help
 * @return help text for section
 */
function wlm_help($section='') {
	$output = '';
	switch ($section) {
	case "admin/modules#description":
		$output = t("Web Log Monitor");
		break;
	}
	return $output;
} // function wlm_help()

/**
 * Implementation of hook_init().
 * Minden modul betolteskor lefut.
 */
function wlm_init() {
	global $db_url, $db_prefix;

	$wlm_db_url = variable_get('wlm_db_url', '');
	if ( !empty($wlm_db_url) ) {
		$db_url['wlm'] = $wlm_db_url;
		$db_prefix['wlm'] = '';
	}
} // function wlm_init

/**
 * Valid permissions for this module
 * @return array An array of valid permissions for the wlm module
 */
function wlm_perm() {
	return array('access wlm', 'create wlm', 'administer wlm');
} // function wlm_perm()

/**
 * wlm_menu: menus
 */
function wlm_menu() {
	$items = array();
	/** Administrator menus, System admin*/
	$items['admin/sysadmin/wlm'] = array(
		'title' => 'WLM settings',
		'page callback' => 'drupal_get_form',
		'page arguments' => array('wlm_settings'),
		'access arguments' => array('administer wlm'),
	);

	$items['wlm'] = array(
		'title' => 'Web Log Monitor',
		'page callback' => 'wlm_all',
		'access arguments' => array('access wlm'),
		'weight' => 1,
	);

	$items['wlm/autolog'] = array(
		'title' => 'Auto Log',
		'page callback' => 'wlm_autolog_page',
		'access arguments' => array('access wlm'),
		'weight' => 1,
	);

	$items['wlm/search'] = array(
		'title' => 'Search',
		'page callback' => 'wlm_search_page',
		'access arguments' => array('access wlm'),
		'weight' => 1,
	);

	$items['wlm/log'] = array(
		'title' => 'Log',
		'page callback' => 'wlm_log',
		'access arguments' => array('access wlm'),
		'type' => MENU_CALLBACK,
		'weight' => 1,
	);

	$items['wlm/template'] = array(
		'title' => 'Search template',
		'page callback' => 'wlm_template',
		'access arguments' => array('create wlm'),
		'weight' => 1,
	);

	$items['wlm/template/add'] = array(
		'title' => 'Add serach template',
		'page callback' => 'wlm_template_add',
		'access arguments' => array('create wlm'),
		'type' => MENU_CALLBACK,
		'weight' => 1,
	);

	$items['wlm/template/edit'] = array(
		'title' => 'Edit serach template',
		'page callback' => 'wlm_template_edit',
		'access arguments' => array('create wlm'),
		'type' => MENU_CALLBACK,
		'weight' => 1,
	);

	$items['wlm/template/delete'] = array(
		'title' => 'Delete serach template',
		'page callback' => 'wlm_template_delete_confirm',
		'access arguments' => array('create wlm'),
		'type' => MENU_CALLBACK,
		'weight' => 1,
	);

	$items['wlm/oldlogsearch'] = array(
		'title' => 'Old Logs Search',
		'page callback' => 'wlm_oldlogsearch',
		'access arguments' => array('access wlm'),
		'weight' => 1,
	);

	$items['wlm/oldlog'] = array(
		'title' => 'Old Logs',
		'page callback' => 'wlm_oldlog',
		'access arguments' => array('access wlm'),
		'type' => MENU_CALLBACK,
		'weight' => 1,
		);
	return $items;
} // function wlm_menu()

/**
 * Implementation of hook_theme()
 */
function wlm_theme($form, &$form_state) {
	return array(
		'wlm_all' => array(
			'arguments' => array(),
		),
		'wlm_template' => array(
			'arguments' => array('rows' => NULL),
		),

	);
} // function wlm_theme

/**
 * Settings for the wlm module
 * @return form contents for this module
 */
function wlm_settings() {
	$form['wlm_sql_server'] = array(
		'#type' => 'fieldset',
		'#title' => t('Sql Server Settings'),
		'#collapsible' => TRUE,
		'#collapsed' => false,
	);

	$sql_server_type['mysql'] = 'MySql';
	$sql_server_type['mysqli'] = 'MySqli';
	$sql_server_type['pgsql'] = 'PostgreSQL';
	$form['wlm_sql_server']['wlm_type'] = array(
		'#type' => 'radios',
		'#title' => t('Type'),
		'#default_value' => variable_get('wlm_server_type', mysql),
		'#options' => $sql_server_type,
		'#description' => '',
	);

	$form['wlm_sql_server']['wlm_server'] = array(
		'#type' => 'textfield',
		'#title' => t('Host'),
		'#default_value' => variable_get('wlm_server', localhost),
		'#description' => '',
		'#maxlength' => '200',
		'#size' => '30',
	);

	$form['wlm_sql_server']['wlm_port'] = array(
		'#type' => 'textfield',
		'#title' => t('Port'),
		'#default_value' => variable_get('wlm_port', 3306),
		'#description' => '',
		'#maxlength' => '5',
		'#size' => '5',
	);

	$form['wlm_sql_server']['wlm_db'] = array(
		'#type' => 'textfield',
		'#title' => t('Database name'),
		'#default_value' => variable_get('wlm_db', syslog),
		'#description' => '',
		'#size' => '10',
	);

	$form['wlm_sql_server']['wlm_user'] = array(
		'#type' => 'textfield',
		'#title' => t('Username'),
		'#default_value' => variable_get('wlm_user', user),
		'#description' => '',
		'#size' => '10',
	);

	$form['wlm_sql_server']['wlm_pwd'] = array(
		'#type' => 'textfield',
		'#title' => t('Password'),
		'#default_value' => variable_get('wlm_pwd', password),
		'#description' => '',
		'#size' => '10',
	);

	$form["submit"] = array(
		"#type" => "submit",
		"#value" => t('Save'),
	);

	return $form;
} // function wlm_settins()

function wlm_settings_submit($form, &$form_state) {
	global $db_url;

	$v = $form_state['values'];

	variable_set('wlm_server_type', $v['wlm_type']);
	variable_set('wlm_server', $v['wlm_server']);
	variable_set('wlm_port', $v['wlm_port']);
	variable_set('wlm_db', $v['wlm_db']);
	variable_set('wlm_user', $v['wlm_user']);
	if ( !empty($v['wlm_pwd']) ) {
		variable_set('wlm_pwd', $v['wlm_pwd']);
	} else {
		$v['wlm_pwd'] = variable_get('wlm_pwd', password);
	}

	$wlm_db_url = $v['wlm_type'].'://'.$v['wlm_user'].':'.$v['wlm_pwd'].'@'.$v['wlm_server'];
	if ( !empty($v['wlm_port']) ) {
		$wlm_db_url .= ':'.$v['wlm_port'];
	}
	$wlm_db_url .= '/'.$v['wlm_db'];

	variable_set('wlm_db_url', $wlm_db_url);
}

/**
 * Roviden mit tudnak a menuk.
 */
function wlm_all() {
	return theme('wlm_all');
} // function wlm_all()

function theme_wlm_all() {
	$output = '<dl>';
	$output .= '<dt>'.l(t('Auto Log'), 'wlm/autolog').':</dt>';
	$output .= '<dd>'.t('Automatic log viewer...').'</dd>';
	$output .= '<dt>'.l(t('Search'), 'wlm/search').':</dt>';
	$output .= '<dd>'.t('Search syslog...').'</dd>';
	$output .= '<dt>'.l(t('Old logs search'), 'wlm/oldlogsearch').':</dt>';
	$output .= '<dd>'.t('Search old syslog...').'</dd>';
	$output .= '<dt>'.l(t('Search template'), 'wlm/template').':</dt>';
	$output .= '<dd>'.t('Use and create search template...').'</dd>';
	$output .= '</dl>';

	return $output;
} // function theme_wlm_all()

/**
 * _wlm_get_default:
 * @param $links - modositja az alapertelmezett ertekeket
 * @param $def - alapertelmezett ertek megadasa, ha elter a megszokottol
 * @return form alapertelmezett elemeit adja vissza
 */
 function _wlm_get_default($links = array(), $def = array()) {
	if (!count($def)) {
		$def['host'] = '*';	$def['facility'] = '*';	$def['priority'] = '*';
		$def['date'] = '*';	$def['date2'] = '*';	$def['time'] = '*';	$def['time2'] = '*';
		$def['msg1_not'] = '0';	$def['msg1'] = '';	$def['msg2_op'] = '0';
		$def['msg2_not'] = '0';	$def['msg2'] = '';	$def['msg3_op'] = '0';
		$def['msg3_not'] = '0';	$def['msg3'] = '';
		$def['limit'] = '100';	$def['orderby'] = 'DESC';	$def['format'] = '1';
	}
	/**
	 * Alapertelemezett ertekek felul irasa a link parameter alapjan.
	 */
	if (count($links)) {
		$prev_key = '';
		foreach ($links as $value) {
			if ( strpos($value, '|') ) {
				list($key, $link) = explode('|', $value);
				if ( strpos($link, '+') ) {
					$link = explode('+', $link);
				}
				$def[$key] = $link;
				$prev_key = $key;
			} else {
				 $def[$prev_key] .= '/'.$value;
			}
		}
	}
	return $def;
} // function _wlm_get_default()

/**
 * _wlm_get_form:
 * @param $form_values - tartalmazza a form mezok ertekeit
 * @param $def - tartalmazza a form alapertelmezett ertekeit
 * @return form valtozot adja vissza
 */
function _wlm_get_form($form_values, $def) {
	$form['log'] = array(
		'#type' => 'fieldset',
		'#title' => t('Log Type').':',
		'#collapsible' => TRUE,
		'#collapsed' => false,
		'#prefix' => '<div id="wlm-select">',
		'#suffix' => '</div>',
	);
	$form['log']['host'] = array(
		'#type' => 'select',
		'#title' => t('Host'),
		'#default_value' => $def['host'],
		'#options' => $form_values['host'],
		'#multiple' => true,
		'#required' => false,
		'#prefix' => '<div class="wlm-left">',
		'#suffix' => '</div>',
	);

	$form['log']['facility'	] = array(
		'#type' => 'select',
		'#title' => t('Facility'),
		'#default_value' => $def['facility'],
		'#options' => $form_values['facility'],
		'#multiple' => true,
		'#required' => false,
		'#prefix' => '<div class="wlm-left">',
		'#suffix' => '</div>',
	);
	$form['log']['priority'] = array(
		'#type' => 'select',
		'#title' => t('Priority'),
		'#default_value' => $def['priority'],
		'#options' => $form_values['priority'],
		'#multiple' => true,
		'#required' => false,
		'#prefix' => '<div class="wlm-left">',
		'#suffix' => '</div>',
	);

	$form['dates'] = array(
		'#type' => 'fieldset',
		'#title' => t('Date'),
		'#collapsible' => TRUE,
		'#collapsed' => false,
		'#prefix' => '<div id="wlm-dates">',
		'#suffix' => '</div>',
	);
	$form['dates']['date'] = array(
		'#type' => 'select',
		'#title' => t('Date'),
		'#default_value' => $def['date'],
		'#options' => $form_values['date'],
		'#prefix' => '<div class="wlm-date1">',
		'#suffix' => '<span class="sep"> - </span></div> ',
	);
	$form['dates']['date2'] = array(
		'#type' => 'select',
		'#default_value' => $def['date2'],
		'#options' => $form_values['date'],
		'#prefix' => '<div class="wlm-date2">',
		'#suffix' => '</div>',
	);
	$form['dates']['time'] = array(
		'#type' => 'select',
		'#title' => t('Time'),
		'#default_value' => $def['time1'],
		'#options' => $form_values['times'],
		'#prefix' => '<div class="wlm-date1">',
		'#suffix' => '<span class="sep"> - </span></div> ',
	);
	$form['dates']['time2'] = array(
		'#type' => 'select',
		'#default_value' => $def['time2'],
		'#options' => $form_values['times'],
		'#prefix' => '<div class="wlm-date2">',
		'#suffix' => '</div>',
	);

	$form['txt'] = array(
		'#type' => 'fieldset',
		'#title' => t('Search message').':',
		'#collapsible' => TRUE,
		'#collapsed' => false,
		'#prefix' => "\n\n\n".'<div id="wlm-txt">',
		'#suffix' => '</div>'."\n\n\n",
	);
	$form['txt']['msg1_not'] = array(
		'#type' => 'checkbox',
		'#default_value' => $def['msg1_not'],
		'#title' => t('Not'),
		'#prefix' => "\n\n\n".'<div class="wlm-line">',
	);
	$form['txt']['msg1'] = array(
		'#type' => 'textfield',
		'#default_value' => $def['msg1'],
		'#size' => 60,
		'#maxlength' => 128,
		'#required' => false,
	);
	$form['txt']['msg2_op'] = array(
		'#type' => 'radios',
		'#default_value' => $def['msg2_op'],
		'#options' => $form_values['chk_opt'],
		'#suffix' => '</div>'."\n\n\n",
	);
	$form['txt']['msg2_not'] = array(
		'#type' => 'checkbox',
		'#default_value' => $def['msg2_not'],
		'#title' => t('Not'),
		'#prefix' => "\n\n\n".'<div class="wlm-line">',
	);
	$form['txt']['msg2'] = array(
		'#type' => 'textfield',
		'#default_value' => $def['msg2'],
		'#size' => 60,
		'#maxlength' => 128,
		'#required' => false,
	);
	$form['txt']['msg3_op'] = array(
		'#type' => 'radios',
		'#default_value' => $def['msg3_op'],
		'#options' => $form_values['chk_opt'],
		'#suffix' => '</div>'."\n\n\n",
	);
	$form['txt']['msg3_not'] = array(
		'#type' => 'checkbox',
		'#default_value' => $def['msg3_not'],
		'#title' => t('Not'),
		'#prefix' => "\n\n\n".'<div class="wlm-line">',
	);
	$form['txt']['msg3'] = array(
		'#type' => 'textfield',
		'#default_value' => $def['msg3'],
		'#size' => 60,
		'#maxlength' => 128,
		'#required' => false,
		'#suffix' => '</div>'."\n\n\n",
	);

	$form['other'] = array(
		'#type' => 'fieldset',
		'#title' => t('Other').':',
		'#collapsible' => TRUE,
		'#collapsed' => false,
		'#prefix' => '<div id="wlm-other">',
		'#suffix' => '</div>',
	);
	$form['other']['limit'] = array(
		'#type' => 'select',
		'#title' => t('Records per page'),
		'#default_value' => $def['limit'],
		'#options' => $form_values['limits'],
		'#prefix' => '<div class="wlm-left">',
		'#suffix' => '</div>',
	);
	$form['other']['orderby'] = array(
		'#type' => 'select',
		'#title' => t('Search order'),
		'#default_value' => $def['orderby'],
		'#options' => $form_values['orderby'],
		'#prefix' => '<div class="wlm-left">',
		'#suffix' => '</div>',
	);
	$form['other']['format'] = array(
		'#type' => 'select',
		'#title' => t('Format'),
		'#default_value' => $def['format'],
		'#options' => $form_values['format'],
		'#prefix' => '<div class="wlm-left">',
		'#suffix' => '</div>',
	);
	return $form;
} // function _wlm_get_form()

/**
 * _wlm_get_link: Kitoltott form parametereibol linket keszit
 * @param $def - alapertelmezett form ertekek
 * @param $values - form altal atadott ertekek
 * @return $link - a form ertekeibol keszitett linket adja vissza
 */
function _wlm_get_link($def, $values) {
	/**
	 * alapertelmezettol eltero ertekbol csinal linket
	 */
	while (!is_null($key = key($values) ) ) {
		if ( ($key !='submit') && ($key != 'form_id') ) {
			if ( is_array($values[$key]) ) {
				if ( !(($values[$key][0] == '*') || ($values[$key][0] == 'all')) ) {
					$link .= '/'.$key.'|';
					while (!is_null($sub_key = key($values[$key]) ) ) {
						$link .= $values[$key][$sub_key].'+';
						next($values[$key]);
					}
					$link = trim($link, '+');
				}
			} else {
				if ($def[$key] != $values[$key]) {
					$link .= '/'.$key.'|'.$values[$key];
				}
			}
		}
		next($values);
	}
	return $link;
} // function _wlm_get_link()

/**
 * _wlm_url2sql: atadott parameterekbol SQL feltetelt keszit
 * @param $db - adatbazis kapcsolat
 * @param $url - linkbol generalt valtozo
 * @return sql feltetel
 */
function _wlm_url2sql($db, $url) {
	$SQL = '';
	$list = array('host', 'facility', 'priority');
	foreach ($list as $key) {
		$sub_sql = '';
		if ( is_array($url[$key]) ) {
			foreach ($url[$key] as $element) {
				$sub_sql .= $key." = '".$db->escapeSimple($element)."' OR ";
			}
			$sub_sql = trim($sub_sql,' OR ');
		} elseif ( ($url[$key] != '') && ($url[$key] != '*') ) {
			$sub_sql = $key." = '".$db->escapeSimple($url[$key])."'";
		}
		if ($sub_sql != '') {
			$SQL .= '('.$sub_sql.') AND ';
		}
	}

	if ($url['date'] != '*') {
		if ($url['date2'] == '*') {
			$SQL .= "date = '".$db->escapeSimple($url['date'])."' AND ";
		} else {
			if ($url['date'] > $url['date2']) {
				//	Datum csere
				$tmp = $url['date'];
				$url['date'] = $url['date2'];
				$url['date2'] = $tmp;
			}
			$SQL .= "date >= '".$db->escapeSimple($url['date'])."' AND date <= '".$db->escapeSimple($url['date2'])."' AND ";
		}
	}

	if ($url['time'] != '*') {
		if ($url['time2'] == '*') {
			$SQL .= "HOUR(time) = '".$db->escapeSimple($url['time']).":00' AND ";
		} else {
			if ($url['time'] > $url['time2']) {
				//	Datum csere
				$tmp = $url['time'];
				$url['time'] = $url['time2'];
				$url['time2'] = $tmp;
			}
			$SQL .= "HOUR(time) between '".$db->escapeSimple($url['time']).":00' AND '".$db->escapeSimple($url['time2']).":00' AND ";
		}
	}

	if ($url['msg1'] != '') {
		if ($url['msg1_not']) {
			$msg_not = ' NOT ';
		} else {
			$msg_not = '';
		}
		$SQL .= '((msg '.$msg_not." LIKE '%".$db->escapeSimple($url['msg1'])."%')";

		if ($url['msg2'] != '') {
			if ($url['msg2_not']) {
				$msg_not = ' NOT ';
			} else {
				$msg_not = '';
			}

			if ($url['msg2_op']) {
				$msg_op = ' OR ';
			} else {
				$msg_op = ' AND ';
			}
			$SQL .= $msg_op.'(msg '.$msg_not." LIKE '%".$db->escapeSimple($url['msg2'])."%')";

			if ($url['msg3'] != '') {
				if ($url['msg3_not']) {
					$msg_not = ' NOT ';
				} else {
					$msg_not = '';
				}

				if ($url['msg3_op']) {
					$msg_op = ' OR ';
				} else {
					$msg_op = ' AND ';
				}
				$SQL .= $msg_op.'(msg '.$msg_not." LIKE '%".$db->escapeSimple($url['msg3'])."%')";
			}
		}
		$SQL .= ')';

	}
	if ($SQL == '') {
		$SQL = '1';
	}
	return $SQL;
} // function _wlm_url2sql()

/**
 * wlm_autolog_page: autmatikus log kiiratas
 */
function wlm_autolog_page() {
	global $wlm_server;
	$links = func_get_args();
	theme_add_style($path = 'modules/wlm/style.css', $media = 'all');

	$def['host'] = 'all';	$def['facility'] = 'all';	$def['rtime'] = '60';	$def['limit'] = '25';

	if (count($links)) {
		$def = _wlm_get_default($links, $def);
		$refresh = $def['rtime'];
	} else {
		$refresh = 0;
	}
	$db =& DB::connect($wlm_server);
	if (DB::isError($db)) {
		drupal_set_message( t($db->getMessage()), 'error' );
		return " ";
//		die($db->getMessage());
	}
	$db->setFetchMode(DB_FETCHMODE_ASSOC);

	$SQL = "SELECT * FROM cache WHERE id='host' OR id='facility' ORDER BY value ASC";
	$result =& $db->query($SQL);
	if (DB::isError($result)) {
		drupal_set_message( t($result->getMessage()), 'error' );
		return " ";
//		die($result->getMessage());
	}
	$form_values['host']['all'] = t('All');
	$form_values['facility']['all'] = t('All');
	if ($result->numRows()>0) {
		while ($row =& $result->fetchRow()) {
			$form_values[$row['id']][$row['value']] = $row['value'];
		}
	}
	$form['auto'] = array(
		'#type' => 'fieldset',
		'#title' => t('Auto Log Settings'),
		'#collapsible' => TRUE,
		'#collapsed' => $refresh,
		'#prefix' => '',
		'#suffix' => '',
	);
	$form['auto']['host'] = array(
		'#type' => 'select',
		'#title' => t('Host'),
		'#default_value' => $def['host'],
		'#options' => $form_values['host'],
		'#multiple' => true,
		'#required' => false,
		'#prefix' => '<div id="wlm-select"><div class="wlm-left">',
		'#suffix' => '</div>',
	);

	$form['auto']['facility'] = array(
		'#type' => 'select',
		'#title' => t('Facility'),
		'#default_value' => $def['facility'],
		'#options' => $form_values['facility'],
		'#multiple' => true,
		'#required' => false,
		'#prefix' => '<div class="wlm-left">',
		'#suffix' => '</div></div>',
	);

	$form_values['rtime']['30'] = '0.5 '.t('Minute');	$form_values['rtime']['60'] = '01 '.t('minute');
	$form_values['rtime']['120'] = '02 '.t('minute');	$form_values['rtime']['300'] = '05 '.t('minute');
	$form_values['rtime']['300'] = '10 '.t('minute');	$form_values['rtime']['600'] = '20 '.t('minute');
	$form['auto']['rtime'] = array(
		'#type' => 'select',
		'#title' => t('Refresh Time'),
		'#default_value' => $def['rtime'],
		'#options' => $form_values['rtime'],
		'#required' => false,
	);
	$form_values['limit']['25'] = '25'; $form_values['limit']['50'] = '50';
	$form_values['limit']['100'] = '100';	$form_values['limit']['250'] = '250';
	$form['auto']['limit'] = array(
		'#type' => 'select',
		'#title' => t('Records per page'),
		'#default_value' => $def['limit'],
		'#options' => $form_values['limit'],
		'#required' => false,
	);
	$form['submit'] = array('#type' => 'submit', '#value' => t('Refresh'));

	if ($refresh) {
		$sql = 'SELECT * FROM logs';
		if ( ($def['host'] != 'all') || ($def['facility'] != 'all') ) {
			$sql .= " WHERE ";
			if ($def['host'] != 'all') {
				$sql .= "";
				if ( is_array($def['host']) ) {
					$sub_sql = '(';
					foreach ($def['host'] as $element) {
						$sub_sql .= "host  = '".$db->escapeSimple($element)."' OR ";
					}
					$sql .= trim($sub_sql,' OR ');
					$sql .= ')';
				} else {
					$sql .= "host = '".$db->escapeSimple($def['host'])."'";
				}
				$sql .= " AND ";
			}
			if ($def['facility'] != 'all') {
				if ( is_array($def['facility']) ) {
					$sub_sql = '(';
					foreach ($def['facility'] as $element) {
						$sub_sql .= "facility = '".$db->escapeSimple($element)."' OR ";
					}
					$sql .= trim($sub_sql,' OR ').')';
				} else {
					$sql .= "facility = '".$db->escapeSimple($def['facility'])."'";
				}
			}
			$sql = rtrim($sql, ' AND ');
		}
//		echo $sql;
		$result =& $db->limitQuery($sql.' ORDER BY seq DESC', 0, $def['limit']);
		if (DB::isError($result)) {
			drupal_set_message( t($result->getMessage()), 'error' );
			return " ";
//			die($result->getMessage());
		}
		if ($result->numRows()>0) {
			while ($row =& $result->fetchRow()) {
				$rows[] = array(
					array('data' => $row['seq'], 'class' => 'wlm_seq'),
					array('data' => $row['host'], 'class' => 'wlm_host'),
					array('data' => $row['priority'], 'class' => 'wlm_priority_'.$row['priority']),
					array('data' => $row['date'].' '.$row['time'], 'class' => 'wlm_date'),
					array('data' => $row['facility'], 'class' => 'wlm_facility'),
					array('data' => htmlspecialchars($row['msg'], ENT_QUOTES, 'UTF-8'), 'class' => 'wlm_msg1'),
//					array('data' => $row['level'], 'class' => 'wlm_level'),
//					array('data' => $row['tag'], 'class' => 'wlm_tag'),
//					array('data' => $row['program'], 'class' => 'wlm_program'),
				);
			}
		}
	} else {
		$rows = '';
	}
	$form = drupal_get_form('wlm_autolog_form', $form, $rows, $refresh);
	return theme('wlm_autolog', $form, $rows, $refresh);
} // function wlm_autolog_page()

/**
 * theme_wlm_autolog_form: form kinezet hook
 */
function theme_wlm_autolog_form($form) {
	$output = '';
	$output .= form_render($form);
	return $output;
} // function theme_wlm_autolog_form()

/**
 * theme_wlm_autolog: autolog oldal kenezetet adja meg
 * @param $form - kiirando form
 * @param $rows - tablazat sorai
 * @param $refresh - 0/1 ha volt frissites akkor: 1
 */
function theme_wlm_autolog($form, $rows, $refresh) {
	$output = '';
	$output .= $form;
	if ($refresh) {
		drupal_set_header('Refresh: '.$refresh.';');
		$output .= '<div id="wlm_log">';
		$header = array(array('data' => t('Seq')),
			array('data' => t('Host')),
			array('data' => t('Priority')),
			array('data' => t('Date')),
			array('data' => t('Facility')),
			array('data' => t('Message')),
//			array('data' => t('level')),
//			array('data' => t('tag')),
//			array('data' => t('program')),
		);
		$output .= theme('table', $header, $rows);
		$output .= '</div>';
//		var_dump($rows);
	}
	return $output;
} // function wlm_autolog_page()

/**
 * wlm_autolog_form_submit: autolog submit hook
 */
function wlm_autolog_form_submit($form_id, $form) {
	if ($form['host'] != '') {
		$def = array();
//		echo _wlm_get_link($def, $form);
		drupal_goto('/wlm/autolog'._wlm_get_link($def, $form));
	}
} // function wlm_autolog_form_submit()

/**
 * wlm_search_page: Log kereses form kiiratasa.
 */
function wlm_search_page() {
	global $wlm_server;
	$links = func_get_args();
	theme_add_style($path = 'modules/wlm/style.css', $media = 'all');

	$db =& DB::connect($wlm_server);
	if (DB::isError($db)) {
		drupal_set_message( t($db->getMessage()), 'error' );
		return " ";
//		die($db->getMessage());
	}
	$db->setFetchMode(DB_FETCHMODE_ASSOC);

	$SQL = "SELECT * FROM cache ORDER BY value ASC";
	$result =& $db->query($SQL);
	if (DB::isError($result)) {
		drupal_set_message( t($result->getMessage()), 'error' );
		return " ";
//		die($result->getMessage());
	}
	$form_values['host']['*'] = $form_values['facility']['*'] = t('All');
	$form_values['priority']['*'] = $form_values['date']['*'] = t('All');
	if ($result->numRows()>0) {
		while ($row =& $result->fetchRow()) {
			$form_values[$row['id']][$row['value']] = $row['value'];
		}
	}
	array_multisort($form_values['date'], SORT_DESC);

	$def = _wlm_get_default($links);

	$form_values['times']['*'] = t('All');
	for ($i = 0; $i < 24; $i++) {
		if ($i < 10) {
			$form_values['times']['0'.$i] = '0'.$i.':00';
		} else {
			$form_values['times'][$i] = $i.':00';
		}
	}

	$form_values['chk_opt'] = array('0' => t('And'), '1' => t('Or'));

	$form_values['limits']['25'] = '25'; $form_values['limits']['50'] = '50'; 	$form_values['limits']['100'] = '100';
	$form_values['limits']['250'] = '250'; $form_values['limits']['500'] = '500'; $form_values['limits']['1000'] = '1000';

	$form_values['orderby']['ASC'] = t('ASC');	$form_values['orderby']['DESC'] = t('DESC');
	$form_values['format'] = array(t("Wrap On"), t("Wrap Off"), t("Text Mode"));

	$form = _wlm_get_form($form_values, $def);
	$form['submit'] = array('#type' => 'submit', '#value' => t('Search'));

	return drupal_get_form('wlm_search_page', $form);
}  // function wlm_search_page()

/**
 * theme_wlm_search_page: Search form theme hook
 */
 function theme_wlm_search_page($form) {
	$output = '';
	$output .= form_render($form);
	return $output;
}  // function theme_wlm_search_page()

/**
 * wlm_search_page_submit: Kereses, atdobb a kereses oldalra.
 */
function wlm_search_page_submit($form_id, $form) {
	if ($form['host'] != '') {
		$def = _wlm_get_default();
		drupal_goto('/wlm/log'._wlm_get_link($def, $form));
	}
}  // function wlm_search_page_submit()

/**
 * wlm_log: kereses eredmenyet keszit elo kiiratasra.
 */
function wlm_log() {
	global $pager_total, $pager_page_array;
	global $wlm_server;

	$links = func_get_args();
	$action = _wlm_get_default($links);
	if ( ($action['host'] != '') && (count($links)) ) {
		$db =& DB::connect($wlm_server);
		if (DB::isError($db)) {
			drupal_set_message( t($db->getMessage()), 'error' );
			return " ";
//			die($db->getMessage());
		}
		$db->setFetchMode(DB_FETCHMODE_ASSOC);

		$SQL = _wlm_url2sql($db, $action);
		$sql_count = "SELECT count(seq) as num FROM logs WHERE ".trim($SQL, ' AND ');
		$sql = 'SELECT * FROM logs WHERE '.trim($SQL, ' AND ');
		$result =& $db->query($sql_count);
		if (DB::isError($result)) {
			drupal_set_message( t($result->getMessage()), 'error' );
			return " ";
//			die($result->getMessage());
		}
		$row_count =& $result->fetchRow();

		if (!isset($_GET['page'])) {
			$_GET['page'] = 0;
		} else {
			settype($_GET['page'], "integer");
			$pager_page_array[0] = $_GET['page'];
		}
		$pager_total[] = ceil($row_count['num']/$action['limit']);
		$result =& $db->limitQuery($sql.' ORDER BY seq '.$action['orderby'], $_GET['page']*$action['limit'], $action['limit']);
		if (DB::isError($result)) {
			drupal_set_message( t($result->getMessage()), 'error' );
			return " ";
//			die($result->getMessage());
		}
		if ($result->numRows()>0) {
			while ($row =& $result->fetchRow()) {
				$rows[] = array(
					array('data' => $row['seq'], 'class' => 'wlm_seq'),
					array('data' => $row['host'], 'class' => 'wlm_host'),
					array('data' => $row['priority'], 'class' => 'wlm_priority_'.$row['priority']),
					array('data' => $row['date'].' '.$row['time'], 'class' => 'wlm_date'),
					array('data' => $row['facility'], 'class' => 'wlm_facility'),
					array('data' => htmlspecialchars($row['msg'], ENT_QUOTES, 'UTF-8'), 'class' => 'wlm_msg'.$action['format']),
//					array('data' => $row['level'], 'class' => 'wlm_level'),
//					array('data' => $row['tag'], 'class' => 'wlm_tag'),
//					array('data' => $row['program'], 'class' => 'wlm_program'),
				);
			}
		}
		return theme("wlm_log", $rows, $action['limit'], $action['format']);
	} else {
		drupal_goto('/wlm/search');
	}
}  // function wlm_log()

/**
 * theme_wlm_log: Logok kiiratasa
 */
function theme_wlm_log($rows, $log_per_page, $format, $old = '') {
	theme_add_style($path = 'modules/wlm/style.css', $media = 'all');

	$breadcrumb[] = array('path' => 'wlm');
	$breadcrumb[] = array('path' => 'wlm/'.$old.'search/'.ltrim($_GET['q'], 'wlm/'.$old.'log'), 'title' => t('Search'));
	$breadcrumb[] = array('path' => $_GET['q']);
	menu_set_location($breadcrumb);
	drupal_set_title(t('Log'));

	$output = '';
	$output .= '<div id="wlm_log">';
	$header = array(array('data' => t('Seq')),
		array('data' => t('Host')),
		array('data' => t('Priority')),
		array('data' => t('Date')),
		array('data' => t('Facility')),
		array('data' => t('Message')),
//		array('data' => t('level')),
//		array('data' => t('tag')),
//		array('data' => t('program')),
	);
	$output .= theme('table', $header, $rows);
	$output .= '</div>';
	$output .= theme('pager', NULL, $log_per_page, 0);
	return $output;
}  // function theme_wlm_log()

/**
 * wlm_template: Kereses minta/sablon menu
 */
function wlm_template() {
	global $user;

	function date_change ($date) {
		if ($date == 'today') {
			$date = date("Y-m-d");
		} elseif ($date < 0) {
			$date = date("Y-m-d", strtotime($date." day"));
		}
		return $date;
	}

	$def = _wlm_get_default();
	$admin = user_access('administer wlm');
	$rows = array();

	$result = db_query("SELECT w.wid, w.label, w.action, w.public, u.name FROM {wlm_search_template} w  INNER JOIN {users} u ON w.uid = u.uid  WHERE w.public = '1' OR w.uid = %d ORDER BY w.label", $user->uid);
	while ($row = db_fetch_array($result)) {
		if ($row['public']) {
			$public = 'Yes';
		} else {
			$public = 'No';
		}
		$action = unserialize($row['action']);
		if ($action['date'] != '') {
			$action['date'] = date_change($action['date']);
		}
		if ($action['date2'] != '') {
			$action['date2'] = date_change($action['date2']);
		}
		$link = _wlm_get_link($def, $action);

		$acces = 0;
		if ($admin) {
			$acces = 1;
		} elseif ($user->name == $row['name']) {
			$acces = 1;
		}

		$rows[] = array(
			array('data' => l($row['label'], '/wlm/search'.$link), 'class' => 'wlm_label'),
			array('data' => $row['name'], 'class' => 'wlm_user'),
			array('data' => t($public), 'class' => 'wlm_public'),
			array('data' => l(t('search'), '/wlm/search'.$link), 'class' => 'wlm_view'),
			array('data' => $acces ? l(t('edit'), '/wlm/template/edit/'.$row['wid']) : '', 'class' => 'wlm_edit'),
			array('data' => $acces ? l(t('delete'), '/wlm/template/delete/'.$row['wid']) : '', 'class' => 'wlm_del'),
		);
	}
	return theme("wlm_template", $rows);
} // function wlm_template()

/**
 * theme_wlm_template: Kereses sablon kiiratasa
 */
function theme_wlm_template($rows) {
	drupal_add_css(drupal_get_path('module', 'wlm').'/wlm.css');

	$output = '';

	$output .= '<ul>';
	$output .= ' <li>'. l(t('Create new search template.'), "wlm/template/add") .'</li>';
	$output .= '</ul>';
	$output .= '<div id="wlm_template">';
	$header = array(array('data' => t('Label')),
		array('data' => t('User')),
		array('data' => t('Public')),
		array('data' => ''),
		array('data' => ''),
		array('data' => ''),
//		array('data' => t('Facility')),
//		array('data' => t('Message')),
	);
	$output .= theme('table', $header, $rows);
	$output .= '</div>';
//	$output .= theme('pager', NULL, $log_per_page, 0);
	return $output;
}  // function theme_wlm_template()

/**
 * wlm_template_add: Kereses sablon hozza adasa
 */
function wlm_template_add() {
	drupal_add_css(drupal_get_path('module', 'wlm').'/wlm.css');

	/** Kapcsolodas az SQL szerverhez */
	db_set_active('wlm');

	$SQL = "SELECT * FROM {cache} WHERE id != 'date' ORDER BY value ASC";
	$result = db_query($SQL);

	/** Vissza valtas az alapertelmezett SQL szerverhez */
	db_set_active();

	$form_values['host']['*'] = $form_values['facility']['*'] = t('All');
	$form_values['priority']['*'] = $form_values['date']['*'] = t('All');
	$form_values['date']["today"] = t('Today');
	while ($row = db_fetch_array($result)) {
		$form_values[$row['id']][$row['value']] = $row['value'];
	}


	for ($i = -1; $i >= -31; $i--) {
		$form_values['date']["$i"] = $i.' '.t('Day');
	}

	$def = _wlm_get_default();

	$form_values['times']['*'] = t('All');
	for ($i = 0; $i < 24; $i++) {
		if ($i < 10) {
			$form_values['times']['0'.$i] = '0'.$i.':00';
		} else {
			$form_values['times'][$i] = $i.':00';
		}
	}

	$form_values['chk_opt'] = array('0' => t('And'), '1' => t('Or'));

	$form_values['limits']['25'] = '25'; $form_values['limits']['50'] = '50'; 	$form_values['limits']['100'] = '100';
	$form_values['limits']['250'] = '250'; $form_values['limits']['500'] = '500'; $form_values['limits']['1000'] = '1000';

	$form_values['orderby']['ASC'] = t('ASC');	$form_values['orderby']['DESC'] = t('DESC');
	$form_values['format'] = array(t("Wrap On"), t("Wrap Off"), t("Text Mode"));

	$form['desc'] = array(
		'#type' => 'fieldset',
		'#title' => t('Label').':',
		'#collapsible' => TRUE,
		'#collapsed' => false,
		'#prefix' => '<div id="wlm-label">',
		'#suffix' => '</div>',
	);
	$form['desc']['label'] = array(
		'#type' => 'textfield',
		'#default_value' => $def['label'],
		'#size' => 60,
		'#maxlength' => 128,
		'#required' => TRUE,
	);

	$form = array_merge($form, _wlm_get_form($form_values, $def));
	if (user_access('administer wlm')) {
		$form['other']['public'] = array(
			'#type' => 'select',
			'#title' => t('Public'),
			'#default_value' => '',
			'#options' => array( t('No'), t('Yes') ),
			'#prefix' => '<div class="wlm-left">',
			'#suffix' => '</div>',
		);
	}
	$form['submit'] = array('#type' => 'submit', '#value' => t('Save'));

	return '.';
//	return drupal_get_form('wlm_template_add', $form);
} // function wlm_template_add()

/**
 * theme_wlm_template_add: Kereses sablon hozzadas form kiiratasa
 */
function theme_wlm_template_add($form) {
	$breadcrumb[] = array('path' => 'wlm');
	$breadcrumb[] = array('path' => 'wlm/template');
	$breadcrumb[] = array('path' => $_GET['q']);
	menu_set_location($breadcrumb);

	$output = '';
	$output .= form_render($form);
	return $output;
}  // function theme_wlm_template_add()

/**
 * wlm_template_add_submit: Kereses sablon adatbazisba felvitele
 */
function wlm_template_add_submit($form_id, $form) {
	global $user;
	$def = _wlm_get_default();
	while (!is_null($key = key($form) ) ) {
		if ( ($key !='submit') && ($key != 'form_id') && ($key != 'public') && ($key != 'label') ) {
			if ($def[$key] != $form[$key]) {
				$action[$key] = $form[$key];
			}
		}
		next($form);
	}
	if (!user_access('administer wlm')) {
		$form['public'] = 0;
	}
	$sql = "INSERT INTO {wlm_search_template} (wid, uid, label, action, public) VALUES (%d, %d, '%s', '%s', %d)";
	db_query($sql, db_next_id('{wlm_search_template}_wid'), $user->uid, $form['label'], serialize($action), $form['public']);

	drupal_goto('/wlm/template');
}  // function wlm_template_add_submit()

/**
 * wlm_template_edit: Kereses sablon szerkesztese
 * @param $wid template azonositoja
 */
function wlm_template_edit($wid) {
	global $user;
	global $wlm_server;
	theme_add_style($path = 'modules/wlm/style.css', $media = 'all');

	if (user_access('administer wlm')) {
		$result = db_query('SELECT label, action FROM {wlm_search_template} w WHERE wid=%d', $wid);
	} else {
		$result = db_query('SELECT label, action FROM {wlm_search_template} w WHERE uid=%d AND wid=%d', $user->uid, $wid);
	}

	if ($rs = db_fetch_array($result)) {
		$db =& DB::connect($wlm_server);
		if (DB::isError($db)) {
			drupal_set_message( t($db->getMessage()), 'error' );
			return " ";
//			die($db->getMessage());
		}
		$db->setFetchMode(DB_FETCHMODE_ASSOC);

		$SQL = "SELECT * FROM cache WHERE id != 'date' ORDER BY value ASC";
		$result =& $db->query($SQL);
		if (DB::isError($result)) {
			drupal_set_message( t($result->getMessage()), 'error' );
			return " ";
//			die($result->getMessage());
		}

		$form_values['host']['*'] = $form_values['facility']['*'] = t('All');
		$form_values['priority']['*'] = $form_values['date']['*'] = t('All');
		$form_values['date']["today"] = t('Today');
		if ($result->numRows()>0) {
			while ($row =& $result->fetchRow()) {
				$form_values[$row['id']][$row['value']] = $row['value'];
			}
		}
		for ($i = -1; $i >= -31; $i--) {
			$form_values['date']["$i"] = $i.' '.t('Day');
		}

		$def = _wlm_get_default();

		$def['label'] =$rs['label'];

		$values = unserialize($rs['action']);
		while (!is_null($key = key($values) ) ) {
			if ( is_array($values[$key]) ) {
				while (!is_null($sub_key = key($values[$key]) ) ) {
					$def[$key] = $values[$key][$sub_key];
					next($values[$key]);
				}
			} else {
				$def[$key] = $values[$key];
			}
			next($values);
		}

		$form_values['times']['*'] = t('All');
		for ($i = 0; $i < 24; $i++) {
			if ($i < 10) {
				$form_values['times']['0'.$i] = '0'.$i.':00';
			} else {
				$form_values['times'][$i] = $i.':00';
			}
		}

		$form_values['chk_opt'] = array('0' => t('And'), '1' => t('Or'));

		$form_values['limits']['25'] = '25'; $form_values['limits']['50'] = '50'; 	$form_values['limits']['100'] = '100';
		$form_values['limits']['250'] = '250'; $form_values['limits']['500'] = '500'; $form_values['limits']['1000'] = '1000';

		$form_values['orderby']['ASC'] = t('ASC');	$form_values['orderby']['DESC'] = t('DESC');
		$form_values['format'] = array(t("Wrap On"), t("Wrap Off"), t("Text Mode"));

		$form['desc'] = array(
			'#type' => 'fieldset',
			'#title' => t('Label').':',
			'#collapsible' => TRUE,
			'#collapsed' => false,
			'#prefix' => '<div id="wlm-label">',
			'#suffix' => '</div>',
		);
		$form['desc']['label'] = array(
			'#type' => 'textfield',
			'#default_value' => $def['label'],
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
		);

		$form = array_merge($form, _wlm_get_form($form_values, $def));

		if (user_access('administer wlm')) {
			$form['other']['public'] = array(
				'#type' => 'select',
				'#title' => t('Public'),
				'#default_value' => '',
				'#options' => array( t('No'), t('Yes') ),
				'#prefix' => '<div class="wlm-left">',
				'#suffix' => '</div>',
			);
		}
		$form['submit'] = array('#type' => 'submit', '#value' => t('Save'));

		return drupal_get_form('wlm_template_edit', $form);
	} else {
		drupal_access_denied();
//		drupal_goto('/wlm/template');
	}
} // function wlm_template_edit()

/**
 * theme_wlm_template_edit: Kereses sablon szerkesztes form kiiratasa
 */
function theme_wlm_template_edit($form) {
	return theme_wlm_template_add($form);
}  // function theme_wlm_template_edit()

/**
 * wlm_template_edit_submit: Kereses sablon modositas adatbazisba irasa
 */
function wlm_template_edit_submit($form_id, $form) {
	global $user;

	$wid = arg(3);
	if (is_numeric($wid)) {
		$def = _wlm_get_default();
		while (!is_null($key = key($form) ) ) {
			if ( ($key !='submit') && ($key != 'form_id') && ($key != 'public') && ($key != 'label') ) {
				if ($def[$key] != $form[$key]) {
					$action[$key] = $form[$key];
				}
			}
			next($form);
		}

		if (user_access('administer wlm')) {
			$sql = "UPDATE {wlm_search_template} SET label='%s', action='%s', public=%d WHERE wid = %d";
			db_query($sql, $form['label'], serialize($action), $form['public'], $wid);
		} else {
			$sql = "UPDATE {wlm_search_template} SET label='%s', action='%s', public=%d WHERE uid=%d AND wid=%d";
			db_query($sql, $form['label'], serialize($action), $form['public'], $user->uid, $wid);
		}
	}
	drupal_goto('/wlm/template');
	return "";
}  // function wlm_template_edit_submit()

/**
 * wlm_template_delete_confirm: Kereses sablon torles megerositese
 * @param $wid template id
 */
function wlm_template_delete_confirm($wid) {
	global $user;
	if (user_access('administer wlm')) {
		$result = db_query('SELECT wid, label FROM {wlm_search_template} w WHERE wid=%d', $wid);
	} else {
		$result = db_query('SELECT wid, label FROM {wlm_search_template} w WHERE uid=%d AND wid=%d', $user->uid, $wid);
	}
	if ($row = db_fetch_array($result)) {
		$form['wid'] = array('#type' => 'value', '#value' => $wid);
	    $output = confirm_form('wlm_template_delete_confirm', $form,
			t('Are you sure you want to delete %title?', array('%title' => theme('placeholder', $row['label']))),
			$_GET['destination'] ? $_GET['destination'] : 'wlm/template', t('This action cannot be undone.'),
			t('Delete'), t('Cancel')  );
	} else {
		drupal_access_denied();
//		drupal_goto('wlm/template');
	}
	return $output;
} // function wlm_template_delete_confirm()

/**
 * wlm_template_delete_confirm_submit: Kereses sablon torles megerositve
 */
function wlm_template_delete_confirm_submit($form_id, $form_values) {
	global $user;
	if ($form_values['confirm']) {
		wlm_template_delete($form_values['wid']);
		drupal_goto('wlm/template');
	}
} // function wlm_template_delete_confirm_submit()

/**
 * wlm_template_delete: Kereses sablon torlese az adatbazisbol
 * @param $wid template id
 */
function wlm_template_delete($wid) {
	global $user;
	if (user_access('administer wlm')) {
		$result = db_query('SELECT uid, public FROM {wlm_search_template} w WHERE wid=%d', $wid);
	} else {
		$result = db_query('SELECT uid, public FROM {wlm_search_template} w WHERE uid=%d AND wid=%d', $user->uid, $wid);
	}
	if ($rs = db_fetch_array($result)) {
		db_query('DELETE FROM {wlm_search_template} WHERE wid=%d', $wid);
	}
} // function wlm_template_delete()

/**
 * wlm_oldlogsearch: kereses a regi logokban
 */
function wlm_oldlogsearch() {
	global $wlm_server;
	$links = func_get_args();
	theme_add_style($path = 'modules/wlm/style.css', $media = 'all');

	$db =& DB::connect($wlm_server);
	if (DB::isError($db)) {
		drupal_set_message( t($db->getMessage()), 'error' );
		return " ";
//		die($db->getMessage());
	}

	$SQL = "SHOW TABLES LIKE 'cache%'";
	$result =& $db->query($SQL);
	if (DB::isError($result)) {
		drupal_set_message( t($result->getMessage()), 'error' );
		return " ";
//		die($result->getMessage());
	}
	if ($result->numRows()>0) {
		while ($row =& $result->fetchRow()) {
			if (ltrim($row['0'], 'cache') != '') {
				$old_db[] =  ltrim($row['0'], 'cache_');
			}
		}
	}
	array_multisort($old_db, SORT_DESC);
	$refresh = count($links);

	$old_form['old'] = array(
		'#type' => 'fieldset',
		'#title' => t('Old Logs'),
		'#collapsible' => TRUE,
		'#collapsed' => $refresh,
		'#prefix' => '<div id="wlm-old">',
		'#suffix' => '</div>',
	);

	while (!is_null($key = key($old_db) ) ) {
		$old_form['old'][$old_db[$key]] = array(
			'#type' => 'submit',
			'#value' => $old_db[$key],
			'#name' => 'old_log',
			'#prefix' => '<div class="wlm-left">',
			'#suffix' => '</div>',
		);
		next($old_db);
	}

	$db->setFetchMode(DB_FETCHMODE_ASSOC);
	if ($refresh) {
		$SQL = "SHOW TABLES LIKE 'cache_".$db->escapeSimple($links[0])."'";

		$result =& $db->query($SQL);
		if (DB::isError($result)) {
			drupal_set_message( t($result->getMessage()), 'error' );
			return " ";
//			die($result->getMessage());
		}
		if ($result->numRows() == 0) {
			drupal_goto('wlm/oldlogsearch');
		}

		$links[0] = str_replace('.', '_',$links[0]);
		$SQL = "SELECT * FROM cache_".$db->escapeSimple($links[0])." ORDER BY value ASC";
		$result =& $db->query($SQL);
		if (DB::isError($result)) {
			drupal_set_message( t($result->getMessage()), 'error' );
			return " ";
//			die($result->getMessage());
		}
		$form_values['host']['*'] = $form_values['facility']['*'] = t('All');
		$form_values['priority']['*'] = $form_values['date']['*'] = t('All');
		if ($result->numRows()>0) {
			while ($row =& $result->fetchRow()) {
				$form_values[$row['id']][$row['value']] = $row['value'];
			}
		}
		array_multisort($form_values['date'], SORT_DESC);

		$def = _wlm_get_default($links);

		$form_values['times']['*'] = t('All');
		for ($i = 0; $i < 24; $i++) {
			if ($i < 10) {
				$form_values['times']['0'.$i] = '0'.$i.':00';
			} else {
				$form_values['times'][$i] = $i.':00';
			}
		}

		$form_values['chk_opt'] = array('0' => t('And'), '1' => t('Or'));

		$form_values['limits']['25'] = '25'; $form_values['limits']['50'] = '50'; 	$form_values['limits']['100'] = '100';
		$form_values['limits']['250'] = '250'; $form_values['limits']['500'] = '500'; $form_values['limits']['1000'] = '1000';

		$form_values['orderby']['ASC'] = t('ASC');	$form_values['orderby']['DESC'] = t('DESC');
		$form_values['format'] = array(t("Wrap On"), t("Wrap Off"), t("Text Mode"));

		$form = _wlm_get_form($form_values, $def);
		$form['hidden'] = array(
			'#type' => 'hidden',
			'#value' => $links[0],
		);
		$form['submit'] = array('#type' => 'submit', '#value' => t('Search'));
	} else {
		$form = array();
	}
	return theme('wlm_oldlogsearch', $old_form, $form);
} // function wlm_oldlogsearch()

/**
 * theme_wlm_oldlogsearch: regi logok kiiratasa
 * @param $old_form - regi logok formja
 * @param $form - a kivalasztott regi log kereso formja
 */
 function theme_wlm_oldlogsearch($old_form, $form) {
	$output = '';
	$output .= drupal_get_form('wlm_oldlogsearch_form', $old_form);
	if (count($form)) {
		$output .= drupal_get_form('wlm_oldlog_form', $form);
	}
	return $output;
} // function theme_wlm_oldlogsearch

/**
 * theme_wlm_oldlogsearch_form
 */
 function theme_wlm_oldlogsearch_form($form) {
	$output = '';
	$output .= form_render($form);
	return $output;
} // function theme_wlm_oldlogsearch_form()

/**
 * wlm_oldlogsearch_form_submit
 */
function wlm_oldlogsearch_form_submit($form_id, $form) {
	$old_log = isset($_POST['old_log']) ? $_POST['old_log'] : '';
	drupal_goto('wlm/oldlogsearch/'.$old_log);
} // function wlm_oldlogsearch_form_submit()

/**
 * wlm_oldlog_form_submit
 */
function wlm_oldlog_form_submit($form_id, $form) {
		$def = _wlm_get_default();
		drupal_goto('/wlm/oldlog/'.$form['hidden']._wlm_get_link($def, $form));
} // function wlm_oldlog_form_submit()

/**
 * wlm_oldlog: kereses a regi logban
 */
function wlm_oldlog() {
	global $pager_total, $pager_page_array;
	global $wlm_server;

	$links = func_get_args();
	$action = _wlm_get_default($links);

	if ($action['host'] != '') {
		$db =& DB::connect($wlm_server);
		if (DB::isError($db)) {
			drupal_set_message( t($db->getMessage()), 'error' );
			return " ";
//			die($db->getMessage());
		}
		$db->setFetchMode(DB_FETCHMODE_ASSOC);

		$SQL = "SHOW TABLES LIKE 'logs_".$db->escapeSimple($links[0])."'";

		$result =& $db->query($SQL);
		if (DB::isError($result)) {
			drupal_set_message( t($result->getMessage()), 'error' );
			return " ";
//			die($result->getMessage());
		}
		if ($result->numRows() == 0) {
			drupal_goto('wlm/oldlogsearch');
		}

		$SQL = _wlm_url2sql($db, $action);
		$action['hidden'] = $db->escapeSimple(str_replace('.', '_',$action['hidden']));
		$sql_count = 'SELECT count(seq) as num FROM logs_'.$action['hidden'].' WHERE '.trim($SQL, ' AND ');
		$sql = 'SELECT * FROM logs_'.$action['hidden'].' WHERE '.trim($SQL, ' AND ');

		$result =& $db->query($sql_count);
		if (DB::isError($result)) {
			drupal_set_message( t($result->getMessage()), 'error' );
			return " ";
//			die($result->getMessage());
		}
		$row_count =& $result->fetchRow();

		if (!isset($_GET['page'])) {
			$_GET['page'] = 0;
		} else {
			settype($_GET['page'], "integer");
			$pager_page_array[0] = $_GET['page'];
		}
		$pager_total[] = ceil($row_count['num']/$action['limit']);
		$result =& $db->limitQuery($sql.' ORDER BY seq '.$action['orderby'], $_GET['page']*$action['limit'], $action['limit']);
		if (DB::isError($result)) {
			drupal_set_message( t($result->getMessage()), 'error' );
			return " ";
//			die($result->getMessage());
		}
		if ($result->numRows()>0) {
			while ($row =& $result->fetchRow()) {
				$rows[] = array(
					array('data' => $row['seq'], 'class' => 'wlm_seq'),
					array('data' => $row['host'], 'class' => 'wlm_host'),
					array('data' => $row['priority'], 'class' => 'wlm_priority_'.$row['priority']),
					array('data' => $row['date'].' '.$row['time'], 'class' => 'wlm_date'),
					array('data' => $row['facility'], 'class' => 'wlm_facility'),
					array('data' => htmlspecialchars($row['msg'], ENT_QUOTES, 'UTF-8'), 'class' => 'wlm_msg'.$action['format']),
//					array('data' => $row['level'], 'class' => 'wlm_level'),
//					array('data' => $row['tag'], 'class' => 'wlm_tag'),
//					array('data' => $row['program'], 'class' => 'wlm_program'),
				);
			}
		}
		return theme("wlm_log", $rows, $action['limit'], $action['format'], 'old');
	} else {
		drupal_goto('/wlm/search');
	}
}  // function wlm_oldlog()

