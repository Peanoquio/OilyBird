<?php

namespace OilyBird\CacheMgr;


use OilyBird\Common\Singleton;
use OilyBird\CacheMgr\Component\CacheBase;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The cache manager that interacts with the cache store (uses the strategy and singleton design patterns)
 * @author oliver.chong
 *
 */
class CacheManager extends Singleton
{
	//the specific cache server class
	private $m_cCache;

	//the current database index
	private static $s_nCurrDbIndex = 0;


	/**
	 * The constructor of the CacheManager
	 *
	 * @param CacheBase $cCache : an instance of the class that inherits from the CacheBase base class
	 */
	public function __construct( CacheBase $cCache )
	{
		$this->m_cCache = $cCache;
	}


	/**
	 * Initializes the CacheManager with the cache object being passed (it will be handled polymorphically)
	 *
	 * @param CacheBase $cCache : an instance of the class that inherits from the CacheBase base class
	 */
	public static function initialize( CacheBase $cCache )
	{
		return self::getInstance( $cCache );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//GENERIC


	/**
	 * Checks if the key exists
	 *
	 * @param string $szKey : the key to uniquely identify the entry in the cache store
	 * @return boolean : if true, the key exists
	 */
	public static function exists( $szKey )
	{
		return self::getInstance()->m_cCache->exists( $szKey );
	}


	/**
	 * Deletes the key-value pair
	 *
	 * @param string $szKey : the key to uniquely identify the entry in the cache store
	 */
	public static function delete( $szKey )
	{
		return self::getInstance()->m_cCache->delete( $szKey );
	}


	/**
	 * Retrieves the stored value
	 *
	 * @param string $szKey : the key to uniquely identify the entry in the cache store
	 * @return string|number|array|object : the stored value
	 */
	public static function get( $szKey, $nType = null )
	{
		$value = self::getInstance()->m_cCache->get( $szKey, $nType );
		$value = is_string( $value ) ? unserialize( $value ) : $value;
		return $value;
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//STRINGS / NUMBERS


	/**
	 * Stores the key-value pair
	 *
	 * @param string $szKey : the key to uniquely identify the entry in the cache store
	 * @param string|number|array|object $value : the value to be stored
	 * @param unsigned $nExpireTimeInSecs : the duration before the key-value expires from the cache
	 * @param boolean $bIsDuration : if true, the expiry will be duration-based else it will be a scheduled time-based
	 */
	public static function set( $szKey, $value, $nExpireTimeInSecs = null, $bIsDuration = true )
	{
		return self::getInstance()->m_cCache->set( $szKey, serialize( $value ), $nExpireTimeInSecs, $bIsDuration );
	}


	/**
	 * Increments the counter
	 *
	 * @param string $szKey : the key to uniquely identify the entry in the cache store
	 * @param unsigned $nIncrementVal : the number to increase the value by
	 */
	public static function incr( $szKey, $nIncrVal = 1 )
	{
		return self::getInstance()->m_cCache->incr( $szKey, $nIncrVal );
	}


	/**
	 * Decrements the counter
	 *
	 * @param string $szKey : the key to uniquely identify the entry in the cache store
	 * @param unsigned $nIncrementVal : the number to decrease the value by
	 */
	public static function decr( $szKey, $nDecrVal = 1 )
	{
		return self::getInstance()->m_cCache->decr( $szKey, $nDecrVal );
	}


	/**
	 * Sets multiple values at once based on the provided array of key-value pairs (DOES NOT WORK IN CLUSTER MODE)
	 *
	 * @param array $aKeyValuePairs : the array of key value pairs
	 * @param unsigned $nExpireTimeInSecs : the duration before the key-value expires from the cache
	 * @param boolean $bIsDuration : if true, the expiry will be duration-based else it will be a scheduled time-based
	 * @return integer : the expiry time in seconds (if -1, it will not expire)
	 */
	public static function setMultiple( array $aKeyValuePairs, $nExpireTimeInSecs = null, $bIsDuration = true )
	{
		return self::getInstance()->m_cCache->setMultiple( $aKeyValuePairs, $nExpireTimeInSecs, $bIsDuration );
	}


	/**
	 * Gets multiple values at once based on the keys provided (DOES NOT WORK IN CLUSTER MODE)
	 *
	 * @param array $aKeys : the keys
	 * @return array : the array of values
	 */
	public static function getMultiple( array $aKeys )
	{
		return self::getInstance()->m_cCache->getMultiple( $aKeys );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//CACHE DATABASE


	/**
	 * Clears the data from the cache
	 *
	 * @param boolean $bFlushAll : if true, delete all data from the entire cache, if false, delete all data from the current cache database
	 */
	public static function clearData( $bFlushAll )
	{
		return self::getInstance()->m_cCache->clearData( $bFlushAll );
	}


	/**
	 * Selects a database in the cache
	 *
	 * @param boolean $nDatabaseIndex : the database index
	 */
	public static function selectDatabase( $nDatabaseIndex )
	{
		self::$s_nCurrDbIndex = $nDatabaseIndex;
		return self::getInstance()->m_cCache->selectDatabase( $nDatabaseIndex );
	}


	/**
	 * Gets the current database used in the cache
	 *
	 * @return unsigned : the database index
	 */
	public static function getCurrentDatabase()
	{
		return self::$s_nCurrDbIndex;
	}


	/**
	 * Gets the maximum allowed number of database instances in the cache
	 *
	 * @return unsigned : the maximum allowed number of database instances in the cache
	 */
	public static function getMaxDatabaseCount()
	{
		return self::getInstance()->m_cCache->getMaxDatabaseCount();
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//QUERY KEYS


	/**
	 * Gets all the registered keys
	 *
	 * @param string $sSearchParam : the search parameter with the * wildcard symbol (example: "*man*" will return "batman", "mandate", etc.)
	 * @param string $szDataType : the data type (CacheDataType)
	 * @return array : the array of keys
	 */
	public static function getKeys( $sSearchParam, $szDataType = null )
	{
		return self::getInstance()->m_cCache->getKeys( $sSearchParam, $szDataType );
	}


	/**
	 * Gets all the registered keys and the corresponding data types
	 *
	 * @param string $sSearchParam : the search parameter with the * wildcard symbol (example: "*man*" will return "batman", "mandate", etc.)
	 * @return array : the array of keys and types
	 */
	public static function getKeysAndTypes( $sSearchParam )
	{
		return self::getInstance()->m_cCache->getKeysAndTypes( $sSearchParam );
	}


	/**
	 * Gets all the registered keys and the corresponding values
	 *
	 * @param string $sSearchParam : the search parameter with the * wildcard symbol (example: "*man*" will return "batman", "mandate", etc.)
	 * @param string $szDataType : the data type (CacheDataType)
	 * @return array : the array of keys and values
	 */
	public static function getKeysAndValues( $sSearchParam, $szDataType = null )
	{
		return self::getInstance()->m_cCache->getKeysAndValues( $sSearchParam, $szDataType );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//HASH SETS


	/**
	 * Processes the transaction based on the provided records
	 *
	 * @param array $aCacheRecords : the instances of CacheRecord class that will contain the fields and data to be inserted/updated/deleted
	 */
	public static function processRecords( array $aCacheRecords, $nMode = CacheTransactionType::MODE_TRANSACTION )
	{
		return self::getInstance()->m_cCache->processRecords( $aCacheRecords, $nMode );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//LISTS


	/**
	 * Adds elements to the specified list
	 *
	 * @param string $szKey : the unique key to identify the list
	 * @param array $aValues : the values to be added to the list
	 * @param unsigned $nCap : the cap to limit the size of the list
	 * @param unsigned $nExpireSecs : the expire time duration in seconds
	 * @param boolean $bAddToEnd : if true, add the values to the end of the list, otherwise add the values to the front
	 * @throws Exception : if the argument passed is of the wrong type
	 * @return array : the lists that have been added with values
	 */
	public static function addToList( $szKey, array $aValues, $nCap, $nExpireSecs, $bAddToEnd /* supports more arguments [ key, array, nCap, nExpireSecs, bAddToEnd ... ] */ )
	{
		return call_user_func_array( array( self::getInstance()->m_cCache, "addToList" ), func_get_args() );
	}


	/**
	 * Removes elements from the specified list (supports more arguments)
	 *
	 * @param string $szKey : the unique key to identify the list
	 * @param unsigned $nIndex : if the flag to pop right is on, the index will signify the starting element (until the last element) to be popped from the list
	 *							 if the flag to pop right is off (pop left), the index will signify the ending element (from the first element) to be popped from the list
	 * @param boolean $bPopRight : if true, remove the specified values from the end of the list, otherwise remove from the front of the list
	 * @throws Exception : if the argument passed is of the wrong type
	 * @return array : the values that have been removed from the respective lists
	 */
	public static function popFromList( $szKey, $nIndex, $bPopRight /* supports more arguments [ key, array, nCap, nExpireSecs, bAddToEnd ... ] */ )
	{
		return call_user_func_array( array( self::getInstance()->m_cCache, "popFromList" ), func_get_args() );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//SORTED SETS


	/**
	 * Adds elements to the specified sorted set (if it is already existing, it updates the value of the element)
	 *
	 * @param string $szKey : the unique key to identify the sorted set
	 * @param array $aValues : the values to be added to the sorted set
	 * @param unsigned $nExpireSecs : the expire time duration in seconds
	 * @throws Exception : if the argument passed is of the wrong type
	 * @return array : the sorted set that have been added with values
	 */
	public static function upsertSortedSet( $szKey, array $aValues, $nExpireSecs /* supports more arguments [ key, array, nExpireSecs ... ] */ )
	{
		return call_user_func_array( array( self::getInstance()->m_cCache, "upsertSortedSet" ), func_get_args() );
	}


	/**
	 * Removes elements from the sorted set based on the specified index range
	 *
	 * @param string $szKey : the unique key to identify the sorted set
	 * @param integer $nStartingIndex : the starting index from where we will start removing the elements (inclusive)
	 * @param integer $nEndingIndex : the ending index until where we will remove elements (inclusive)
	 * @param array : the list of elements that have been removed
	 */
	public static function removeFromSortedSetByIndex( $szKey, $nStartingIndex, $nEndingIndex /* supports more arguments [ key, nStartingIndex, nEndingIndex ... ] */ )
	{
		return call_user_func_array( array( self::getInstance()->m_cCache, "removeFromSortedSetByIndex" ), func_get_args() );
	}


	/**
	 * Removes elements from the sorted set based on the specified elements
	 *
	 * @param string $szKey : the unique key to identify the sorted set
	 * @param array $nElementKeys : the list of elements to be removed
	 * @return unsigned : the number of elements removed
	 */
	public static function removeFromSortedSet( $szKey, array $nElementKeys )
	{
		return call_user_func_array( array( self::getInstance()->m_cCache, "removeFromSortedSet" ), func_get_args() );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//PUB / SUB


	/**
	 * Subscribe to a channel
	 *
	 * @param string $sChannelName : the channel name
	 */
	public static function subscribe( $sChannelName )
	{
		return self::getInstance()->m_cCache->pubSubLoop()->subscribe( $sChannelName );
	}


	/**
	 * Publish a message to the channel
	 *
	 * @param string $sChannelName : the channel name
	 * @param string $sMsg : the message to be published to the channel
	 * @return unsigned : the number of subscribers of the channel
	 */
	public static function publish( $sChannelName, $sMsg )
	{
		return self::getInstance()->m_cCache->publish( $sChannelName, $sMsg );
	}

}//end class


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>