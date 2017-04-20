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
 * Telegram message plugin version information.
 *
 * @package message_telegram
 * @author  Mike Churchward
 * @copyright  2017 onwards Mike Churchward (mike.churchward@poetgroup.org)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/message/output/lib.php');

/**
 * The telegram message processor
 *
 * @package   message_telegram
 * @copyright  2017 onwards Mike Churchward (mike.churchward@poetgroup.org)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class message_output_telegram extends message_output {

    /**
     * Processes the message and sends a notification via telegram
     *
     * @param stdClass $eventdata the event data submitted by the message sender plus $eventdata->savedmessageid
     * @return true if ok, false if error
     */
    public function send_message($eventdata) {
        global $CFG;

        // Skip any messaging of suspended and deleted users.
        if (($eventdata->userto->auth === 'nologin') || $eventdata->userto->suspended || $eventdata->userto->deleted) {
            return true;
        }

        if (!empty($CFG->noemailever)) {
            // Hidden setting for development sites, set in config.php if needed.
            debugging('$CFG->noemailever is active, no telegram message sent.', DEBUG_MINIMAL);
            return true;
        }

        if (empty($usertoken = get_user_preferences('message_processor_telegram_bottoken', '', $eventdata->userto->id))) {
            return true;
        }
        if (empty($chatid = get_user_preferences('message_processor_telegram_chatid', '', $eventdata->userto->id))) {
            return true;
        }

        $message = $eventdata->fullmessage;

        $curl = new curl();
        $response = json_decode($curl->get('https://api.telegram.org/bot'.$usertoken.'/sendMessage',
            ['chat_id' => $chatid, 'text' => $message]));
        return (!empty($response) && isset($response->ok) && ($response->ok == true));
    }

    /**
     * Creates necessary fields in the messaging config form.
     *
     * @param array $preferences An object of user preferences
     */
    public function config_form($preferences) {
        global $USER;
        if (!$this->is_system_configured()) {
            return get_string('notconfigured', 'message_telegram');
        } else {
            $bottoken = get_string('telegrambottoken', 'message_telegram').': <input size="30" name="telegram_bottoken" value="'.
                s($preferences->telegram_bottoken).'" /><br />';
            $chatid = get_string('telegramchatid', 'message_telegram').': <input size="30" name="telegram_chatid" value="'.
                s($preferences->telegram_chatid).'" />';
            return $bottoken.$chatid;
        }
    }

    /**
     * Parses the submitted form data and saves it into preferences array.
     *
     * @param stdClass $form preferences form class
     * @param array $preferences preferences array
     */
    public function process_form($form, &$preferences) {
        if (isset($form->telegram_bottoken) && !empty($form->telegram_bottoken)) {
            $preferences['message_processor_telegram_bottoken'] = $form->telegram_bottoken;
        }
        if (isset($form->telegram_chatid) && !empty($form->telegram_chatid)) {
            $preferences['message_processor_telegram_chatid'] = $form->telegram_chatid;
        }
    }

    /**
     * Loads the config data from database to put on the form during initial form display.
     *
     * @param object $preferences preferences object
     * @param int $userid the user id
     */
    public function load_data(&$preferences, $userid) {
        $preferences->telegram_bottoken = get_user_preferences('message_processor_telegram_bottoken', '', $userid);
        $preferences->telegram_chatid = get_user_preferences('message_processor_telegram_chatid', '', $userid);
    }

    /**
     * Tests whether the Telegram settings have been configured on user level
     * @param  object $user the user object, defaults to $USER.
     * @return bool has the user made all the necessary settings
     * in their profile to allow this plugin to be used.
     */
    public function is_user_configured($user = null) {
        global $USER;

        if ($user === null) {
            $user = $USER;
        }
        return (!empty(get_user_preferences('message_processor_telegram_bottoken', null, $user->id)) &&
                !empty(get_user_preferences('message_processor_telegram_chatid', null, $user->id)));
    }
}