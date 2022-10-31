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

namespace message_telegram;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/lib/filelib.php');

/**
 * Telegram helper manager class
 *
 * @author  Mike Churchward
 * @copyright  2017 onwards Mike Churchward (mike.churchward@poetgroup.org)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /**
     * @var $secretprefix A variable used to identify that chatid had not been set for the user.
     */
    private $secretprefix = 'usersecret::';

    /**
     * @var $curl The curl object used in this run. Avoids continuous creation of a curl object.
     */
    private $curl = null;

    /**
     * Constructor. Loads all needed data.
     */
    public function __construct() {
        $this->config = get_config('message_telegram');
    }

    /**
     * Send the message to Telegram.
     * @param string $message The message contect to send to Slack.
     * @param int $userid The Moodle user id that is being sent to.
     */
    public function send_message($message, $userid) {

        if (empty($this->config('sitebottoken'))) {
            return true;
        } else if (empty($chatid = get_user_preferences('message_processor_telegram_chatid', '', $userid))) {
            return true;
        }

        /**
         * remove  <p>...</p> and <a href="...">...</a>
         */
        $msg = preg_replace('/<p>((.|\n)*)<\/p>/', '${1}', $message);
        $msg = preg_replace('/<a href=[^>]*>(.*)<\/a>/','${1}',$msg);
        $response = $this->send_api_command('sendMessage', ['chat_id' => $chatid, 'text' => $msg]);
        return (!empty($response) && isset($response->ok) && ($response->ok == true));
    }

    /**
     * Set the config item to the specified value, in the object and the database.
     * @param string $name The name of the config item.
     * @param string $value The value of the config item.
     */
    public function set_config($name, $value) {
        set_config($name, $value, 'message_telegram');
        $this->config->{$name} = $value;
    }
    /**
     * Return the requested configuration item or null. Should have been loaded in the constructor.
     * @param string $configitem The requested configuration item.
     * @return mixed The requested value or null.
     */
    public function config($configitem) {
        return isset($this->config->{$configitem}) ? $this->config->{$configitem} : null;
    }

    /**
     * Return the HTML for the user preferences form.
     * @param array $preferences An array of user preferences.
     * @param int $userid Moodle id of the user in question.
     * @return string The HTML for the form.
     */
    public function config_form ($preferences, $userid) {
        // If the chatid is not set, display the link to do this.
        if (!$this->is_chatid_set($userid, $preferences)) {
            // Temporarily set the user's chatid to the sesskey value for security.
            $this->set_usersecret($userid);
            $url = 'https://telegram.me/'.$this->config('sitebotusername').'?start='.$this->usersecret();
            $configbutton = get_string('connectinstructions', 'message_telegram', $this->config('sitebotname'));
            $configbutton .= '<div align="center"><a href="'.$url.'" target="_blank">'.
                get_string('connectme', 'message_telegram') . '</a></div>';
        } else {
            $url = new \moodle_url($this->redirect_uri(), ['action' => 'removechatid', 'userid' => $userid,
                'sesskey' => sesskey()]);
            $configbutton = '<a href="'.$url.'">' . get_string('removetelegram', 'message_telegram') . '</a>';
        }

        return $configbutton;
    }

    /**
     * Construct a variable used only by the plugin to help ensure user identity.
     * @return string A constructed variable for this user (Moodle's sesskey).
     */
    public function usersecret() {
        return sesskey();
    }

    /**
     * Set the user's chat id to the usersecret to allow for secure chat id identification.
     * @param int $userid The id of the user to set this for.
     * @return boolean Success or failure.
     */
    private function set_usersecret($userid = null) {
        global $USER;

        if ($userid === null) {
            $userid = $USER->id;
        }

        if ($userid != $USER->id) {
            require_capability('moodle/site:config', \context_system::instance());
        }

        return set_user_preference('message_processor_telegram_chatid', $this->secretprefix . $this->usersecret(), $userid);
    }

    /**
     * Check that the received usersecret matches the user's usersecret stored in the database.
     * @param string $receivedsecret The secret to test against the stored one.
     * @param int $userid The id of the user to set this for.
     * @return boolean Success or failure.
     */
    private function usersecret_match($receivedsecret, $userid = null) {
        global $USER;

        if ($userid === null) {
            $userid = $USER->id;
        }

        if ($userid != $USER->id) {
            require_capability('moodle/site:config', \context_system::instance());
        }

        $usersecret = substr(get_user_preferences('message_processor_telegram_chatid', '', $userid), strlen($this->secretprefix));
        return ($usersecret === $receivedsecret);
    }

    /**
     * Verify that a user has their chat id set.
     * @param int $userid The id of the user to check.
     * @param object $preferences Contains the Telegram user preferences for the user, if present.
     * @return boolean True if the id is set.
     */
    public function is_chatid_set($userid, $preferences = null) {
        if ($preferences === null) {
            $preferences = new \stdClass();
        }
        if (!isset($preferences->telegram_chatid)) {
            $preferences->telegram_chatid = get_user_preferences('message_processor_telegram_chatid', '', $userid);
        }
        return (!empty($preferences->telegram_chatid) && (strpos($preferences->telegram_chatid, $this->secretprefix) !== 0));
    }

    /**
     * Return the redirect URI to handle the callback for OAuth.
     * @return string The URI.
     */
    public function redirect_uri() {
        global $CFG;

        return $CFG->wwwroot.'/message/output/telegram/telegramconnect.php';
    }

    /**
     * Given a valid bot token, get the name and username of the bot.
     */
    public function update_bot_info() {
        if (empty($this->config('sitebottoken'))) {
            return false;
        } else {
            $response = $this->send_api_command('getMe');
            if ($response->ok) {
                $this->set_config('sitebotname', $response->result->first_name);
                $this->set_config('sitebotusername', $response->result->username);
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Get the latest information from the Slack bot, and see if the user has initiated a connection.
     * Only needed if no webHook has been created.
     * @param int $userid The id of the user in question.
     * @return boolean Success.
     */
    public function set_chatid($userid = null) {
        global $USER;

        if ($userid === null) {
            $userid = $USER->id;
        }

        if (empty($this->config('sitebottoken'))) {
            return false;
        } else {
            $results = $this->get_updates();
            if ($results !== false) {
                foreach ($results as $object) {
                    if (isset($object->message)) {
                        if ($this->usersecret_match(substr($object->message->text, strlen('/start ')))) {
                            set_user_preference('message_processor_telegram_chatid', $object->message->chat->id, $userid);
                            break;
                        }
                    }
                }
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Remove the user's Telegram chat id from the preferences.
     * @param int $userid The id to be cleared.
     * @return string Any information message.
     */
    public function remove_chatid($userid = null) {
        global $USER;

        if ($userid === null) {
            $userid = $USER->id;
        } else if ($userid != $USER->id) {
            require_capability('moodle/site:config', \context_system::instance());
        }
        unset_user_preference('message_processor_telegram_chatid', $userid);

        return '';
    }

    /**
     * Set the webhook for this site into the Telegram Bot.
     * @return string Empty if successful, otherwise the error message.
     */
    public function set_webhook() {
        return 'This feature is still under development... Stand by.';
        if (empty($this->config('sitebottoken'))) {
            $message = get_string('sitebottokennotsetup', 'message_telegram');
        } else {
            $response = $this->send_api_command('setWebhook', ['url' => $this->redirect_uri(), 'allowed_updates' => 'message']);
            if (!empty($response) && isset($response->ok) && ($response->ok == true)) {
                $this->set_config('webhook', '1');
                $message = '';
            } else if (!empty($response) && isset($response->error_code) && isset($response->description)) {
                $message = $response->description;
            }
        }
        return $message;
    }

    /**
     * Returns the results of a getUpdates API request.
     * @return object The JSON decoded results object.
     */
    public function get_updates() {
        $response = $this->send_api_command('getUpdates');
        if ($response->ok) {
            return $response->result;
        } else {
            return false;
        }
    }

    /**
     * Send a Telegram API command and return the results.
     * @param string $command The API command to send.
     * @param array $params The parameters to send to the API command. Can be ommited.
     * @return object The JSON decoded return object.
     */
    private function send_api_command($command, $params = null) {
        if (empty($this->config('sitebottoken'))) {
            return false;
        }

        if ($this->curl === null) {
            $this->curl = new \curl();
        }

        return json_decode($this->curl->get('https://api.telegram.org/bot'.$this->config('sitebottoken').'/'.$command, $params, ["CURLOPT_RETURNTRANSFER" => true]));
    }
}
