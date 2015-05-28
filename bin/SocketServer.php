<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

use OilyBird\Conf\Config;
use OilyBird\WampMgr\Handler\SocketServerHandler;

require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * This is the event loop that listens for websocket events
 * @author oliver.chong
 *
 */


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//create the webserver that supports websockets
$webServer = IoServer::factory(
		new HttpServer(
				new WsServer(
						new SocketServerHandler()
				)
		),
		Config::getWebsocketPort()
);

var_dump( "Websocket server starting..." );

$webServer->run();

?>