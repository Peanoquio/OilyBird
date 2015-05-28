<?php

namespace OilyBird\Conf;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The class that contains the configuration parameters
 * @author oliver.chong
 *
 */
abstract class Config
{
	//the parsed contents of the configuration file
	private static $s_aConfig;


	/**
	 * Reads the configuration file
	 */
	private static function readConfig()
	{
		if ( empty( self::$s_aConfig ) )
		{
			//read and parse the configuration file
			self::$s_aConfig = parse_ini_file( "conf.ini", true );

			//var_dump( self::$s_aConfig );
		}
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//DATABASE (SQL)


	/**
	 * Gets the database IP
	 * @return string : the database IP
	 */
	public static function getDatabaseIP()
	{
		self::readConfig();

		return self::$s_aConfig["database"]["ip"];
	}


	/**
	 * Gets the database port
	 * @return unsigned : the database port
	 */
	public static function getDatabasePort()
	{
		self::readConfig();

		return intval( self::$s_aConfig["database"]["port"] );
	}


	/**
	 * Gets the database user name
	 * @return string : the database user name
	 */
	public static function getDatabaseUsername()
	{
		self::readConfig();

		return self::$s_aConfig["database"]["username"];
	}


	/**
	 * Gets the database password
	 * @return string : the database password
	 */
	public static function getDatabasePassword()
	{
		self::readConfig();

		return self::$s_aConfig["database"]["password"];
	}


	/**
	 * Gets the default database schema
	 * @return string : the default database schema
	 */
	public static function getDatabaseDefaultSchema()
	{
		self::readConfig();

		return self::$s_aConfig["database"]["default_db"];
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//CACHE (REDIS)


	/**
	 * Gets the Redis cache IP
	 * @return string : the Redis cache IP
	 */
	public static function getRedisIP()
	{
		self::readConfig();

		return self::$s_aConfig["redis"]["ip"];
	}


	/**
	 * Gets the Redis cache port
	 * @return unsigned : the Redis cache port
	 */
	public static function getRedisPort()
	{
		self::readConfig();

		return intval( self::$s_aConfig["redis"]["port"] );
	}


	/**
	 * Gets the Redis cache password
	 * @return string : the Redis cache password
	 */
	public static function getRedisPassword()
	{
		self::readConfig();

		return self::$s_aConfig["redis"]["password"];
	}


	/**
	 * Gets the Redis cache password
	 * @return string : the Redis cache password
	 */
	public static function getRedisTransactionRetryCount()
	{
		self::readConfig();

		return self::$s_aConfig["redis"]["transaction_retry_count"];
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//WEBSOCKET


	/**
	 * Gets the websocket server IP
	 * @return string : the websocket server IP
	 */
	public static function getWebsocketIP()
	{
		self::readConfig();

		return self::$s_aConfig["websocket"]["ip"];
	}


	/**
	 * Gets the websocket server port
	 * @return unsigned : the websocket server port
	 */
	public static function getWebsocketPort()
	{
		self::readConfig();

		return intval( self::$s_aConfig["websocket"]["port"] );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//WAMP (PUB/SUB)


	/**
	 * Gets the WAMP Pub/Sub server IP
	 * @return string : the WAMP Pub/Sub server IP
	 */
	public static function getWampPubSubIP()
	{
		self::readConfig();

		return self::$s_aConfig["wamp_pubsub"]["ip"];
	}


	/**
	 * Gets the WAMP Pub/Sub server port
	 * @return unsigned : the WAMP Pub/Sub server port
	 */
	public static function getWampPubSubPort()
	{
		self::readConfig();

		return intval( self::$s_aConfig["wamp_pubsub"]["port"] );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//WAMP (ZMQ)


	/**
	 * Gets the WAMP ZMQ server IP
	 * @return string : the WAMP ZMQ server IP
	 */
	public static function getWampZmqIP()
	{
		self::readConfig();

		return self::$s_aConfig["wamp_zmq"]["ip"];
	}


	/**
	 * Gets the WAMP ZMQ server port
	 * @return unsigned : the WAMP ZMQ server port
	 */
	public static function getWampZmqPort()
	{
		self::readConfig();

		return intval( self::$s_aConfig["wamp_zmq"]["port"] );
	}


}//end class

?>