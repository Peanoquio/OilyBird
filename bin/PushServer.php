<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\WampServer;

use React\ZMQ;

use OilyBird\Conf\Config;
use OilyBird\Conf\Constants;
use OilyBird\WampMgr\Constants\WampConstants;
use OilyBird\WampMgr\Handler\WampServerHandler;
use OilyBird\WampMgr\Component\PubSubComponentRedis;
use OilyBird\WampMgr\Component\PubSubComponentZmq;
use OilyBird\WampMgr\WampManager;


require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * This is the event loop that listens for WAMP Pub/Sub and RPC events
 * @author oliver.chong
 *
 */


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//these are the code references that I studied to make Pub/Sub working through 2 methods: Redis and ZMQ
//http://socketo.me/docs/push
//http://blog.jmoz.co.uk/websockets-ratchet-react-redis/
//http://qiita.com/ytake/items/0a0be973552b7cd055a1


//create the event loop
$loop = React\EventLoop\Factory::create();


//this will broadcast the messages through the websockets once data is received in the event loop
$cWampHandler = new WampServerHandler();

//register the procedures for RPC (Remote Procedure Calls)
$cWampHandler->registerProcedure( Constants::$PROCEDURES["TEST"], function() {
	return func_get_args();
} );


//initialize the WAMP manager
if ( Constants::PUBSUB_MODE == WampConstants::MODE_PUBSUB_ZMQ )
{
	echo "Init WAMP manager to use ZMQ...\n";
	WampManager::initialize( new PubSubComponentZmq( $loop ) );
}
else
{
	echo "Init WAMP manager to use Redis...\n";
	WampManager::initialize( new PubSubComponentRedis( $loop ) );
}


//subscribe
WampManager::subscribe( Constants::$CHANNELS["CHAT"] );
WampManager::subscribe( Constants::$CHANNELS["GAME"] );


//this parameter is only needed when using Redis pub/sub
$aChannels = array( "subscribe" => array(
		Constants::$CHANNELS["CHAT"] => Constants::$CHANNELS["CHAT"],
		Constants::$CHANNELS["GAME"] => Constants::$CHANNELS["GAME"] )
);


//on message listener
WampManager::onMessage( array( $cWampHandler, "broadcast" ), $aChannels );


// Set up our WebSocket server for clients wanting real-time updates
$webSocket = new React\Socket\Server( $loop );
// Binding to 0.0.0.0 means remotes can connect
//$webSocket->listen( Config::getWampPubSubPort(), '0.0.0.0' );
$webSocket->listen( Config::getWampPubSubPort(), Config::getWampPubSubIP() );


//create the webserver that supports websockets using the WAMP subprotocol
$webServer = new IoServer(
		new HttpServer(
				new WsServer(
						new WampServer(
								$cWampHandler
						)
				)
		),
		$webSocket
);


echo "WAMP Pusher starting...\n";


$loop->run();

?>