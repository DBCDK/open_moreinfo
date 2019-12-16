<?php

/**
 * @file
 * Admin settings for moreInfo client
 */

/**
 * Implements hook_form_FORM_ID_alter (ting_client_admin_webservices_settings)
 * Add fields to webservice client settings
 * */
function open_moreinfo_form_ting_client_admin_webservices_settings_alter(&$form, &$form_state) {

  // don't start by defining the fieldset as an array. This way other modules can extend the fieldset.
  $form['open_moreinfo']['#type'] = 'fieldset';
  $form['open_moreinfo']['#title'] = t('MoreInfo settings');
  $form['open_moreinfo']['#description'] = t("The MoreInfo service is used to retrieve cover images and other information about objects. <a href='http://www.danbib.dk/index.php?doc=forsideservice'>More information about the service (in Danish)</a>");
  $form['open_moreinfo']['#collapsible'] = TRUE;
  $form['open_moreinfo']['#collapsed'] = TRUE;
  $form['open_moreinfo']['#tree'] = FALSE;

  $form['open_moreinfo']['moreInfo_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Url'),
    '#description' => t('Url to the Additional information webservice'),
    '#required' => TRUE,
    '#default_value' => variable_get('moreInfo_url', ''),
  );

  $form['open_moreinfo']['moreInfo_username'] = array(
    '#type' => 'textfield',
    '#title' => t('Username'),
    '#description' => t('Username for the Additional information webservice'),
    '#required' => TRUE,
    '#default_value' => variable_get('moreInfo_username', ''),
  );

  $form['open_moreinfo']['moreInfo_group'] = array(
    '#type' => 'textfield',
    '#title' => t('Group'),
    '#description' => t('User group for the Additional information webservice'),
    '#required' => TRUE,
    '#default_value' => variable_get('moreInfo_group', ''),
  );

  $form['open_moreinfo']['moreInfo_password'] = array(
    '#type' => 'textfield',
    '#title' => t('Password'),
    '#description' => t('Password for the Additional information webservice'),
    '#required' => TRUE,
    '#default_value' => variable_get('moreInfo_password', ''),
  );

  $form['open_moreinfo']['moreInfo_lifetime'] = array(
    '#type' => 'select',
    '#title' => t('MoreInfo cache lifetime'),
    '#options' => array(
      CACHE_TEMPORARY =>  t('Disable cache'),
      '86400' => t("24 hours"),
      '604800' => t("1 week"),
      '2592000' => t("1 month"),
    ),
    '#default_value' => variable_get('moreInfo_lifetime', OPEN_MOREINFO_CACHE_LIFETIME),
  );

  $form['open_moreinfo']['moreInfo_enable_logging'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable logging'),
    '#default_value' => variable_get('moreInfo_enable_logging', FALSE),
  );
}
