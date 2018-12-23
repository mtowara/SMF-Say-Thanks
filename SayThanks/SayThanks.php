<?php
/**
 * Say Thanks
 *
 * @package SMF
 * @author kelvincoool
 * @copyright 2015 kelvincoool
 * @license http://creativecommons.org/licenses/by/3.0 CC
 *
 * @version 1.3.4
 */

// No Direct Access!
if (!defined('SMF'))
	die('Hacking attempt...');

class SayThanks
{
	private static $thank_count = 0; // for hide content
	private static $thanked = null; // for hide content
	
	public static function thank()
	{
		global $user_info, $smcFunc, $modSettings, $context;
		
		$ajax = !empty($_REQUEST['ajax']);
		
		if ($user_info['is_guest']) {
			if ($ajax) {
				loadTemplate('SayThanks');
				$arr = array(
					'result' => 'error',
					'response' => template_saythanks_ajax_guest()
				);
				SayThanks::returnResponse($arr);
				return;
			}
			else {
				redirectexit('action=login');
			}
			return;
		}
		$msg = intval($_REQUEST['msg']);
		$topic = intval($_REQUEST['topic']);
		$member = intval($_REQUEST['member']);
		
		if (empty($msg) || empty($topic) || empty($member) || (!empty($msg) && SayThanks::isPostOwner($msg))) {
			if ($ajax) {
				loadTemplate('SayThanks');
				$arr = array(
					'result' => 'error',
					'response' => template_saythanks_ajax_error()
				);
				SayThanks::returnResponse($arr);
				return;
			}
			else {
				redirectexit('');
			}
			return;
		}
		$smcFunc['db_insert']('ignore' ,
			'{db_prefix}messages_thanks',
			array(
				'id_msg' => 'int', 'id_member' => 'int'
			),
			array(
				$msg, $user_info['id']
			),
			array('id_msg','id_member')
		);
		if ($smcFunc['db_affected_rows']() > 0) {
			$thank_stats = $smcFunc['db_query']('', '
				SELECT s.thanks_count
				FROM {db_prefix}messages_thanks_stats s
				WHERE s.id_member = {int:id_member}',
				array(
					'id_member' => $member,
				)
			);

			$thank_count = 1;
			if ($smcFunc['db_num_rows']($thank_stats) != 0)
			{
				$thank_stat = $smcFunc['db_fetch_assoc']($thank_stats);
				$thank_count = $thank_stat['thanks_count'] + 1;
			}
			$smcFunc['db_free_result']($thank_stats);

			$smcFunc['db_insert']('replace' ,
				'{db_prefix}messages_thanks_stats',
				array(
					'id_member' => 'int', 'thanks_count' => 'int'
				),
				array(
					$member, $thank_count
				),
				array('id_member')
			);
		}

		if ($ajax) {
			$context['saythanks_refresh'] = $msg;
			$context['saythanks_thanked'] = $msg;
			loadTemplate('SayThanks');
			$message = array(
				'id_member' => $user_info['id'],
				'id' => $msg,
				'id_msg' => $msg,
				'member_name' => $user_info['name']
			);
			$arr = array(
				'thanker' => template_saythanks_thanker_separator() . template_saythanks_current_thanker($message),
				'list' => template_saythanks_thanker_list($message, template_saythanks_current_thanker($message)),
				'id' => $msg,
				'member' => $user_info['id']
			);
			if (!empty($modSettings['saythanks_withdraw_thanks_enable'])) {
				$arr['result'] = 'button';
				$thank = array(
				'id' => $msg,
				'id_member' => $member
			);
				$arr['response'] = template_saythanks_withdraw($thank);
			}
			else {
				$arr['result'] = 'success';
				$arr['response'] = template_saythanks_ajax_success();
			}
			if (!empty($_REQUEST['refresh'])) {
				$arr['refresh'] = SayThanks::getPostContent($msg);
			}
			if ($context['user']['language'] == 'russian') {
				$arr['list'] = iconv('Windows-1251', 'UTF-8', $arr['list']);
				$arr['response'] = iconv('Windows-1251', 'UTF-8', $arr['response']);
			}
			SayThanks::returnResponse($arr);
			return;
		}
		else {
			redirectexit('topic=' . $topic . '.msg' . $msg . '#msg' . $msg);
		}
	}
	
