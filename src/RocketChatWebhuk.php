<?php

namespace RocketChat;

class WebHook extends Client {
	public $text; // Text message as array 
	public $postData; // Full message as array
	/*
  array (
    'token' => 'token',
    'bot' => false,
    'channel_id' => 'EtP4MMXZ8WHWEMw6e',
    'channel_name' => 'chanal',
    'message_id' => 'Wp2s2nDLpTGfp2eQp',
    'timestamp' => '2019-04-29T18:13:41.310Z',
    'user_id' => '13123123',
    'user_name' => 'user',
    'text' => 'edit text',
    'isEdited' => true,
  )
	 */

	function __construct(){
		$this->postData=json_decode(file_get_contents('php://input'), true);
		if (isset($this->postData['text']))
			$this->text = explode ("\n",$this->postData['text']);
	}

	public function getChannelId()
	{
		return $this->postData['channel_id'];
	}

	public function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
		$escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
		$replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
		$result = str_replace($escapers, $replacements, $value);
		return $result;
	}

    /**
     * Send message via WebHook
     */
	public function sendmessage($text){
		// Send message and EXIT script;
		$text_valid=$this->escapeJsonString($text);
		$data = '{"text": "'.$text_valid.'"}';
/*  "attachments": [
    {
      "title": "'.$text.'"
        }
        ]
        }';
/*
      "title_link": "https://rocket.chat",
      "text": "Rocket.Chat, the best open source chat",
      "image_url": "https://rocket.chat/images/mockup.png",
      "color": "#764FA5"
    }
  ]
}';
*/
		header('Content-Type: application/json');
		echo $data;
		exit;
	}
}

