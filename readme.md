[![Build Status](https://travis-ci.org/mchurchward/moodle-message_output_telegram.png?branch=MOODLE_32_BETA)](https://travis-ci.org/mchurchward/moodle-message_output_telegram)

First, create a new bot - https://core.telegram.org/bots#3-how-do-i-create-a-bot
(talk to botfather using https://telegram.me/botfather)
That will give you the token you need for configuring the Moodle plugin.
Next, get the chat_id for your chat.
There doesn't seem to be easy way to do that. Here are a couple of options:

Option 1 - Use the get_id_bot:
Go to https://telegram.me/get_id_bot and allow it to open in your Telegram app. It will report your chat_id.
Or, in your telegram app, enter "@get_id_bot /my_id". Click the resulting "get_id_bot" link. Click "Start". Wait a few seconds and
it will report your chat_id.

Option 2 - Use the getUpdates API:
Go to following url: https://api.telegram.org/botXXX:YYYY/getUpdates
replace XXX:YYYY with your bot token
Look for "chat":{"id":-zzzzzzzzzz,
-zzzzzzzzzz is your chat id (with the negative sign).

Once you have your bot token, and your chat id, complete your notification configuration on your notification preferences screen
in Moodle.