	public static function withdrawthank()
	{
		global $user_info, $smcFunc, $modSettings, $context;
		
		$ajax = !empty($_REQUEST['ajax']);
		
		if ($user_info['is_guest']) {
			if ($ajax) {
				loadTemplate('SayThanks');
				$arr = array(
					'result' => 'error',
					'response' => template_saythanks_ajax_guest()
				);
				SayThanks::returnResponse($arr);
				return;
			}
			else {
				redirectexit('action=login');
			}
			return;
		}
		$msg = intval($_REQUEST['msg']);
		$topic = intval($_REQUEST['topic']);
		$member = intval($_REQUEST['member']);
		
		if (empty($msg) || empty($topic) || empty($member) || $modSettings['saythanks_withdraw_thanks_enable'] != 1 || (!empty($msg) && SayThanks::isPostOwner($msg))) {
			if ($ajax) {
				loadTemplate('SayThanks');
				$arr = array(
					'result' => 'error',
					'response' => template_saythanks_ajax_error()
				);
				SayThanks::returnResponse($arr);
				return;
			}
			else {
				redirectexit('');
			}
			return;
		}
		
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}messages_thanks
			WHERE id_msg = {int:id_msg} AND id_member = {int:id_member}',
			array(
				'id_msg' => $msg,
				'id_member' => $user_info['id']
			)
		);
		if ($smcFunc['db_affected_rows']() > 0) {
			$thank_stats = $smcFunc['db_query']('', '
				SELECT s.thanks_count
				FROM {db_prefix}messages_thanks_stats s
				WHERE s.id_member = {int:id_member}',
				array(
					'id_member' => $member,
				)
			);

			$thank_count = 0;
			if ($smcFunc['db_num_rows']($thank_stats) != 0)
			{
				$thank_stat = $smcFunc['db_fetch_assoc']($thank_stats);
				$thank_count = $thank_stat['thanks_count'] - 1;
			}
			$smcFunc['db_free_result']($thank_stats);

			$smcFunc['db_insert']('replace' ,
				'{db_prefix}messages_thanks_stats',
				array(
					'id_member' => 'int', 'thanks_count' => 'int'
				),
				array(
					$member, $thank_count
				),
				array('id_member')
			);
		}
		if ($ajax) {
			loadTemplate('SayThanks');
			$thank = array(
				'id' => $msg,
				'id_member' => $member
			);
			$arr = array(
				'result' => 'button',
				'response' => template_saythanks_default($thank),
				'thanker' => '',
				'id' => $msg,
				'member' => $user_info['id']
			);
			if ($context['user']['language'] == 'russian') {
				$arr['response'] = iconv('Windows-1251', 'UTF-8', $arr['response']);
			}
			SayThanks::returnResponse($arr);
			return;
		}
		else {
			redirectexit('topic=' . $topic . '.msg' . $msg . '#msg' . $msg);
		}
	}
	
	public static function returnResponse($arr) {
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode($arr);
		obExit(false);
	}

	public static function addAction(&$actionArray)
	{
		$actionArray['thank'] = array('SayThanks.php', 'SayThanks::thank');
		$actionArray['withdrawthank'] = array('SayThanks.php', 'SayThanks::withdrawthank');
	}
	
	public static function loadTheme()
	{
		global $context;
		
		loadTemplate('SayThanks', 'saythanks');
		// viewing a topic
		if (!empty($context['current_topic']) && !isset($_REQUEST['xml'])) {
			$context['template_layers'][] = 'saythanks';
			$context['html_headers'] .= template_saythanks_js();
		}

		if (!empty($context['current_action']) && ($context['current_action'] == 'thank' || $context['current_action'] == 'withdrawthank')) {
			$ajax = !empty($_REQUEST['ajax']);
			if ($ajax) {
				$context['template_layers'] = array();
				$context['sub_template'] = 'saythanks';
			}
		}
	}
	
	public static function loadButtons()
	{
		global $messages_request, $smcFunc, $user_info, $context, $txt, $scripturl;
		loadTemplate('SayThanks', 'saythanks');
		$postIds = array();
		while($message = $smcFunc['db_fetch_assoc']($messages_request))
		{
			if (!$message)
			{
				$smcFunc['db_free_result']($messages_request);
			}
			$postIds[] = $message['id_msg'];
		}
		$thank_yous = $smcFunc['db_query']('', '
			SELECT
				m.id_msg, u.id_member, u.real_name as member_name
			FROM {db_prefix}messages m, {db_prefix}messages_thanks t, {db_prefix}members u
			WHERE m.id_msg IN ({array_int:message_list}) AND m.id_msg = t.id_msg AND t.id_member = u.id_member',
			array(
				'message_list' => $postIds,
			)
		);
		$thanks = array();
		$context['message_thanks'] = array();
		while($thank = $smcFunc['db_fetch_assoc']($thank_yous))
		{
			if (!$thank)
			{
				$smcFunc['db_free_result']($thank_yous);
			}
			if (!isset($context['message_thanks'][$thank['id_msg']]))
			{
				$context['message_thanks'][$thank['id_msg']] = array(); 
				$context['message_thanks'][$thank['id_msg']]['list'] = '';
				$context['message_thanks'][$thank['id_msg']]['users'] = array();
			}
			$context['message_thanks'][$thank['id_msg']]['list'] .= $context['message_thanks'][$thank['id_msg']]['list'] == '' ? '' : template_saythanks_thanker_separator();
			if ($thank['id_member'] == $user_info['id'])
			{
				$context['message_thanks'][$thank['id_msg']]['list'] .= template_saythanks_current_thanker($thank);
			}
			else
			{
				$context['message_thanks'][$thank['id_msg']]['list'] .= template_saythanks_thanker($thank);
			}
			$context['message_thanks'][$thank['id_msg']]['users'][] = $thank['id_member'];
		}
		@$smcFunc['db_data_seek']($messages_request, 0); // go back to beginning
	}
	
	public static function setManagement(&$subActions) {
		$subActions['saythanks'] = 'ModifySayThanksSettings';
	}
	
	public static function setAdminArea(&$admin_areas) {
		global $txt;
		$admin_areas['config']['areas']['modsettings']['subsections']['saythanks'] = array($txt['saythanks_settings']);
	}
	
	public static function setProfileArea(&$profile_areas) {
		global $txt;
		$profile_areas['info']['areas']['showposts']['subsections']['thanked'] = array($txt['saythanks_show_thanked_posts'], array('profile_view_own', 'profile_view_any'));
		$profile_areas['info']['areas']['showposts']['subsections']['thank'] = array($txt['saythanks_show_thank_by_user_posts'], array('profile_view_own', 'profile_view_any'));
	}
	
	public static function showThankedPosts($memID)
	{
		global $txt, $user_info, $scripturl, $modSettings;
		global $db_type, $context, $user_profile, $sourcedir, $smcFunc, $board;

		// Default to 10.
		if (empty($_REQUEST['viewscount']) || !is_numeric($_REQUEST['viewscount']))
			$_REQUEST['viewscount'] = '10';

		
		$request = $smcFunc['db_query']('', '
			SELECT count(distinct(m.id_msg))
			FROM {db_prefix}messages AS m
				INNER JOIN {db_prefix}messages_thanks as mt ON (mt.id_msg = m.id_msg)' . ($user_info['query_see_board'] == '1=1' ? '' : '
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board AND {query_see_board})') . '
			WHERE m.id_member = {int:current_member}' . (!empty($board) ? '
				AND m.id_board = {int:board}' : '') . (!$modSettings['postmod_active'] || $context['user']['is_owner'] ? '' : '
				AND m.approved = {int:is_approved}'),
			array(
				'current_member' => $memID,
				'is_approved' => 1,
				'board' => $board,
			)
		);
		list ($msgCount) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		
		$request = $smcFunc['db_query']('', '
			SELECT MIN(m.id_msg), MAX(m.id_msg)
			FROM {db_prefix}messages AS m
				LEFT JOIN {db_prefix}messages_thanks as mt ON (mt.id_msg = m.id_msg)
			WHERE m.id_member = {int:current_member}' . (!empty($board) ? '
				AND m.id_board = {int:board}' : '') . (!$modSettings['postmod_active'] || $context['user']['is_owner'] ? '' : '
				AND m.approved = {int:is_approved}'),
			array(
				'current_member' => $memID,
				'is_approved' => 1,
				'board' => $board,
			)
		);
		list ($min_msg_member, $max_msg_member) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		$reverse = false;
		$range_limit = '';
		$maxIndex = (int) $modSettings['defaultMaxMessages'];

		// Make sure the starting place makes sense and construct our friend the page index.
		$context['page_index'] = constructPageIndex($scripturl . '?action=profile;u=' . $memID . ';area=showposts;sa=thanked' . (!empty($board) ? ';board=' . $board : ''), $context['start'], $msgCount, $maxIndex);
		$context['current_page'] = $context['start'] / $maxIndex;

		// Reverse the query if we're past 50% of the pages for better performance.
		$start = $context['start'];
		$reverse = $_REQUEST['start'] > $msgCount / 2;
		if ($reverse)
		{
			$maxIndex = $msgCount < $context['start'] + $modSettings['defaultMaxMessages'] + 1 && $msgCount > $context['start'] ? $msgCount - $context['start'] : (int) $modSettings['defaultMaxMessages'];
			$start = $msgCount < $context['start'] + $modSettings['defaultMaxMessages'] + 1 || $msgCount < $context['start'] + $modSettings['defaultMaxMessages'] ? 0 : $msgCount - $context['start'] - $modSettings['defaultMaxMessages'];
		}

		// Guess the range of messages to be shown.
		if ($msgCount > 1000)
		{
			$margin = floor(($max_msg_member - $min_msg_member) * (($start + $modSettings['defaultMaxMessages']) / $msgCount) + .1 * ($max_msg_member - $min_msg_member));
			// Make a bigger margin for topics only.
			if ($context['is_topics'])
			{
				$margin *= 5;
				$range_limit = $reverse ? 't.id_first_msg < ' . ($min_msg_member + $margin) : 't.id_first_msg > ' . ($max_msg_member - $margin);
			}
			else
				$range_limit = $reverse ? 'm.id_msg < ' . ($min_msg_member + $margin) : 'm.id_msg > ' . ($max_msg_member - $margin);
		}

		// Find this user's posts.  The left join on categories somehow makes this faster, weird as it looks.
		$looped = false;
		if ($db_type == 'postgresql') {
			$concat = "string_agg('*' || m2.id_member::text || '**' || m2.real_name::text || '***', ', ') as thank_list";
		}
		else if ($db_type == 'sqlite') {
			$concat = "GROUP_CONCAT('*' || m2.id_member || '**' || m2.real_name || '***', ', ') as thank_list";
		}
		else {
			$concat = "GROUP_CONCAT(CONCAT('*', m2.id_member, '**', m2.real_name, '***') separator ', ') as thank_list";
		}
		while (true)
		{
			$request = $smcFunc['db_query']('', '
				SELECT
					b.id_board, b.name AS bname, c.id_cat, c.name AS cname, m.id_topic, m.id_msg,
					t.id_member_started, t.id_first_msg, t.id_last_msg, m.body, m.smileys_enabled,
					m.subject, m.poster_time, m.approved, ' . $concat . ', count(m2.id_member) as total
				FROM {db_prefix}messages AS m
					INNER JOIN {db_prefix}messages_thanks as mt ON (mt.id_msg = m.id_msg)
					INNER JOIN {db_prefix}members as m2 ON (mt.id_member = m2.id_member)
					INNER JOIN {db_prefix}topics AS t ON (t.id_topic = m.id_topic)
					INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
					LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
				WHERE m.id_member = {int:current_member}' . (!empty($board) ? '
					AND b.id_board = {int:board}' : '') . (empty($range_limit) ? '' : '
					AND ' . $range_limit) . '
					AND {query_see_board}' . (!$modSettings['postmod_active'] || $context['user']['is_owner'] ? '' : '
					AND t.approved = {int:is_approved} AND m.approved = {int:is_approved}') . '
				GROUP BY m.id_msg
				ORDER BY m.id_msg ' . ($reverse ? 'ASC' : 'DESC') . '
				LIMIT ' . $start . ', ' . $maxIndex,
				array(
					'current_member' => $memID,
					'is_approved' => 1,
					'board' => $board,
				)
			);

			// Make sure we quit this loop.
			if ($smcFunc['db_num_rows']($request) === $maxIndex || $looped)
				break;
			$looped = true;
			$range_limit = '';
		}

		// Start counting at the number of the first message displayed.
		$counter = $reverse ? $context['start'] + $maxIndex + 1 : $context['start'];
		$context['posts'] = array();
		$board_ids = array('own' => array(), 'any' => array());
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			// Censor....
			censorText($row['body']);
			censorText($row['subject']);

			// Do the code.
			$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);
			
			$has_more = false;
			// Check if thank list has been truncated
			if (substr($row['thank_list'], -3, 3) != '***') {
				$pos = strripos($row['thank_list'], ',');
				$diff = strlen($row['thank_list']) - $pos;
				// Shift to the last whole name
				$row['thank_list'] = substr($row['thank_list'], 0, -($diff));
				$has_more = true;
			}
			// Check if list total matches comma total
			if (!$has_more) {
				$total_commas = substr_count($row['thank_list'], ',');
				if ($total_commas < ($row['total'] - 1)) {
					$has_more = true;
				}
			}
			if ($has_more) {
				$row['thank_list'] = $row['thank_list'] . ' ...';
			}
			$row['thank_list'] = str_replace(array('***','**','*'),array('</a>','">','<a href="' . $scripturl . '?action=profile;u='), $row['thank_list']);

			// And the array...
			$context['posts'][$counter += $reverse ? -1 : 1] = array(
				'body' => $row['body'],
				'counter' => $counter,
				'alternate' => $counter % 2,
				'category' => array(
					'name' => $row['cname'],
					'id' => $row['id_cat']
				),
				'board' => array(
					'name' => $row['bname'],
					'id' => $row['id_board']
				),
				'topic' => $row['id_topic'],
				'subject' => $row['subject'],
				'start' => 'msg' . $row['id_msg'],
				'time' => timeformat($row['poster_time']),
				'timestamp' => forum_time(true, $row['poster_time']),
				'id' => $row['id_msg'],
				'can_reply' => false,
				'can_mark_notify' => false,
				'can_delete' => false,
				'delete_possible' => ($row['id_first_msg'] != $row['id_msg'] || $row['id_last_msg'] == $row['id_msg']) && (empty($modSettings['edit_disable_time']) || $row['poster_time'] + $modSettings['edit_disable_time'] * 60 >= time()),
				'approved' => $row['approved'],
				'thank_list' => $row['thank_list'],
			);
			
			if ($user_info['id'] == $row['id_member_started'])
				$board_ids['own'][$row['id_board']][] = $counter;
			$board_ids['any'][$row['id_board']][] = $counter;
		}
		$smcFunc['db_free_result']($request);

		// All posts were retrieved in reverse order, get them right again.
		if ($reverse)
			$context['posts'] = array_reverse($context['posts'], true);

		// These are all the permissions that are different from board to board..
		$permissions = array(
			'own' => array(
				'post_reply_own' => 'can_reply',
				'delete_own' => 'can_delete',
			),
			'any' => array(
				'post_reply_any' => 'can_reply',
				'mark_any_notify' => 'can_mark_notify',
				'delete_any' => 'can_delete',
			)
		);

		// For every permission in the own/any lists...
		foreach ($permissions as $type => $list)
		{
			foreach ($list as $permission => $allowed)
			{
				// Get the boards they can do this on...
				$boards = boardsAllowedTo($permission);

				// Hmm, they can do it on all boards, can they?
				if (!empty($boards) && $boards[0] == 0)
					$boards = array_keys($board_ids[$type]);

				// Now go through each board they can do the permission on.
				foreach ($boards as $board_id)
				{
					// There aren't any posts displayed from this board.
					if (!isset($board_ids[$type][$board_id]))
						continue;

					// Set the permission to true ;).
					foreach ($board_ids[$type][$board_id] as $counter)
						$context['posts'][$counter][$allowed] = true;
				}
			}
		}

		// Clean up after posts that cannot be deleted and quoted.
		$quote_enabled = empty($modSettings['disabledBBC']) || !in_array('quote', explode(',', $modSettings['disabledBBC']));
		foreach ($context['posts'] as $counter => $dummy)
		{
			$context['posts'][$counter]['can_delete'] &= $context['posts'][$counter]['delete_possible'];
			$context['posts'][$counter]['can_quote'] = $context['posts'][$counter]['can_reply'] && $quote_enabled;
		}
	}
	
	public static function showThankByUserPosts($memID)
	{
		global $txt, $user_info, $scripturl, $modSettings;
		global $db_type, $context, $user_profile, $sourcedir, $smcFunc, $board;

		// Default to 10.
		if (empty($_REQUEST['viewscount']) || !is_numeric($_REQUEST['viewscount']))
			$_REQUEST['viewscount'] = '10';

		$request = $smcFunc['db_query']('', '
			SELECT count(distinct(m.id_msg))
			FROM {db_prefix}messages_thanks AS mt
				INNER JOIN {db_prefix}messages as m ON (m.id_msg = mt.id_msg)' . ($user_info['query_see_board'] == '1=1' ? '' : '
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board AND {query_see_board})') . '
			WHERE mt.id_member = {int:current_member}' . (!empty($board) ? '
				AND m.id_board = {int:board}' : '') . (!$modSettings['postmod_active'] || $context['user']['is_owner'] ? '' : '
				AND m.approved = {int:is_approved}'),
			array(
				'current_member' => $memID,
				'is_approved' => 1,
				'board' => $board,
			)
		);
		list ($msgCount) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		
		$request = $smcFunc['db_query']('', '
			SELECT MIN(m.id_msg), MAX(m.id_msg)
			FROM {db_prefix}messages_thanks AS mt
				LEFT JOIN {db_prefix}messages as m ON (m.id_msg = mt.id_msg)
			WHERE mt.id_member = {int:current_member}' . (!empty($board) ? '
				AND m.id_board = {int:board}' : '') . (!$modSettings['postmod_active'] || $context['user']['is_owner'] ? '' : '
				AND m.approved = {int:is_approved}'),
			array(
				'current_member' => $memID,
				'is_approved' => 1,
				'board' => $board,
			)
		);
		list ($min_msg_member, $max_msg_member) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		$reverse = false;
		$range_limit = '';
		$maxIndex = (int) $modSettings['defaultMaxMessages'];

		// Make sure the starting place makes sense and construct our friend the page index.
		$context['page_index'] = constructPageIndex($scripturl . '?action=profile;u=' . $memID . ';area=showposts;sa=thank' . (!empty($board) ? ';board=' . $board : ''), $context['start'], $msgCount, $maxIndex);
		$context['current_page'] = $context['start'] / $maxIndex;

		// Reverse the query if we're past 50% of the pages for better performance.
		$start = $context['start'];
		$reverse = $_REQUEST['start'] > $msgCount / 2;
		if ($reverse)
		{
			$maxIndex = $msgCount < $context['start'] + $modSettings['defaultMaxMessages'] + 1 && $msgCount > $context['start'] ? $msgCount - $context['start'] : (int) $modSettings['defaultMaxMessages'];
			$start = $msgCount < $context['start'] + $modSettings['defaultMaxMessages'] + 1 || $msgCount < $context['start'] + $modSettings['defaultMaxMessages'] ? 0 : $msgCount - $context['start'] - $modSettings['defaultMaxMessages'];
		}

		// Guess the range of messages to be shown.
		if ($msgCount > 1000)
		{
			$margin = floor(($max_msg_member - $min_msg_member) * (($start + $modSettings['defaultMaxMessages']) / $msgCount) + .1 * ($max_msg_member - $min_msg_member));
			// Make a bigger margin for topics only.
			if ($context['is_topics'])
			{
				$margin *= 5;
				$range_limit = $reverse ? 't.id_first_msg < ' . ($min_msg_member + $margin) : 't.id_first_msg > ' . ($max_msg_member - $margin);
			}
			else
				$range_limit = $reverse ? 'm.id_msg < ' . ($min_msg_member + $margin) : 'm.id_msg > ' . ($max_msg_member - $margin);
		}

		// Find this user's posts.  The left join on categories somehow makes this faster, weird as it looks.
		$looped = false;
		if ($db_type == 'postgresql') {
			$concat = "string_agg('*' || m2.id_member::text || '**' || m2.real_name::text || '***', ', ') as thank_list";
		}
		else if ($db_type == 'sqlite') {
			$concat = "GROUP_CONCAT('*' || m2.id_member || '**' || m2.real_name || '***', ', ') as thank_list";
		}
		else {
			$concat = "GROUP_CONCAT(CONCAT('*', m2.id_member, '**', m2.real_name, '***') separator ', ') as thank_list";
		}
		while (true)
		{
			$request = $smcFunc['db_query']('', '
				SELECT
					b.id_board, b.name AS bname, c.id_cat, c.name AS cname, m.id_topic, m.id_msg,
					t.id_member_started, t.id_first_msg, t.id_last_msg, m.body, m.smileys_enabled,
					m.subject, m.poster_time, m.approved, ' . $concat . ', count(m2.id_member) as total
				FROM {db_prefix}messages_thanks AS mt
					INNER JOIN {db_prefix}messages as m ON (m.id_msg = mt.id_msg)
					INNER JOIN {db_prefix}members as m2 ON (mt.id_member = m2.id_member)
					INNER JOIN {db_prefix}topics AS t ON (t.id_topic = m.id_topic)
					INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
					LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
				WHERE mt.id_member = {int:current_member}' . (!empty($board) ? '
					AND b.id_board = {int:board}' : '') . (empty($range_limit) ? '' : '
					AND ' . $range_limit) . '
					AND {query_see_board}' . (!$modSettings['postmod_active'] || $context['user']['is_owner'] ? '' : '
					AND t.approved = {int:is_approved} AND m.approved = {int:is_approved}') . '
				GROUP BY m.id_msg
				ORDER BY m.id_msg ' . ($reverse ? 'ASC' : 'DESC') . '
				LIMIT ' . $start . ', ' . $maxIndex,
				array(
					'current_member' => $memID,
					'is_approved' => 1,
					'board' => $board,
				)
			);

			// Make sure we quit this loop.
			if ($smcFunc['db_num_rows']($request) === $maxIndex || $looped)
				break;
			$looped = true;
			$range_limit = '';
		}

		// Start counting at the number of the first message displayed.
		$counter = $reverse ? $context['start'] + $maxIndex + 1 : $context['start'];
		$context['posts'] = array();
		$board_ids = array('own' => array(), 'any' => array());
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			// Censor....
			censorText($row['body']);
			censorText($row['subject']);

			// Do the code.
			$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);
			
			$has_more = false;
			// Check if thank list has been truncated
			if (substr($row['thank_list'], -3, 3) != '***') {
				$pos = strripos($row['thank_list'], ',');
				$diff = strlen($row['thank_list']) - $pos;
				// Shift to the last whole name
				$row['thank_list'] = substr($row['thank_list'], 0, -($diff));
				$has_more = true;
			}
			// Check if list total matches comma total
			if (!$has_more) {
				$total_commas = substr_count($row['thank_list'], ',');
				if ($total_commas < ($row['total'] - 1)) {
					$has_more = true;
				}
			}
			if ($has_more) {
				$row['thank_list'] = $row['thank_list'] . ' ...';
			}
			$row['thank_list'] = str_replace(array('***','**','*'),array('</a>','">','<a href="' . $scripturl . '?action=profile;u='), $row['thank_list']);

			// And the array...
			$context['posts'][$counter += $reverse ? -1 : 1] = array(
				'body' => $row['body'],
				'counter' => $counter,
				'alternate' => $counter % 2,
				'category' => array(
					'name' => $row['cname'],
					'id' => $row['id_cat']
				),
				'board' => array(
					'name' => $row['bname'],
					'id' => $row['id_board']
				),
				'topic' => $row['id_topic'],
				'subject' => $row['subject'],
				'start' => 'msg' . $row['id_msg'],
				'time' => timeformat($row['poster_time']),
				'timestamp' => forum_time(true, $row['poster_time']),
				'id' => $row['id_msg'],
				'can_reply' => false,
				'can_mark_notify' => false,
				'can_delete' => false,
				'delete_possible' => ($row['id_first_msg'] != $row['id_msg'] || $row['id_last_msg'] == $row['id_msg']) && (empty($modSettings['edit_disable_time']) || $row['poster_time'] + $modSettings['edit_disable_time'] * 60 >= time()),
				'approved' => $row['approved'],
				'thank_list' => $row['thank_list'],
			);
			
			if ($user_info['id'] == $row['id_member_started'])
				$board_ids['own'][$row['id_board']][] = $counter;
			$board_ids['any'][$row['id_board']][] = $counter;
		}
		$smcFunc['db_free_result']($request);

		// All posts were retrieved in reverse order, get them right again.
		if ($reverse)
			$context['posts'] = array_reverse($context['posts'], true);

		// These are all the permissions that are different from board to board..
		$permissions = array(
			'own' => array(
				'post_reply_own' => 'can_reply',
				'delete_own' => 'can_delete',
			),
			'any' => array(
				'post_reply_any' => 'can_reply',
				'mark_any_notify' => 'can_mark_notify',
				'delete_any' => 'can_delete',
			)
		);

		// For every permission in the own/any lists...
		foreach ($permissions as $type => $list)
		{
			foreach ($list as $permission => $allowed)
			{
				// Get the boards they can do this on...
				$boards = boardsAllowedTo($permission);

				// Hmm, they can do it on all boards, can they?
				if (!empty($boards) && $boards[0] == 0)
					$boards = array_keys($board_ids[$type]);

				// Now go through each board they can do the permission on.
				foreach ($boards as $board_id)
				{
					// There aren't any posts displayed from this board.
					if (!isset($board_ids[$type][$board_id]))
						continue;

					// Set the permission to true ;).
					foreach ($board_ids[$type][$board_id] as $counter)
						$context['posts'][$counter][$allowed] = true;
				}
			}
		}

		// Clean up after posts that cannot be deleted and quoted.
		$quote_enabled = empty($modSettings['disabledBBC']) || !in_array('quote', explode(',', $modSettings['disabledBBC']));
		foreach ($context['posts'] as $counter => $dummy)
		{
			$context['posts'][$counter]['can_delete'] &= $context['posts'][$counter]['delete_possible'];
			$context['posts'][$counter]['can_quote'] = $context['posts'][$counter]['can_reply'] && $quote_enabled;
		}
	}
	
	public static function setStats() {
		global $context, $smcFunc, $scripturl;
		global $board, $modSettings, $user_info;
		
			// Thanked poster top 10.
		$thanked_result = $smcFunc['db_query']('top_thanked', '
			SELECT s.id_member, m.real_name, s.thanks_count
			FROM {db_prefix}messages_thanks_stats s, {db_prefix}members m
			WHERE s.id_member = m.id_member
			ORDER BY s.thanks_count DESC
			LIMIT 10'
		);
		$context['top_thanked'] = array();
		$max_thanked = 1;
		while ($row_members = $smcFunc['db_fetch_assoc']($thanked_result))
		{
			$context['top_thanked'][] = array(
				'name' => $row_members['real_name'],
				'id' => $row_members['id_member'],
				'thanked_count' => $row_members['thanks_count'],
				'href' => $scripturl . '?action=profile;u=' . $row_members['id_member'],
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row_members['id_member'] . '">' . $row_members['real_name'] . '</a>'
			);

			if ($max_thanked < $row_members['thanks_count'])
				$max_thanked = $row_members['thanks_count'];
		}
		$smcFunc['db_free_result']($thanked_result);

		foreach ($context['top_thanked'] as $i => $member)
		{
			$context['top_thanked'][$i]['thanked_percent'] = round(($member['thanked_count'] * 100) / $max_thanked);
			$context['top_thanked'][$i]['thanked_count'] = comma_format($context['top_thanked'][$i]['thanked_count']);
		}
		
		// Thanks giver top 10.
		/*$thanker_result = $smcFunc['db_query']('top_thanker', '
			SELECT s.id_member, m.real_name, count(s.id_member) as thanks_count
			FROM {db_prefix}messages_thanks s, {db_prefix}members m
			WHERE s.id_member = m.id_member
			GROUP BY s.id_member
			ORDER BY count(s.id_member) DESC
			LIMIT 10'
		);
		$context['top_thanker'] = array();
		$max_thanks = 1;
		while ($row_members = $smcFunc['db_fetch_assoc']($thanker_result))
		{
			$context['top_thanker'][] = array(
				'name' => $row_members['real_name'],
				'id' => $row_members['id_member'],
				'thanks_count' => $row_members['thanks_count'],
				'href' => $scripturl . '?action=profile;u=' . $row_members['id_member'],
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row_members['id_member'] . '">' . $row_members['real_name'] . '</a>'
			);

			if ($max_thanks < $row_members['thanks_count'])
				$max_thanks = $row_members['thanks_count'];
		}
		$smcFunc['db_free_result']($thanker_result);

		foreach ($context['top_thanker'] as $i => $member)
		{
			$context['top_thanker'][$i]['thanks_percent'] = round(($member['thanks_count'] * 100) / $max_thanks);
			$context['top_thanker'][$i]['thanks_count'] = comma_format($context['top_thanker'][$i]['thanks_count']);
		}*/
		
		// Thanked posts top 10.
		$top_thanked_posts = $smcFunc['db_query']('top_thanked_posts', '
			SELECT m.subject, m.id_topic, m.id_msg, count(m.id_msg) as thanks_count
			FROM {db_prefix}messages_thanks mt, {db_prefix}messages m' . ($user_info['query_see_board'] == '1=1' ? '' : '
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board AND {query_see_board})') . '
			WHERE mt.id_msg = m.id_msg' . (!empty($board) ? '
			AND m.id_board = {int:board}' : '') . (!$modSettings['postmod_active'] ? '' : '
				AND m.approved = {int:is_approved}') . '
			GROUP BY m.id_msg
			ORDER BY count(m.id_msg) DESC
			LIMIT 10',
			array(
				'is_approved' => 1,
				'board' => $board
			)
		);
		$context['top_thanked_posts'] = array();
		$max_thanks = 1;
		while ($row_posts = $smcFunc['db_fetch_assoc']($top_thanked_posts))
		{
			$context['top_thanked_posts'][] = array(
				'thanks_count' => $row_posts['thanks_count'],
				'link' => '<a href="' . $scripturl . '?topic=' . $row_posts['id_topic'] .'.msg' . $row_posts['id_msg'] . '#msg' . $row_posts['id_msg'] . '">' . $row_posts['subject'] . '</a>'
			);

			if ($max_thanks < $row_posts['thanks_count'])
				$max_thanks = $row_posts['thanks_count'];
		}
		$smcFunc['db_free_result']($top_thanked_posts);

		foreach ($context['top_thanked_posts'] as $i => $member)
		{
			$context['top_thanked_posts'][$i]['thanks_percent'] = round(($member['thanks_count'] * 100) / $max_thanks);
			$context['top_thanked_posts'][$i]['thanks_count'] = comma_format($context['top_thanked_posts'][$i]['thanks_count']);
		}
		
	}
	
	public static function checkCanThank($message) {
		global $context, $user_info, $modSettings;
		// handle topic page
		if (!empty($context['current_board'])) {
			if (!empty($modSettings['st_disable_on_boards'])) {
					$boardsDisabled = unserialize($modSettings['st_disable_on_boards']);
					if (in_array($context['current_board'], $boardsDisabled)) {
						return false;
					}
			}
		}
		return ($user_info['is_guest'] == false && $message['member']['id'] && array_key_exists('message_thanks', $context) && $user_info['id'] != $message['member']['id']);
	}
	
	public static function checkIfAlreadyThanked($message) {
		global $context, $user_info;
		$context['message_thanks'] = !empty($context['message_thanks']) ? $context['message_thanks'] : array();
		return (!array_key_exists($message['id'], $context['message_thanks']) || (array_key_exists($message['id'], $context['message_thanks']) && !in_array($user_info['id'], $context['message_thanks'][$message['id']]['users'])));
	}
	
	public static function checkCanWithdrawThanks($message) {
		global $context, $user_info, $modSettings;
		return (!empty($modSettings['saythanks_withdraw_thanks_enable']) && (array_key_exists($message['id'], $context['message_thanks']) && in_array($user_info['id'], $context['message_thanks'][$message['id']]['users'])));
	}
	
	public static function isPostOwner($msg_id) {
		global $smcFunc, $user_info;
		$is_owner = false;
		$result = $smcFunc['db_query']('', '
			SELECT id_msg FROM {db_prefix}messages
			WHERE id_msg = {int:id_msg}
			AND id_member = {int:id_member}',
			array(
				'id_msg' => $msg_id,
				'id_member' => $user_info['id'],
			)
		);
		if ($smcFunc['db_num_rows']($result) > 0) {
			$is_owner = true;
		}
		$smcFunc['db_free_result']($result);
		return $is_owner;
	}
	
	/*
	* Start of hide content by thank count
	*/
	public static function reset() {
		self::$thank_count = 0;
	}
	
	public static function setThankCount($count) {
		self::$thank_count = intval($count);
	}
	
	public static function getThankCount() {
		return self::$thank_count;
	}
	
	public static function getPluginInfo(&$plugins) {
		global $txt;
		$plugins['thank'] = array(
			'title' => $txt['saythanks_title'], 
			'code' => 'thank', 
			'default_value' => 1, 
			'description' => $txt['saythanks_desc'], 
			'evaluate' => 'SayThanks::evaluateCondition', 
			'options' => 'SayThanks::getOptions',
			'reset' => 'SayThanks::reset'
		);
		$plugins['thanked'] = array(
			'title' => $txt['saythanks_thanked_title'], 
			'code' => 'thanked', 
			'default_value' => 1, 
			'description' => $txt['saythanks_thanked_desc'], 
			'evaluate' => 'SayThanks::evaluateThankedCondition', 
			'options' => 'SayThanks::getThankedOptions',
			'reset' => 'SayThanks::resetThanked'
		);
	}
	
	public static function setParameter(&$parameters) {
		$parameters['thank'] = array(
			'optional' => true, 
			'match' => '(\d+)', 
			'validate' => create_function('&$data', 'SayThanks::setThankCount($data);')
		);
		$parameters['thanked'] = array(
			'optional' => true, 
			'match' => '(\d+)', 
			'validate' => create_function('&$data', 'SayThanks::setThanked($data);')
		);
	}
	
	public static function evaluateCondition() {
		global $txt, $user_info;
		$result = '';
		$thanks_needed = SayThanks::getThankCount();
		if ($thanks_needed === 0) {
			return $result;
		}
		if ($thanks_needed > $user_info["thanks"] || $user_info["is_guest"]) {
			$thanks_left = $thanks_needed - $user_info["thanks"];
			$result = strtr($txt['saythanks_hide_text'], array(
				'{thanks_needed}' => '<strong>' . $thanks_needed . '</strong>',
				'{thanks_left}' => '<strong>' . $thanks_left . '</strong>',
			));
		}
		return $result;
	}
	
	public static function getOptions() {
		global $txt;
		$options = array(
			'type' => 'text', 
			'title' => $txt['saythanks_input_title'], 
			'id' => 'thank_count', 
			'default_value' => 1, 
			'class' => 'numeric'
		);
		return $options;
	}
	
	/*
	* Start of hide content by thank
	*/
	public static function resetThanked() {
		self::$thanked = null;
	}
	
	public static function setThanked($data) {
		$thanked = true;
		if (intval($data) == 0) {
			$thanked = false;
		}
		self::$thanked = $thanked;
	}
	
	public static function getThanked() {
		return self::$thanked;
	}
	
	public static function evaluateThankedCondition() {
		global $txt, $context;
		$result = '';
		$thanked_needed = SayThanks::getThanked();
		if ($thanked_needed == 1) {
			if (!empty($context['hc_current_post'])) {
				$message['id'] = $context['hc_current_post']['id_msg'];
				$context['saythanks_refresh'] = $message['id'];
				if (SayThanks::checkIfAlreadyThanked($message)) {
					$result = $txt['saythanks_hide_thanked_text'];
				}
			}
			else if (empty($context['saythanks_thanked'])) {
				$result = $txt['saythanks_hide_thanked_text'];
			}
			unset($context['saythanks_thanked']);
		}
		return $result;
	}
	
	public static function getThankedOptions() {
		global $txt;
		$options = array(
			'type' => 'hidden', 
			'title' => '', 
			'id' => 'thanked_input', 
			'default_value' => 1
		);
		return $options;
	}
	
	public static function getPostContent($id) {
		global $modSettings, $smcFunc;
		$form_message = '';
		// Make sure they _can_ quote this post, and if so get it.
		$request = $smcFunc['db_query']('', '
			SELECT m.body
			FROM {db_prefix}messages AS m
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board AND {query_see_board})
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
			WHERE m.id_msg = {int:id_msg}' . (!$modSettings['postmod_active'] || allowedTo('approve_posts') ? '' : '
				AND m.approved = {int:is_approved}') . '
			LIMIT 1',
			array(
				'id_msg' => $id,
				'is_approved' => 1,
			)
		);
		if ($smcFunc['db_num_rows']($request) != 0) {
			list ($form_message) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
			// Censor the message
			censorText($form_message);
			$form_message = parse_bbc($form_message);
		}
		return $form_message;
	}
}