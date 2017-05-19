[![Build Status](https://travis-ci.org/mchurchward/moodle-message_output_telegram.png?branch=MOODLE_32_BETA)](https://travis-ci.org/mchurchward/moodle-message_output_telegram)

For Admins:
First, create a new bot - https://core.telegram.org/bots#3-how-do-i-create-a-bot
(talk to botfather using https://telegram.me/botfather)
That will give you the token you need for configuring the Moodle plugin.

To make this easier for your users, you can select the "Use site bot token" option at the main Telegram settings screen, and set
up one Bot for the site. Then the users will only need to get their chat id. If you don't do this, then users will also need to
create their own bots.

For Users:
Next, get the chat_id for your chat. Currently, this is a manual process. To do this, go to https://telegram.org/dl/webogram and
sign in using your cell phone number. If you have not done this previously, you will need to create your account.

Option 1 - Use the get_id_bot:
Go to https://telegram.me/get_id_bot and allow it to open in your Telegram app. It will report your chat_id. Or, in your telegram
app, enter "@get_id_bot /my_id". Click the resulting "get_id_bot" link. Click "Start". Wait a few seconds or several minutes
(I've seen as long as ten minutes) and it will report your chat_id.

Option 2 - Use the getUpdates API:
You can only use this option if you have configured your own bot. If using a site bot, use option 1.
Go to following url: https://api.telegram.org/botXXX:YYYY/getUpdates replace XXX:YYYY with your bot token.
Look for '"chat":{"id":zzzzzzzzzz,'. 'zzzzzzzzzz' is your chat id.

Once you have your bot token, and your chat id, complete your notification configuration on your notification preferences screen
in Moodle.
