<?php
/**
 * Say Thanks
 *
 * @package SMF
 * @author kelvincoool
 * @copyright 2014 kelvincoool
 * @license http://creativecommons.org/licenses/by/3.0 CC
 *
 * @version 1.2
 */

// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (!defined('SMF') && file_exists(dirname(__FILE__) . '/SSI.php'))
	require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc, $db_prefix, $modSettings, $sourcedir;

///////////////////////////////////////////
// Add table for storing thank you's
db_extend('packages');

// Create the thanks table
$columns = array(
	array(
		'name' => 'id_msg',
		'type' => 'int',
	),
	array(
		'name' => 'id_member',
		'type' => 'mediumint',
	)
);
$indexes = array(
	array(
		'type' => 'primary',
		'columns' => array('id_msg', 'id_member')
	)
);
$smcFunc['db_create_table']('{db_prefix}messages_thanks', $columns, $indexes);

// Create the stats table
$columns = array(
	array(
		'name' => 'id_member',
		'type' => 'mediumint',
	),
	array(
		'name' => 'thanks_count',
		'type' => 'mediumint',
	)
);
$indexes = array(
	array(
		'type' => 'primary',
		'columns' => array('id_member')
	)
);
$smcFunc['db_create_table']('{db_prefix}messages_thanks_stats', $columns, $indexes);

// Add hooks (for 2.0)
$sef_functions = array(
	'integrate_load_theme' => 'SayThanks::loadTheme',
	'integrate_display_buttons' => 'SayThanks::loadButtons',
	'integrate_pre_include' => $sourcedir . '/SayThanks.php',
	'integrate_actions' => 'SayThanks::addAction',
	'integrate_modify_modifications' => 'SayThanks::setManagement',
	'integrate_admin_areas' => 'SayThanks::setAdminArea',
	'integrate_profile_areas' => 'SayThanks::setProfileArea',
	'integrate_hide_content_implement_parameter' => 'SayThanks::setParameter',
	'integrate_hide_content_plugin_info' => 'SayThanks::getPluginInfo',
);

foreach ($sef_functions as $hook => $function)
	add_integration_function($hook, $function, TRUE);