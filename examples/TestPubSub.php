<?php

use OilyBird\Conf\Constants;
use OilyBird\WampMgr\Constants\WampConstants;
use OilyBird\WampMgr\Component\PubSubComponentRedis;
use OilyBird\WampMgr\Component\PubSubComponentZmq;
use OilyBird\WampMgr\WampManager;


require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * This provides examples on how to use the WAMP manager to publish / subscribe to a channel (using either Redis or ZMQ Pub/Sub)
 * @author oliver.chong
 *
 */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


echo "<span style='font-weight:bold'>This will test subscribing and publishing from the server-side</span><br><br>";


//initialize the WAMP manager
if ( Constants::PUBSUB_MODE == WampConstants::MODE_PUBSUB_ZMQ )
{
	echo "Initializing PubSub to use <span style='color:green'>ZMQ</span><br><br>";

	WampManager::initialize( new PubSubComponentZmq() );
}
else
{
	echo "Initializing PubSub to use <span style='color:green'>Redis</span><br><br>";

	WampManager::initialize( new PubSubComponentRedis() );
}


//subscribe
echo "<span style='color:red'>Take note that the subscribe will only work if subscribing from the event loop</span><br>";
echo "Subscribing to channel... <br>";

WampManager::subscribe( Constants::$CHANNELS["TEST"] );

echo "Subscribed to channel: <span style='color:green'>" . Constants::$CHANNELS["TEST"] . "</span><br><br>";


//publish
echo "Publishing to channels... <br>";

WampManager::publish( Constants::$CHANNELS["CHAT"], "let's chat" );
WampManager::publish( Constants::$CHANNELS["GAME"], "it's game time!" );

echo "send message <span style='color:purple'>'let's chat'</span> to <span style='color:green'>" . Constants::$CHANNELS["CHAT"] . "</span><br>";
echo "send message <span style='color:purple'>'it's game time!'</span> to <span style='color:green'>" . Constants::$CHANNELS["GAME"] . "</span><br>";

?>