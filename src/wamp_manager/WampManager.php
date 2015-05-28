<?php

namespace OilyBird\WampMgr;

use OilyBird\Common\Util;
use OilyBird\Common\Singleton;
use OilyBird\WampMgr\Constants\WampConstants;
use OilyBird\WampMgr\Component\PubSubInterface;
use OilyBird\WampMgr\Component\PubSubComponentZmq;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The class that handles WAMP (uses the strategy and singleton design patterns)
 * @author oliver.chong
 *
 */
class WampManager extends Singleton
{
	//the publish/subscribe component
	private $m_cPubSub;


	/**
	 * The constructor of the WampManager
	 * @param PubSubInterface $cPubSub : an instance of the class that inherits from the PubSubInterface base class
	 */
	public function __construct( PubSubInterface $cPubSub )
	{
		$this->m_cPubSub = $cPubSub;
	}


	/**
	 * Initializes the WampManager with the publish/subscribe object being passed (it will be handled polymorphically)
	 * @param PubSubInterface $cPubSub : an instance of the class that inherits from the PubSubInterface base class
	 */
	public static function initialize( PubSubInterface $cPubSub )
	{
		return self::getInstance( $cPubSub );
	}


	/**
	 * Publish the message to the subscribers of the channel
	 * @param string $sChannel : the channel that will receive the message
	 * @param string $sMsg : the message to be sent
	 * @return unsigned : the number of subscribers of the channel
	 */
	public static function publish( $sChannel, $sMsg )
	{
		return self::getInstance()->m_cPubSub->publish( $sChannel, $sMsg );
	}


	/**
	 * Subscribe to a channel
	 * @param string $sChannel : the channel to subscribe to
	 */
	public static function subscribe( $sChannel )
	{
		self::getInstance()->m_cPubSub->subscribe( $sChannel );
	}


	/**
	 * The channel to unsubscribe from
	 * @param string $sChannel : the channel to unsubscribe from
	 */
	public static function unsubscribe( $sChannel )
	{
		self::getInstance()->m_cPubSub->unsubscribe( $sChannel );
	}


	/**
	 * The event listener once a message has been received
	 * @param callable $fCallback : the callback function to execute
	 * @param array $aChannels : the list of channels (only used by Redis pub/sub)
	 */
	public static function onMessage( callable $fCallback, array $aChannels = null )
	{
		self::getInstance()->m_cPubSub->onMessage( $fCallback, $aChannels );

		if ( self::getInstance()->m_cPubSub instanceof PubSubComponentZmq )
		{
			self::getInstance()->m_cPubSub->pull( $fCallback );
		}
	}


	//added by Oliver Chong - April 15, 2015
	/**
	 * Handles the WAMP request through an Ajax post
	 * @param array $aRequest : the array of request parameters from $_REQUEST
	 * @throws Exception : if insufficient parameters were provided
	 */
	public static function handleRequest( array $aRequest )
	{
		try
		{
			switch ( $aRequest[ WampConstants::MODE ] )
			{
				//publish the message to the channel through ZMQ or Redis
				case WampConstants::MODE_PUBSUB_ZMQ:
				case WampConstants::MODE_PUBSUB_REDIS:
					switch ( $aRequest[ WampConstants::WAMP_ACTION ] )
					{
						//publish
						case WampConstants::WAMP_ACTION_PUBLISH:
							if ( !isset( $aRequest[ WampConstants::CHANNEL ] ) )
							{
								throw new \Exception( "Missing request parameter: " . WampConstants::CHANNEL );
							}
							else if ( !isset( $aRequest[ WampConstants::MSG ] ) )
							{
								throw new \Exception( "Missing request parameter: " . WampConstants::MSG );
							}
							else
							{
								self::publish( $aRequest[ WampConstants::CHANNEL ], $aRequest[ WampConstants::MSG ] );
							}
							break;

						//subscribe
						case WampConstants::WAMP_ACTION_SUBSCRIBE:
							if ( !isset( $aRequest[ WampConstants::CHANNEL ] ) )
							{
								throw new \Exception( "Missing request parameter: " . WampConstants::CHANNEL );
							}
							else
							{
								self::subscribe( $aRequest[ WampConstants::CHANNEL ] );
							}
							break;

						//unsubscribe
						case WampConstants::WAMP_ACTION_SUBSCRIBE:
							if ( !isset( $aRequest[ WampConstants::CHANNEL ] ) )
							{
								throw new \Exception( "Missing request parameter: " . WampConstants::CHANNEL );
							}
							else
							{
								self::unsubscribe( $aRequest[ WampConstants::CHANNEL ] );
							}
							break;

						default:
							throw new \Exception( "Missing request parameter: " . WampConstants::WAMP_ACTION );
							break;

					}//end switch
					break;

				case WampConstants::MODE_NORMAL:
					break;

				default:
					throw new \Exception( "Missing request parameter: " . WampConstants::MODE );
					break;

			}//end switch
		}
		catch ( Exception $e )
		{
			echo Util::formatException( $e );
		}
	}

}//end class

?>