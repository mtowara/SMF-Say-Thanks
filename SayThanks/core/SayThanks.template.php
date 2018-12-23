<?php
/**
 * Say Thanks
 *
 * @package SMF
 * @author kelvincool
 * @copyright 2015 kelvincool
 * @license http://creativecommons.org/licenses/by/3.0 CC
 *
 * @version 1.3
 */

function template_saythanks()
{

}
 
function template_saythanks_js()
{
	global $settings;
	return '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/saythanks.js?fin122"></script>';
}

function template_saythanks_default($thank)
{
	global $context, $txt, $scripturl, $settings;
	return '<a href="' . $scripturl . '?action=thank;msg=' . $thank['id'] . ';member=' . $thank['id_member'] . ';topic=' . $context['current_topic'] . (!empty($context['saythanks_refresh']) && $context['saythanks_refresh'] == $thank['id'] ? ';refresh=1' : '') . '" class="thank_you_button_link"><img src="' . $settings['images_url'] . '/theme/thank.png" alt="' . $txt['saythanks_text'] . '" align="middle" /><strong>' . $txt['saythanks_text'] . '</strong></a>';
}

function template_saythanks_ajax_success()
{
	global $context, $txt, $settings;
	return '<a href="#"><img src="' . $settings['images_url'] . '/thanks_thanked.png" alt="' . $txt['saythanks_ajax_success'] . '" align="middle" /><strong>' . $txt['saythanks_ajax_success'] . '</strong></a>';
}

function template_saythanks_ajax_error()
{
	global $context, $txt, $settings;
	return '<a href="#"><img src="' . $settings['images_url'] . '/thanks_error.png" alt="' . $txt['saythanks_ajax_error'] . '" align="middle" /><strong>' . $txt['saythanks_ajax_error'] . '</strong></a>';
}

function template_saythanks_ajax_loading()
{
	global $context, $txt, $settings;
	return '<a href="#"><img src="' . $settings['images_url'] . '/thanks_loading.gif" alt="' . $txt['saythanks_ajax_loading'] . '" align="middle" /><strong>' . $txt['saythanks_ajax_loading'] . '</strong></a>';
}

function template_saythanks_ajax_guest()
{
	global $context, $txt;
	return $txt['saythanks_ajax_guest'];
}

function template_saythanks_withdraw($thank)
{
	global $context, $txt, $scripturl, $settings;
	return '<a href="' . $scripturl . '?action=withdrawthank;msg=' . $thank['id'] . ';member=' . $thank['id_member'] . ';topic=' . $context['current_topic'] . (!empty($context['saythanks_refresh']) && $context['saythanks_refresh'] == $thank['id'] ? ';refresh=1' : '') . '" class="withdraw_thank_you_button_link"><img src="' . $settings['images_url'] . '/theme/thank.png" alt="' . $txt['saythanks_withdraw_thanks'] . '" align="middle" /><strong>' . $txt['saythanks_withdraw_thanks'] . '</strong></a>';
}

function template_saythanks_above()
{

}

function template_saythanks_below()
{
	echo '
				<div id="thanks_error" class="thank_you_hidden">
					', template_saythanks_ajax_error(), '
				</div>
				<div id="thanks_loading" class="thank_you_hidden">
					', template_saythanks_ajax_loading(), '
				</div>';
	echo '
		<script type="text/javascript"><!-- // --><![CDATA[
			var o_SayThanks = new saythanks();
		// ]]></script>';
}

function template_saythanks_thanker_list($message, $list) {
	global $txt;
	return '
						<div id="thanker_list_' . $message['id'] . '" class="thanks smalltext"><span>' . $txt['saythanks_thanked'] . '</span>' . $list . '</div>';
}

function template_saythanks_thanker_separator()
{
	return '<span class="thanker_separator">, </span>';
}

function template_saythanks_thanker($message)
{
	global $scripturl;
	return '<a href="' . $scripturl . '?action=profile;u=' . $message['id_member'] . '" id="thanker_' . $message['id_msg'] . '_' . $message['id_member'] . '">'  . $message['member_name'] . '</a>';
}

function template_saythanks_current_thanker($message)
{
	global $scripturl;
	return '<a href="' . $scripturl . '?action=profile;u=' . $message['id_member'] . '" id="thanker_' . $message['id_msg'] . '_' . $message['id_member'] . '"><strong>' . $message['member_name'] . '</strong></a>';
}
?>