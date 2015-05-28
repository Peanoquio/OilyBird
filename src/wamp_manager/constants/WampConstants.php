<?php


namespace OilyBird\WampMgr\Constants;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The enumeration for the WAMP constants
 * @author oliver.chong
 *
 */
abstract class WampConstants
{
	//URL parameters
	const MODE = "mode";
	const WAMP_ACTION = "wamp_action";
	const CHANNEL = "channel";
	const MSG = "msg";

	//mode
	const MODE_NORMAL = 0;
	const MODE_PUBSUB_ZMQ = 1;
	const MODE_PUBSUB_REDIS = 2;

	//wamp action
	const WAMP_ACTION_PUBLISH = 1;
	const WAMP_ACTION_SUBSCRIBE = 2;
	const WAMP_ACTION_UNSUBSCRIBE = 3;
	const WAMP_ACTION_RPC_CALL = 4;
	const WAMP_ACTION_RPC_REGISTERL = 5;
}

?>