/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//WAMP interface


//JavaScript does not support interfaces/pure virtual class so I am just toying with this idea
/**
 * The WAMP interface
 * @author oliver.chong
 *
 */
var WampInterface = new Interface( "WampInterface", [ "connect", "subscribe", "unsubscribe", "publish", "call" ] );


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//WAMP version 1


//added by Oliver Chong - April 20, 2015
/**
 * The class that handles WAMP version 1
 * @author oliver.chong
 *
 * @param string sIP : the IP address
 * @param unsigned nPort : the port number
 * @param object cAutobahnVer1 : the Autobahn class library (that support WAMP version 1)
 */
function WampVer1( sIP, nPort, cAutobahnVer1 )
{
	//check this class implements the interface
	Interface.EnsureImplements( this, WampInterface );

	this.m_sIP = sIP;
	this.m_nPort = nPort;

	this.m_cAb = cAutobahnVer1;

	//console.log( "Ok, Autobahn loaded...", this.m_cAb.version() );
	//this.m_cAb.debug( true, true );
};


//added by Oliver Chong - April 20, 2015
/**
 * Connect to the WAMP server
 *
 * @param function fOnConnectCallback : the callback function to be invoked upon successful connection
 * @param function fOnDisconnectCallback : the callback function to be invoked upon disconnection
 */
WampVer1.prototype.connect = function( fOnConnectCallback, fOnDisconnectCallback )
{
	var that = this;

	this.m_cAb.connect(
			// The WebSocket URI of the WAMP server
			"ws://" + this.m_sIP + ":" + this.m_nPort,

			// The onconnect handler
			function ( session )
			{
				if ( typeof ( fOnConnectCallback ) === "function" )
				{
					fOnConnectCallback.apply( that, [ session ] );
				}
			},

			// The onhangup handler
			function ( code, reason, detail )
			{
				if ( typeof ( fOnDisconnectCallback ) === "function" )
				{
					fOnDisconnectCallback.apply( that, [ code, reason, detail ] );
				}
			},

			// The session options
			// reconnection keeps on happening since the 'retries' does not reach the 'maxRetries'
			{
				'maxRetries': 3,
				'retryDelay': 5000
			}
		);
};


//added by Oliver Chong - April 20, 2015
/**
 * Subscribe to a channel
 *
 * @param object cSession : the Autobahn WAMP session
 * @param string sChannel : the channel to subscribe to
 * @param function fCallback : the callback function to be invoked after subscribing
 */
WampVer1.prototype.subscribe = function( cSession, sChannel, fCallback )
{
	var that = this;

	cSession.subscribe( sChannel, function ( topic, event ) {
		if ( typeof ( fCallback ) === "function" )
		{
			fCallback.apply( that, [ topic, event ] );
		}
	} );
};


//added by Oliver Chong - April 20, 2015
/**
 * Unsubscribe from a channel
 *
 * @param object cSession : the Autobahn WAMP session
 * @param string sChannel : the channel to unsubscribe from
 */
WampVer1.prototype.unsubscribe = function( cSession, sChannel )
{
	cSession.unsubscribe( sChannel );
};


//added by Oliver Chong - April 20, 2015
/**
 * Publish to a channel
 *
 * @param object cSession : the Autobahn WAMP session
 * @param string sChannel : the channel to publish to
 * @param array|object aData : the data to be sent to the channel
 */
WampVer1.prototype.publish = function( cSession, sChannel, aData )
{
	cSession.publish( sChannel, aData );
};


//added by Oliver Chong - April 20, 2015
/**
 * Performs a remote procedure call (RPC)
 *
 * @param object cSession : the Autobahn WAMP session
 * @param string sRemoteProcName : the remote procedure name
 * @param array|object aParam : the parameters to be passed to the remote procedure
 * @param function fSuccessCallback : the callback function to be invoked upon a successful remote procedure call
 * @param function fErrorCallback : the callback function to be invoked upon encountering an error
 */
WampVer1.prototype.call = function( cSession, sRemoteProcName, aParam, fSuccessCallback, fErrorCallback )
{
	var that = this;

    cSession.call( sRemoteProcName, aParam ).then(
			//success
			function( data )
			{
				if ( typeof ( fSuccessCallback ) === "function" )
				{
					fSuccessCallback.apply( that, [ data ] );
				}
			},
			//error
			function ( error )
			{
				if ( typeof ( fErrorCallback ) === "function" )
				{
					fErrorCallback.apply( that, [ error ] );
				}
			}
		);
};


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//WAMP manager


//added by Oliver Chong - April 20, 2015
/**
 * The WAMP manager class that handles client-side WAMP
 * @author oliver.chong
 *
 * @param object cWampInterfaceComponent : the class instance that implements the WAMP Interface
 */
 var WampManager = function( cWampInterfaceComponent )
 {
 	this.m_cComp = cWampInterfaceComponent;
 };


//added by Oliver Chong - April 20, 2015
/**
 * Connect to the WAMP server
 *
 * @param function fOnConnectCallback : the callback function to be invoked upon successful connection
 * @param function fOnDisconnectCallback : the callback function to be invoked upon disconnection
 */
WampManager.prototype.connect = function( fOnConnectCallback, fOnDisconnectCallback )
{
	this.m_cComp.connect( fOnConnectCallback, fOnDisconnectCallback );
};


//added by Oliver Chong - April 20, 2015
/**
 * Subscribe to a channel
 *
 * @param object cSession : the Autobahn WAMP session
 * @param string sChannel : the channel to subscribe to
 * @param function fCallback : the callback function to be invoked after subscribing
 */
WampManager.prototype.subscribe = function( cSession, sChannel, fCallback )
{
	this.m_cComp.subscribe( cSession, sChannel, fCallback );
};


//added by Oliver Chong - April 20, 2015
/**
 * Unsubscribe from a channel
 *
 * @param object cSession : the Autobahn WAMP session
 * @param string sChannel : the channel to unsubscribe from
 */
WampManager.prototype.unsubscribe = function( cSession, sChannel )
{
	this.m_cComp.unsubscribe( cSession, sChannel );
};


//added by Oliver Chong - April 20, 2015
/**
 * Publish to a channel
 *
 * @param object cSession : the Autobahn WAMP session
 * @param string sChannel : the channel to publish to
 * @param array|object aData : the data to be sent to the channel
 */
WampManager.prototype.publish = function( cSession, sChannel, aData )
{
	this.m_cComp.publish( cSession, sChannel, aData );
};


//added by Oliver Chong - April 20, 2015
/**
 * Performs a remote procedure call (RPC)
 *
 * @param object cSession : the Autobahn WAMP session
 * @param string sRemoteProcName : the remote procedure name
 * @param array|object aParam : the parameters to be passed to the remote procedure
 * @param function fSuccessCallback : the callback function to be invoked upon a successful remote procedure call
 * @param function fErrorCallback : the callback function to be invoked upon encountering an error
 */
WampManager.prototype.call = function( cSession, sRemoteProcName, aParam, fSuccessCallback, fErrorCallback )
{
	this.m_cComp.call( cSession, sRemoteProcName, aParam, fSuccessCallback, fErrorCallback );
};


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////