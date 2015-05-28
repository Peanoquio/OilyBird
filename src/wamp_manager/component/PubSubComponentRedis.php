<?php


namespace OilyBird\WampMgr\Component;

use React\EventLoop\LoopInterface;

use Predis\Async\Client;
use Predis\Async\PubSub\Consumer;

use OilyBird\Conf\Config;
use OilyBird\WampMgr\Component\PubSubInterface;
use OilyBird\CacheMgr\Component\CacheComponentRedis;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The publish-subscribe component that makes use of Redis
 * @author oliver.chong
 *
 */
class PubSubComponentRedis implements PubSubInterface
{
	//the Redis cache object
	private $m_cCache;
	//the asynchronous version of the Redis cache object
	private $m_cCacheAsync;


	//added by Oliver Chong - April 16, 2015
	/**
	 * The constructor for the PubSubRedis class
	 */
	public function __construct( LoopInterface $loop = null )
	{
		if ( $loop && $loop instanceof LoopInterface )
		{
			//the Predis Async instance
			$sPredisServerPath = "tcp://".Config::getRedisIP().":".Config::getRedisPort();
			$this->m_cCacheAsync = new Client( $sPredisServerPath, $loop );
		}

		//initialize the Redis cache object
		$this->m_cCache = new CacheComponentRedis();
	}


	//added by Oliver Chong - April 16, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\WampMgr.PubSubInterface::onMessage()
	 */
	public function onMessage( callable $fCallback, array $aChannels = null )
	{
		//connect to the Redis server
		$this->m_cCacheAsync->connect( function ( Client $client ) use ( $fCallback, $aChannels ) {

			echo "Connected to Redis, now listening for incoming messages...\n";

			//the publish / subscribe loop
			$client->pubSubLoop( $aChannels, function ( $cPayloadObj, Consumer $pubsub ) use ( $fCallback, $aChannels ) {

				var_dump( "********************** pubSubLoop ", $cPayloadObj );

				switch ( $cPayloadObj->kind )
				{
					case 'subscribe':
						echo "Subscribed to {$cPayloadObj->channel}", PHP_EOL;
						break;

					case 'message':
						var_dump( "type message", $cPayloadObj->kind, $aChannels );
						if ( !empty( $aChannels ) )
						{
							if ( is_string( $aChannels )
									|| ( is_array( $aChannels ) && array_key_exists( $cPayloadObj->channel, $aChannels[ "subscribe" ] ) ) )
							{
								if ( $cPayloadObj->payload == 'quit_loop' )
								{
									echo "Aborting pubsub loop...", PHP_EOL;
									$pubsub->unsubscribe();
									$pubsub->quit();
								}
								else
								{
									var_dump( "PubSubredis onMessage", $cPayloadObj );

									$aMsg = array( $cPayloadObj->channel, $cPayloadObj->payload );

									call_user_func_array( $fCallback, array( json_encode( $aMsg, JSON_NUMERIC_CHECK ) ) );
								}
							}
						}
						break;

					default:
						var_dump( "PubSubredis onMessage : unhandled payload type $cPayloadObj->kind", $cPayloadObj );
						break;
				}
			});

		});
	}


	/**
	 * (non-PHPdoc)
	 * @see OilyBird\WampMgr.PubSubInterface::publish()
	 */
	public function publish( $sChannel, $sMsg )
	{
		return $this->m_cCache->publish( $sChannel, $sMsg );
	}


	/**
	 * (non-PHPdoc)
	 * @see OilyBird\WampMgr.PubSubInterface::subscribe()
	 */
	public function subscribe( $sChannel )
	{
		$this->m_cCache->subscribe( $sChannel );
	}


	/**
	 * (non-PHPdoc)
	 * @see OilyBird\WampMgr.PubSubInterface::unsubscribe()
	 */
	public function unsubscribe( $sChannel )
	{
		$this->m_cCache->unsubscribe( $sChannel );
	}

}//end class


?>