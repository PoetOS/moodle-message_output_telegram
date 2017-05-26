<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Telegram connection handler.
 *
 * @package message_telegram
 * @author  Mike Churchward
 * @copyright  2017 onwards Mike Churchward (mike.churchward@poetgroup.org)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/lib/filelib.php');

$action = optional_param('action', 'setwebhook', PARAM_TEXT);

$PAGE->set_url(new moodle_url('/message/output/telegram/telegramconnect.php'));
$PAGE->set_context(context_system::instance());

require_login();

$telegrammanager = new message_telegram\manager();

if ($action == 'setwebhook') {
    require_sesskey();
    require_capability('moodle/site:config', context_system::instance());
    if (strpos($CFG->wwwroot, 'https:') !== 0) {
        $message = get_string('requirehttps', 'message_telegram');
    } else {
        if (empty(get_config('message_telegram', 'webhook'))) {
            $message = $telegrammanager->set_webhook();
        }
    }
    redirect(new moodle_url('/admin/settings.php', ['section' => 'messagesettingtelegram']), $message);

} else if ($action == 'removechatid') {
    require_sesskey();
    $userid = optional_param('userid', 0, PARAM_INT);
    if ($userid != 0) {
        $message = $telegrammanager->remove_chatid($userid);
    }
    redirect(new moodle_url('/admin/settings.php', ['section' => 'messagesettingtelegram']), $message);

} else if ($action == 'getUpdates') {
    // This is for debugging purposes only, and should be removed once code is final.
    require_capability('moodle/site:config', context_system::instance());
    $response = $telegrammanager->send_api_command('getUpdates');
    print_object($response);
}
