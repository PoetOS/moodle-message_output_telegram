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
 * Telegram message plugin settings.
 *
 * @package message_telegram
 * @author  Mike Churchward
 * @copyright  2017 onwards Mike Churchward (mike.churchward@poetgroup.org)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $telegrammanager = new message_telegram\manager();

    $sitebottoken = $telegrammanager->config('sitebottoken');
    $botname = $telegrammanager->config('sitebotname');
    $botusername = $telegrammanager->config('sitebotusername');

    if (!empty($sitebottoken)) {
        $telegrammanager->update_bot_info();
    }

    $telegrammanager = new message_telegram\manager();
    if (empty($sitebottoken)) {
        $site = get_site();
        $uniquename = $site->fullname . ' ' . get_string('notifications');
        $sitehostname = parse_url($CFG->wwwroot, PHP_URL_HOST);
        $lastdot = strrpos($sitehostname, '.');
        if ($lastdot !== false) {
            $sitehostname = substr($sitehostname, 0, $lastdot);
        }
        $botusername = strrchr($sitehostname, '.');
        if ($botusername === false) {
            $botusername = $sitehostname;
        } else {
            $botusername = str_replace('.', '', $botusername);
        }
        // The username cannot be longer than 32 characters total, and must end in "bot".
        $botusername = substr($botusername, 0, 29) . 'Bot';

        $url = 'https://telegram.me/botfather';
        $link = '<p><a href="'.$url.'" target="_blank">'.$url.'</a></p>';
        $a = new stdClass();
        $a->name = $uniquename;
        $a->username = $botusername;
        $text = get_string('setupinstructions', 'message_telegram', $a);
        $settings->add(new admin_setting_heading('setuptelegram', '', $text . $link));
    }

    $settings->add(new admin_setting_configtext('message_telegram/sitebottoken', get_string('sitebottoken', 'message_telegram'),
        get_string('configsitebottoken', 'message_telegram'), $sitebottoken, PARAM_TEXT));
    $settings->add(new admin_setting_configtext('message_telegram/sitebotname', get_string('sitebotname', 'message_telegram'),
        get_string('configsitebotname', 'message_telegram'), $botname, PARAM_TEXT));
    $settings->add(new admin_setting_configtext('message_telegram/sitebotusername',
        get_string('sitebotusername', 'message_telegram'),
        get_string('configsitebotusername', 'message_telegram'), $botusername, PARAM_TEXT));

    $url = new moodle_url('/message/output/telegram/telegramconnect.php', ['sesskey' => sesskey(), 'action' => 'setwebhook']);
    $link = html_writer::link($url, get_string('setwebhook', 'message_telegram'));
    $settings->add(new admin_setting_heading('setwebhook', '', $link));
}
