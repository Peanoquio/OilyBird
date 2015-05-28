<?php


namespace OilyBird\WampMgr\Component;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The publish and subscribe interface
 * @author oliver.chong
 *
 */
interface PubSubInterface
{
	/**
	 * Publish the message to the subscribers of the channel
	 * @param string $sChannel : the channel that will receive the message
	 * @param string $sMsg : the message to be sent
	 * @return unsigned : the number of subscribers of the channel
	 */
	public function publish( $sChannel, $sMsg );


	/**
	 * Subscribe to a channel
	 * @param string $sChannel : the channel to subscribe to
	 */
	public function subscribe( $sChannel );


	/**
	 * The channel to unsubscribe from
	 * @param string $sChannel : the channel to unsubscribe from
	 */
	public function unsubscribe( $sChannel );


	/**
	 * The event listener once a message has been received
	 * @param callable $fCallback : the callback function to execute
	 * @param array $aParams : the parameters to be passed to the callback function
	 */
	public function onMessage( callable $fCallback/*, array $aParams = array()*/ );

}//end interface


?>