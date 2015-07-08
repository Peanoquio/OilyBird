<?php

namespace OilyBird\Common;


use \ReflectionClass;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The base class for the Singleton design pattern
 * @author oliver.chong
 *
 */
abstract class Singleton
{
	//the *Singleton* instance of this class.
	//private static $m_cInstance;

	//the array of "Singleton" instances
	//the reason why an array is used is so that this Singleton base class can be extended by multiple but unique child classes
	//for example: in a single state, the Singleton can only be initialized by one DatabaseManager, one CacheManager (there cannot be more than one of each)
	private static $m_aInstance = array();


	/**
	 * Gets the instance of the Singleton class
	 * @param mixed : supports dynamic arguments
	 * @return object : the instance of the Singleton class
	 */
	public static function getInstance( /* supports dynamic arguments */ )
	{
		//get the name of the class that calls this static function
		$szChildClass = get_called_class();

		//if ( !isset( self::$m_cInstance ) || self::$m_cInstance == null )
		if ( empty( self::$m_aInstance ) || !array_key_exists( $szChildClass, self::$m_aInstance) )
		{
			$aArgs = func_get_args();

			if ( !empty( $aArgs ) )
			{
				//instantiate the class dynamically
				//https://thomas.rabaix.net/blog/2009/02/how-to-instantiate-a-php-class-with-dynamic-parameters
				self::$m_aInstance[ $szChildClass ] = call_user_func_array( array( new ReflectionClass( $szChildClass ), "newInstance" ), $aArgs );
			}
			else
			{
				//return the class name that invoked the static method
				//http://stackoverflow.com/questions/5197300/new-self-vs-new-static
				self::$m_aInstance[ $szChildClass ] = new static();
			}

			//var_dump( "added new class instance", $szChildClass, "instances in this Singleton", self::$m_aInstance );
		}

		return self::$m_aInstance[ $szChildClass ];
	}


	/**
	 * Prevent instantiating the class through the new operator
	 */
	protected function __construct()
	{
	}


	/**
	 * Prevent cloning of this class instance
	 */
	private function __clone()
	{
	}


	/**
	 * Prevent unserializing of this class instance
	 */
	private function __wakeup()
	{
	}

}//end class

?>