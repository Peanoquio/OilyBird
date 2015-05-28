/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//HTML5 Websocket


//added by Oliver Chong - April 21, 2015
/**
 * The class that handles HTML5 websockets
 * @author oliver.chong
 *
 * @param string sIP : the IP address
 * @param unsigned nPort : the port number
 * @param function fOnConnectCallback : the callback function to be invoked upon successful connection
 * @param function fOnMessageCallback : the callback function to be invoked upon receiving a message
 * @param function fOnErrorCallback : the callback function to be invoked when encountering a connection error
 */
function WebsocketManager( sIP, nPort, fOnConnectCallback, fOnMessageCallback, fOnErrorCallback )
{
	var that = this;

	//initialize the websocket connection
	this.m_cConn = new WebSocket( "ws://" + sSocketIP + ":" + sSocketPort );

	//triggers upon successful connection
	this.m_cConn.onopen = function( evt )
	{
		console.log( "Websocket: connection established!", evt );

		if ( typeof( fOnConnectCallback ) === "function" )
		{
			fOnConnectCallback.call( that, evt );
		}
	};

	//triggers upon receiving a message
	this.m_cConn.onmessage = function( evt )
	{
		console.log( "Websocket: message received...", evt, evt.data );

		if ( typeof( fOnMessageCallback ) === "function" )
		{
			fOnMessageCallback.call( that, evt );
		}
	};

	//triggers upon encountering a connection error
	this.m_cConn.onerror = function( err )
	{
		console.log( "Websocket: error!", err );

		if ( typeof( fOnErrorCallback ) === "function" )
		{
			fOnErrorCallback.call( that, err );
		}
	};
};


//added by Oliver Chong - April 20, 2015
/**
 * Send a message through the websocket
 *
 * @param string sJsonData : the string in JSON format
 */
WebsocketManager.prototype.send = function( sJsonData )
{
	this.m_cConn.send( sJsonData );
};


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
