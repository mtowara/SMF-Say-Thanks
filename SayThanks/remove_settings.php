<?php
/**
 * Say Thanks
 *
 * @package SMF
 * @author kelvincoool
 * @copyright 2014 kelvincoool
 * @license http://creativecommons.org/licenses/by/3.0 CC
 *
 * @version 1.3
 */

// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
db_extend('packages');

$sef_functions = array(
	'integrate_display_buttons' => 'SayThanks::loadTheme',
	'integrate_pre_include' => $sourcedir . '/SayThanks.php',
	'integrate_actions' => 'SayThanks::addAction',
	'integrate_modify_modifications' => 'SayThanks::setManagement',
	'integrate_admin_areas' => 'SayThanks::setAdminArea',
	'integrate_profile_areas' => 'SayThanks::setProfileArea',
	'integrate_hide_content_implement_parameter' => 'SayThanks::setParameter',
	'integrate_hide_content_plugin_info' => 'SayThanks::getPluginInfo',
);
	// Remove hooks (for 2.0)
	foreach ($sef_functions as $hook => $function)
		remove_integration_function($hook, $function);
		
remove_integration_function('integrate_profile_areas', 'SayThanks::loadProfile'); // tidy up legacy function from 1.0.2

if (!empty($_POST['do_db_changes'])) {
	global $smcFunc;
	db_extend('packages');
	
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}settings
		WHERE variable like {string:variable1} or variable like {string:variable2}',
		array(
			'variable1' => '%saythanks%',
			'variable2' => 'st_disable_on_boards',
		)
	);
}