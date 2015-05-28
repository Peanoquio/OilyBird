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
	private static $m_cInstance;


	/**
	 * Gets the instance of the Singleton class
	 * @param mixed : supports dynamic arguments
	 * @return object : the instance of the Singleton class
	 */
	public static function getInstance( /* supports dynamic arguments */ )
	{
		if ( !isset( self::$m_cInstance ) || self::$m_cInstance == null )
		{
			$aArgs = func_get_args();

			if ( !empty( $aArgs ) )
			{
				//instantiate the class dynamically
				//https://thomas.rabaix.net/blog/2009/02/how-to-instantiate-a-php-class-with-dynamic-parameters
				self::$m_cInstance = call_user_func_array( array( new ReflectionClass( get_called_class() ), "newInstance" ), $aArgs );
			}
			else
			{
				//return the class name that invoked the static method
				//http://stackoverflow.com/questions/5197300/new-self-vs-new-static
				self::$m_cInstance = new static();
			}
		}

		return self::$m_cInstance;
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