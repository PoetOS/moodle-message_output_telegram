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

// $telegrammanager = new message_telegram\manager();

$message = '';
if ($action == 'setwebhook') {
    require_sesskey();
    require_capability('moodle/site:config', context_system::instance());
    if (strpos($CFG->wwwroot, 'https:') !== 0) {
        $message = get_string('requirehttps', 'message_telegram');
    } else {
        if (empty(get_config('message_telegram', 'webhook'))) {
            $curl = new curl();
            $response = json_decode($curl->get('https://api.telegram.org/bot'.get_config('message_telegram', 'sitebottoken').
                '/setWebhook',
                ['url' => 'http://localhost/moodlehq.git/message/output/telegram/telegramconnect.php', 'allowed_updates' => 'message']));
            if (!empty($response) && isset($response->ok) && ($response->ok == true)) {
                set_config('webhook', '1', 'message_telegram');
            } else if (!empty($response) && isset($response->error_code) && isset($response->description)) {
                $message = $response->description;
            }
        }
    }
    redirect(new moodle_url('/admin/settings.php', ['section' => 'messagesettingtelegram']), $message);
} else if ($action == 'getUpdates') {
    require_capability('moodle/site:config', context_system::instance());
    $curl = new curl();
    $response = json_decode($curl->get('https://api.telegram.org/bot'.get_config('message_telegram', 'sitebottoken').
        '/getUpdates'));
    print_object($response);
}
