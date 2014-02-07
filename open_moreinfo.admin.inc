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

  $form['moreinfo'] = array(
    '#type' => 'fieldset',
    '#title' => t('MoreInfo settings'),
    '#description' => t('The MoreInfo service is used to retrieve cover images and other information about objects. <a href="http://www.danbib.dk/index.php?doc=forsideservice">More information about the service (in Danish)</a>'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => FALSE,
  );

  $form['moreinfo']['open_moreinfo_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Service URL'),
    '#description' => t('URL to the MoreInfon webservice, e.g. http://moreinfo.addi.dk/2.1/moreinfo.wsdl'),
    '#required' => TRUE,
    '#default_value' => variable_get('open_moreinfo_url', ''),
  );

  $form['moreinfo']['open_moreinfo_username'] = array(
    '#type' => 'textfield',
    '#title' => t('Username'),
    '#description' => t('Username for the Additional information webservice'),
    '#required' => TRUE,
    '#default_value' => variable_get('open_moreinfo_username', ''),
  );

  $form['moreinfo']['open_moreinfo_group'] = array(
    '#type' => 'textfield',
    '#title' => t('Group'),
    '#description' => t('User group for the Additional information webservice'),
    '#required' => TRUE,
    '#default_value' => variable_get('open_moreinfo_group', ''),
  );

  $form['moreinfo']['open_moreinfo_password'] = array(
    '#type' => 'textfield',
    '#title' => t('Password'),
    '#description' => t('Password for the Additional information webservice'),
    '#required' => TRUE,
    '#default_value' => variable_get('open_moreinfo_password', ''),
  );

  $form['moreinfo']['open_moreinfo_cache_lifetime'] = array(
    '#type' => 'select',
    '#title' => t('MoreInfo cache lifetime'),
    '#options' => array(
      CACHE_TEMPORARY =>  t('Disable cache'),
      '86400' => t("24 hours"),
      '604800' => t("1 week"),
      '2592000' => t("1 month"),
    ),
    '#default_value' => variable_get('open_moreinfo_cache_lifetime', OPEN_MOREINFO_CACHE_LIFETIME),
  );

  $form['moreinfo']['open_moreinfo_enable_logging'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable logging'),
    '#default_value' => variable_get('open_moreinfo_enable_logging', FALSE),
  );

}

