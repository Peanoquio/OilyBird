<?php

namespace OilyBird\Conf;


use OilyBird\WampMgr\Constants\WampConstants;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The class that contains the configuration parameters
 * @author oliver.chong
 *
 */
abstract class Constants
{
	//the pubsub mode to use
	//const PUBSUB_MODE = WampConstants::MODE_PUBSUB_ZMQ;
	const PUBSUB_MODE = WampConstants::MODE_PUBSUB_REDIS;

	//the list of valid channels
	static $CHANNELS = array(
			"CHAT" => "channel_chat",
			"GAME" => "channel_game",
			"TEST" => "channel_test"
			);

	//the list of valid procedures
	static $PROCEDURES = array(
			"TEST" => "procedure_test"
			);

}//end class

?>