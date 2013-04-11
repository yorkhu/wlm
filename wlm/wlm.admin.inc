<?php

/**
* @file
* Administration page callbacks for the wlm module.
*/
/**
* Form builder. Configure wlm.
*
* @ingroup forms
* @see system_settings_form().
*/
function wlm_settings() {
  $form['wlm_sql_server'] = array(
    '#type' => 'fieldset',
    '#title' => t('Sql Server Settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );

  $sql_server_type['mysql'] = 'MySQL';
  $sql_server_type['pgsql'] = 'PostgreSQL';
  // $sql_server_type['sqlite'] = 'SQLite';
  $form['wlm_sql_server']['wlm_server_type'] = array(
    '#type' => 'radios',
    '#title' => t('Type'),
    '#default_value' => variable_get('wlm_server_type', 'mysql'),
    '#options' => $sql_server_type,
    '#description' => '',
  );

  $form['wlm_sql_server']['wlm_server'] = array(
    '#type' => 'textfield',
    '#title' => t('Host'),
    '#default_value' => variable_get('wlm_server', 'localhost'),
    '#description' => '',
    '#maxlength' => '200',
    '#size' => '30',
  );

  $form['wlm_sql_server']['wlm_port'] = array(
    '#type' => 'textfield',
    '#title' => t('Port'),
    '#default_value' => variable_get('wlm_port', '3306'),
    '#description' => '',
    '#maxlength' => '5',
    '#size' => '5',
  );

  $form['wlm_sql_server']['wlm_db'] = array(
    '#type' => 'textfield',
    '#title' => t('Database name'),
    '#default_value' => variable_get('wlm_db', 'syslog'),
    '#description' => '',
    '#size' => '10',
  );

  $form['wlm_sql_server']['wlm_user'] = array(
    '#type' => 'textfield',
    '#title' => t('Username'),
    '#default_value' => variable_get('wlm_user', 'user'),
    '#description' => '',
    '#size' => '10',
  );

  $form['wlm_sql_server']['wlm_pwd'] = array(
    '#type' => 'password',
    '#title' => t('Password'),
    '#description' => '',
    '#size' => '10',
  );

  return system_settings_form($form);
} // function wlm_settins()
