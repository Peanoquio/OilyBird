<?php

namespace OilyBird\WampMgr\Handler;

use OilyBird\Common\Util;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\Wamp\Topic;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The WAMP server component that handles WAMP-related events
 * @author oliver.chong
 *
 */
class WampServerHandler implements WampServerInterface
{

	/**
	 * The list of subscribed channels
	 * @var array
	 */
    private $m_aSubscribedTopics = array();

    /**
     * The list of registered procedures
     * @var array
     */
    private $m_aRegisteredProcedures = array();



    //added by Oliver Chong - April 20, 2015
    /**
     * The constuctor for the WampServerComponent
     */
    public function __construct()
    {
    }


	/**
	 * Prints out the log
	 * @param mixed $value : the value to be printed out
	 */
    public function log( $value )
    {
    	var_dump( $value );
        $message = sprintf("Pusher: %s", $value);
        echo "$message\n";
    }


    //added by Oliver Chong - April 20, 2015
    /**
     * Registers the procedure name needed to support RPC (Remote Procedure Call)
     * @param string $sProcedureName : the procedure name
     * @param callable $fProcedure : the function (remote procedure)
     * @param string $sUrl : the URL (location of the remote procedure)
     */
    public function registerProcedure( $sProcedureName, callable $fProcedure, $sUrl = "" )
    {
    	//note:
    	//for now, we won't be supporting remote calls for this framework (requires setting up PHP cURL extension)
    	//the RPC will actually invoke local functions instead

    	if ( !array_key_exists( $sProcedureName, $this->m_aRegisteredProcedures) )
    	{
    		$this->m_aRegisteredProcedures[ $sProcedureName ] = $fProcedure;
    	}
    }


    //added by Oliver Chong - April 16, 2015
    /**
     * Broadcasts the message to the clients in the channel
     * @param string $sJsonData : the message data in JSON format that contains two parts (channel and message)
     */
    public function broadcast( $sJsonData )
    {
    	$aData = json_decode( $sJsonData );

    	var_dump( "push---------", $this->m_aSubscribedTopics, $sJsonData, $aData );

    	$sChannel = $aData[ 0 ];

    	foreach ( $this->m_aSubscribedTopics as $topic )
    	{
    		//find the matching channel
    		if ( $topic->getId() == $sChannel )
    		{
    			var_dump( "broadcast to $sChannel!!! +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++", $aData[ 1 ] );

    			//broadcast to the subscribers of this channel
    			$topic->broadcast( $aData[ 1 ] );
    			break;
    		}
    	}//end loop
    }


    /**
     * (non-PHPdoc)
     * @see Ratchet.ComponentInterface::onOpen()
     */
    public function onOpen( ConnectionInterface $conn )
    {
    	$this->log("onOpen ({$conn->WAMP->sessionId})");
    }


    /**
     * (non-PHPdoc)
     * @see Ratchet.ComponentInterface::onClose()
     */
    public function onClose( ConnectionInterface $conn )
    {
    	$this->log("onClose ({$conn->WAMP->sessionId})");
    }


    //added by Oliver Chong - April 20, 2015
    /**
     * (non-PHPdoc)
     * @see Ratchet\Wamp.WampServerInterface::onPublish()
     */
    public function onPublish( ConnectionInterface $conn, /*Topic*/ $topic, $event, array $exclude, array $eligible )
    {
    	$this->log("onPublish");

    	//var_dump( $topic, $event, $exclude, $eligible );

    	$sJsonData = json_encode( array( $topic->getId(), $event ) );

    	var_dump( array( $topic->getId(), $event ), $sJsonData );

    	$this->broadcast( $sJsonData );
    }


    //added by Oliver Chong - April 16, 2015
    /**
     * (non-PHPdoc)
     * @see Ratchet\Wamp.WampServerInterface::onSubscribe()
     */
    public function onSubscribe( ConnectionInterface $conn, $topic )
    {
    	$this->log("onSubscribe ".$topic->getId() );
    	$this->log("session id {$conn->WAMP->sessionId}");
    	$this->log("topic: $topic {$topic->count()}");

    	// When a visitor subscribes to a topic link the Topic object in a  lookup array
    	if ( !array_key_exists( $topic->getId(), $this->m_aSubscribedTopics) )
    	{
    		$this->m_aSubscribedTopics[ $topic->getId() ] = $topic;
    	}
    }


    //added by Oliver Chong - April 16, 2015
    /**
     * (non-PHPdoc)
     * @see Ratchet\Wamp.WampServerInterface::onUnSubscribe()
     */
    public function onUnSubscribe( ConnectionInterface $conn, /*Topic*/ $topic )
    {
        $this->log("onUnSubscribe topic: $topic {$topic->count()}");
    }


    //added by Oliver Chong - April 20, 2015
    /**
     * (non-PHPdoc)
     * @see Ratchet\Wamp.WampServerInterface::onCall()
     */
    public function onCall( ConnectionInterface $conn, $id, /*Topic*/ $topic, array $aParams )
    {
        // In this application if clients send data it's because the user hacked around in console
        $this->log("onCall");
        var_dump( $id, $topic, $aParams );

        //check if the procedure has beeen registered
        if ( !array_key_exists( $topic->getId(), $this->m_aRegisteredProcedures) )
        {
        	$conn->callError( $id, $topic->getId(), "The procedure " . $topic->getId() . " is not registered." );
        	return false;
        }
		else
		{
			//calls the remote procedure
			$returnVal = call_user_func_array( $this->m_aRegisteredProcedures[ $topic->getId() ], array( $aParams ) );

			return $conn->callResult( $id, array( $id, $topic->getId(), $returnVal ) );
		}
    }


    /**
     * (non-PHPdoc)
     * @see Ratchet.ComponentInterface::onError()
     */
    public function onError( ConnectionInterface $conn, \Exception $e )
    {
        $this->log("onError : " . " code:" . $e->getCode() . " file:" . $e->getFile() . " line:" . $e->getLine() . " msg:" . $e->getMessage() . " trace:" . $e->getTraceAsString());
    }

}//end class

?>