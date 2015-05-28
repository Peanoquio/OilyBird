<?php

namespace OilyBird\CacheMgr\Component;


use OilyBird\CacheMgr\CacheRecord;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The interface for the caching API
 * @author oliver.chong
 *
 */
interface CacheInterface
{
	/**
	 * Checks if the key exists
	 *
	 * @param string $szKey : the key to uniquely identify the entry in the cache store
	 * @return boolean : if true, the key exists
	 */
	public function exists( $szKey );


	/**
	 * Deletes the key-value pair
	 *
	 * @param string $szKey : the key to uniquely identify the entry in the cache store
	 */
	public function delete( $szKey );


	/**
	 * Retrieves the stored value
	 *
	 * @param string $szKey : the key to uniquely identify the entry in the cache store
	 * @return string|number|array : the stored value
	 */
	public function get( $szKey );


	/**
	 * Stores the key-value pair
	 *
	 * @param string $szKey : the key to uniquely identify the entry in the cache store
	 * @param string|number|array $value : the value to be stored
	 * @param unsigned $nExpireTimeInSecs : the duration before the key-value expires from the cache
	 * @param boolean $bIsDuration : if true, the expiry will be duration-based else it will be a scheduled time-based
	 */
	public function set( $szKey, $value, $nExpireTimeInSecs = null, $bIsDuration = true );


	/**
	 * Increments the counter
	 *
	 * @param string $szKey : the key to uniquely identify the entry in the cache store
	 * @param unsigned $nIncrementVal : the number to increase the value by
	 */
	public function incr( $szKey, $nIncrementVal = 1 );


	/**
	 * Decrements the counter
	 *
	 * @param string $szKey : the key to uniquely identify the entry in the cache store
	 * @param unsigned $nIncrementVal : the number to decrease the value by
	 */
	public function decr( $szKey, $nDecrementVal = 1 );


	/**
	 * Gets all the registered keys
	 *
	 * @param string $sSearchParam : the search parameter with the * wildcard symbol (example: "*man*" will return "batman", "mandate", etc.)
	 * @param string $szDataType : the data type (CacheDataType)
	 * @return array : the array of keys
	 */
	public function getKeys( $sSearchParam, $szDataType );


	/**
	 * Gets all the registered keys and the corresponding data types
	 *
	 * @param string $sSearchParam : the search parameter with the * wildcard symbol (example: "*man*" will return "batman", "mandate", etc.)
	 * @return array : the array of keys and types
	 */
	public function getKeysAndTypes( $sSearchParam );


	/**
	 * Gets all the registered keys and the corresponding values
	 *
	 * @param string $sSearchParam : the search parameter with the * wildcard symbol (example: "*man*" will return "batman", "mandate", etc.)
	 * @param string $szDataType : the data type (CacheDataType)
	 * @return array : the array of keys and values
	 */
	public function getKeysAndValues( $sSearchParam, $szDataType );


	/**
	 * Clears the data from the cache
	 *
	 * @param boolean $bFlushAll : if true, delete all data from the entire cache, if false, delete all data from the current cache database
	 */
	public function clearData( $bFlushAll );


	/**
	 * Sets multiple values at once based on the provided array of key-value pairs (DOES NOT WORK IN CLUSTER MODE)
	 *
	 * @param array $aKeyValuePairs : the array of key value pairs
	 * @param unsigned $nExpireTimeInSecs : the duration before the key-value expires from the cache
	 * @param boolean $bIsDuration : if true, the expiry will be duration-based else it will be a scheduled time-based
	 * @return integer : the expiry time in seconds (if -1, it will not expire)
	 */
	public function setMultiple( array $aKeyValuePairs, $nExpireTimeInSecs = null, $bIsDuration = true );


	/**
	 * Gets multiple values at once based on the keys provided (DOES NOT WORK IN CLUSTER MODE)
	 *
	 * @param array $aKeys : the keys
	 * @return array : the array of values
	 */
	public function getMultiple( array $aKeys );


	/**
	 * Add record
	 *
	 * @param CacheRecord $cCacheRecord : the instance of CacheRecord class that will contain the fields and data to be inserted
	 */
	public function addRecord( CacheRecord $cCacheRecord );


	/**
	 * Update record
	 *
	 * @param CacheRecord $cCacheRecord : the instance of CacheRecord class that will contain the fields and data to be updated
	 */
	public function updateRecord( CacheRecord $cCacheRecord );


	/**
	 * Delete record
	 *
	 * @param CacheRecord $cCacheRecord : the instance of CacheRecord class that will contain the fields and data to be deleted
	 */
	public function deleteRecord( CacheRecord $cCacheRecord );


	/**
	 * Processes the transaction based on the provided records
	 *
	 * @param array $aCacheRecords : the instances of CacheRecord class that will contain the fields and data to be inserted/updated/deleted
	 */
	public function processRecords( array $aCacheRecords );


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
	public function addToList( $szKey, array $aValues, $nCap, $nExpireSecs, $bAddToEnd );


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
	public function popFromList( $szKey, $nIndex, $bPopRight );


	/**
	 * Adds elements to the specified sorted set (if it is already existing, it updates the value of the element)
	 *
	 * @param string $szKey : the unique key to identify the sorted set
	 * @param array $aValues : the values to be added to the sorted set
	 * @param unsigned $nExpireSecs : the expire time duration in seconds
	 * @throws Exception : if the argument passed is of the wrong type
	 * @return array : the sorted set that have been added with values
	 */
	public function upsertSortedSet( $szKey, array $aValues, $nExpireSecs );


	/**
	 * Removes elements from the sorted set based on the specified index range
	 *
	 * @param string $szKey : the unique key to identify the sorted set
	 * @param integer $nStartingIndex : the starting index from where we will start removing the elements (inclusive)
	 * @param integer $nEndingIndex : the ending index until where we will remove elements (inclusive)
	 * @param array : the list of elements that have been removed
	 */
	public function removeFromSortedSetByIndex( $szKey, $nStartingIndex, $nEndingIndex );


	/**
	 * Removes elements from the sorted set based on the specified elements
	 *
	 * @param string $szKey : the unique key to identify the sorted set
	 * @param array $nElementKeys : the list of elements to be removed
	 * @return unsigned : the number of elements removed
	 */
	public function removeFromSortedSet( $szKey, array $nElementKeys );

}//end interface


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>