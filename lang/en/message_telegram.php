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
 * Strings for telegram message plugin.
 *
 * @package message_telegram
 * @author  Mike Churchward
 * @copyright  2017 onwards Mike Churchward (mike.churchward@poetgroup.org)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['configsitebotname'] = 'This will be filled in automatically when you save the bot token.';
$string['configsitebottoken'] = 'Enter the site bot token from Botfather here.';
$string['configsitebotusername'] = 'This will be filled in automatically when you save the bot token.';
$string['connectinstructions'] = 'Once you have clicked the link below, you will need to allow the link to open in Telegram with
your Telegram account. In Telegram, click the "Start" button in the "{$a}" chat that opens to connect your account to Moodle.
Once completed, come back to this page and click "Save changes". Full documentation
<a href="https://docs.moodle.org/33/en/Telegram_message_processor#Configuring_user_preferences" target="_blank">here</a>.';
$string['connectme'] = 'Connect my account to Telegram.';
$string['notconfigured'] = 'The Telegram server hasn\'t been configured so Telegram messages cannot be sent';
$string['pluginname'] = 'Telegram';
$string['sitebotname'] = 'Bot name for site';
$string['sitebottoken'] = 'Bot token for site';
$string['sitebottokennotsetup'] = 'Bot token for site must be specified in plugin settings.';
$string['sitebotusername'] = 'Bot username for site';
$string['telegrambottoken'] = 'Telegram bot token';
$string['telegramchatid'] = 'Telegram chat id';
$string['removetelegram'] = 'Remove Telegram connection';
$string['requirehttps'] = 'Site must use HTTPS for Telegram\'s webhook function.';
$string['setupinstructions'] = 'Create a new Telegram Bot using Botfather. Click the Botfather link below and open it in Telegram.
Use the "/newbot" command in Telegram to start creating the bot. You will need to specify a botname, for example "{$a->name}", and a
unique bot username, for example "{$a->username}". Full documentation
<a href="https://docs.moodle.org/33/en/Telegram_message_processor" target="_blank">here</a>.';
$string['setwebhook'] = 'Setup Telegram webhook';
