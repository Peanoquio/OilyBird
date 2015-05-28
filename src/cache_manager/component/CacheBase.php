<?php

namespace OilyBird\CacheMgr\Component;


use OilyBird\CacheMgr\Component\CacheInterface;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The base class for the caching API
 * @author oliver.chong
 *
 */
abstract class CacheBase implements CacheInterface
{
	//the list of registered cache servers
	protected static $CACHE_SERVERS;

	//the constant server parameters
	protected static $SERVER_PARAMS = array( "IP" => 0, "PORT" => 1 );


	/**
	 * The constructor of the BaseCache class
	 */
	public function __construct()
	{
		self::$CACHE_SERVERS = array();
	}


	/**
	 * Initializes the custom cache class
	 */
	abstract protected function init();


	/**
	 * Registers the server
	 *
	 * @param string $szCacheType : the caching type that corresponds to the API (Redis, Memcache, AeroSpike, etc.)
	 * @param string $szIpAddress : the IP address of the cache server
	 * @param string $sPort : the port number
	 */
	final protected function registerServer( $szCacheType, $szIpAddress, $sPort )
	{
		self::$CACHE_SERVERS[ $szCacheType ][] = array( self::$SERVER_PARAMS["IP"] => $szIpAddress, self::$SERVER_PARAMS["PORT"] => $sPort );
	}


	/**
	 * Gets the list of registered servers
	 *
	 * @param string $szCacheType : the caching type that corresponds to the API (Redis, Memcache, AeroSpike)
	 */
	final protected function getServers( $szCacheType )
	{
		return self::$CACHE_SERVERS[ $szCacheType ];
	}


	/**
	 * Log the exception
	 *
	 * @param Exception $e : the exception object that contains data about the error
	 */
	protected function logException( \Exception $e )
	{
		echo( "Exception code: " . $e->getCode() . " message: " . $e->getMessage() ." in line: " . $e->getLine() . " file: " . $e->getFile() );
		echo( $e->getTraceAsString() );
	}


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//this is to follow the Template design pattern (since some APIs might not support certain functions if defined in the interface)


	//Redis database functions

	/**
	 * Selects a database in the cache
	 *
	 * @param boolean $nDatabaseIndex : the database index
	 */
	public function selectDatabase( $nDatabaseIndex )
	{
		return null;
	}


	/**
	 * Gets the maximum allowed number of database instances in the cache
	 *
	 * @return unsigned : the maximum allowed number of database instances in the cache
	 */
	public function getMaxDatabaseCount()
	{
		return null;
	}

}//end class

?>