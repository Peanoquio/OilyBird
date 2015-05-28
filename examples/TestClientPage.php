<?php
require_once( dirname(__FILE__) . "/../src/wamp_manager/constants/WampConstants.php" );
require_once( dirname(__FILE__) . "/../src/conf/Constants.php" );
require_once( dirname(__FILE__) . "/../src/conf/Config.php" );

/**
 * This provides examples on how to use the AjaxManager, WebsocketManager and WampManager client-side JavaScript classes
 * @author oliver.chong
 *
 */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Chat Page</title>

<script src="../js/lib/autobahn_WAMPv1.js"></script>

<script src="../js/common_utility.js"></script>
<script src="../js/ajax_manager.js"></script>
<script src="../js/websocket_manager.js"></script>
<script src="../js/wamp_manager.js"></script>



</head>

<body>

	<?php

	var_dump( "Hi there! Please open your browser console to view the logs pertaining to websockets.", "おおせのままに。。。", "多謝您們！" );

	?>


	<script>

		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		//connection parameters

		var sSocketIP = "<?php echo OilyBird\Conf\Config::getWebsocketIP() ?>";
		var sSocketPort = "<?php echo OilyBird\Conf\Config::getWebsocketPort() ?>";

		var sPubsubIP = "<?php echo OilyBird\Conf\Config::getWampPubSubIP() ?>";
		var sPubSubPort = "<?php echo OilyBird\Conf\Config::getWampPubSubPort() ?>";

		var sChannel = "<?php echo OilyBird\Conf\Constants::$CHANNELS["GAME"] ?>";

		var sRemoteProc = "<?php echo OilyBird\Conf\Constants::$PROCEDURES["TEST"] ?>";


		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		//AJAX

		//initialize the Ajax manager
		var ajaxMgr = new AjaxManager();

		//callback upon receiving the server response
		var fAjaxCallback = function( sReponse, param1, param2 )
		{
			console.log( "Ajax response:", param1, param2, JSON.parse( sReponse ) );
		};

		ajaxMgr.call( true, "TestAjax.php", "testA=1&testB=2&testC=3", fAjaxCallback, "testParam1", "testParam2" );


		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		//HTML5 WEBSOCKET

		//callback upon successful connection
		var fOnConnectCallback = function( evt ) {
				console.log( "sending a message through websockets" );
				websocketMgr.send( '{ "a":1, "b":2, "c":3 }' );
			};

		//callback upon receiving a message
		var fOnMessageCallback = function( evt ) {
				console.log( "parse the message" );
				var cObj = JSON.parse( evt.data );
				console.log( "cObj:", cObj, "cObj.a:", cObj.a );
			};

		//initialize the client-side websocket manager
		var websocketMgr = new WebsocketManager( sSocketIP, sSocketPort, fOnConnectCallback, fOnMessageCallback );

		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		//WAMP

		//note: this regarding using the correct client-side WAMP version
		//http://stackoverflow.com/questions/26133450/autobahn-0-9-5-amd-error-during-websocket-handshake

		//initialize the client-side WAMP manager
		var cWampMgr = new WampManager( new WampVer1( sPubsubIP, sPubSubPort, ab ) );

		//callback upon successful connection
		var fOnConnectCallback = function( cSession )
		{
			//subscribe
			cWampMgr.subscribe( cSession, sChannel, function ( topic, event ) {
					console.log( "receive message from subscribed channel...", topic );
					console.log( "msg:", event );
				} );

			//publish
			cWampMgr.publish( cSession, sChannel, { a: "help" } );

			//unsubscribe
			//cWampMgr.unsubscribe( cSession, sChannel );

			//remote procedure call
			cWampMgr.call( cSession, sRemoteProc, { top: 10, left: 5 },
					function ( data ) {
						console.log( "rpc return value:", data );
					},
					function ( error ) {
						console.log( "rpc error:", error );
					} );
		};

		//callback upon disconnection
		var fOnDisconnectCallback = function()
		{
			console.log( "Disconnected...", arguments );
		};

		//connect to the WAMP server
		cWampMgr.connect( fOnConnectCallback, fOnDisconnectCallback );

		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	</script>


</body>
</html>