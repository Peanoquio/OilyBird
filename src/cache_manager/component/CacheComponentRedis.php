<?php

namespace OilyBird\CacheMgr\Component;


use Predis\Autoloader;
use Predis\Client;
use Predis\Transaction\MultiExec;
use Predis\ClientContextInterface;
use Predis\Pipeline\Pipeline;
use Predis\Response\Status;
use Predis\Collection\Iterator\Keyspace;

use OilyBird\Conf\Config;
use OilyBird\CacheMgr\CacheRecord;
use OilyBird\CacheMgr\Component\CacheBase;
use OilyBird\CacheMgr\Constants\CacheDataType;
use OilyBird\CacheMgr\Constants\CacheTransactionType;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The custom class that wraps the Predis library to interact with the Redis cache
 * @author oliver.chong
 *
 */
class CacheComponentRedis extends CacheBase
{
	//the class name
	private $m_szClassName;

	//the Predis object
	private $m_cCacheObj;

	//the default clustering mode
	private $m_sClusterMode;

	//the prefix to be prepended to the keys
	private $m_sKeyPrefix;

	//the flag to determine if the pipeline outputs will be formatted
	private $m_bFormatPipelineOutput;

	//the different supported cluster modes
	private static $CLUSTER_MODE = array( "PREDIS" => "predis", "REDIS" => "redis" );



	//added by Oliver Chong - March 15, 2015
	/**
	 * The constructor for the RedisCache class
	 *
	 * @param string $szKeyPrefix : the prefix to be prepended to the keys
	 * @param string $sClusterMode : the cluster mode to use
	 * @param boolean $bFormatPipelineOutput : the flag to determine if the pipeline outputs will be formatted
	 */
	public function __construct( $szKeyPrefix = "", $sClusterMode = null, $bFormatPipelineOutput = true )
	{
		$this->m_sKeyPrefix = $szKeyPrefix;

		$this->m_sClusterMode = ( $sClusterMode == null ) ? self::$CLUSTER_MODE["PREDIS"] : $sClusterMode;

		$this->m_bFormatPipelineOutput = $bFormatPipelineOutput;

		parent::__construct();

		$this->init();
	}


	//added by Oliver Chong - March 15, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheBase::init()
	 */
	protected function init()
	{
		Autoloader::register();

		$this->m_szClassName = get_class( $this );

		//I intentionally called this multiple times to test the cluster mode of Redis
		$this->registerServer( $this->m_szClassName, Config::getRedisIP(), Config::getRedisPort() );
		//$this->registerServer( $this->m_szClassName, Config::getRedisIP(), Config::getRedisPort() );
		//$this->registerServer( $this->m_szClassName, Config::getRedisIP(), Config::getRedisPort() );

		try
		{
			//get the list of servers
			$aServers = $this->getServerList();
			$nServerCnt = count( $aServers );

			//server connection parameters
			$aOptions = array();
			$aOptions[ "prefix" ] = $this->m_sKeyPrefix;

			//if there more than 1 server specified
			if ( $nServerCnt > 1 )
			{
				//set the clustering mode
				$aOptions[ "cluster" ] = $this->m_sClusterMode;
			}
			else
			{
				$aServers = $aServers[ 0 ];
			}

			//var_dump( self::$CACHE_SERVERS, $aServers, $aOptions );

			$this->m_cCacheObj = new Client( $aServers, $aOptions );

			//authenticate based on the password
			//$this->m_cCacheObj->auth( Config::getRedisPassword() );

			//show the redis.conf
			//var_dump( $this->m_cCacheObj->config( "GET", "*" ) );

			//set the required password to access the cache (should be done in redis.conf)
			//$this->m_cCacheObj->config( "SET", "requirepass", Config::getRedisPassword() );
		}
		catch ( \Exception $e )
		{
			$this->logException( $e );
		}
	}


	//added by Oliver Chong - March 15, 2015
	/**
	 * Gets the list of servers in the Predis format
	 *
	 * @return array : the list of servers in the Predis format
	 */
	private function getServerList()
	{
		$aFormattedServerList = array();

		//get the registered Redis servers
		$aServers = $this->getServers( $this->m_szClassName );

		for ( $index = 0, $nServerCnt = count( $aServers ); $index < $nServerCnt; ++$index )
		{
			$aServerObj = $aServers[ $index ];

			//update to the Redis format for the server connection parameters
			$aFormattedServerList[] = array(
					"scheme" => "tcp",
					"host" => $aServerObj[ self::$SERVER_PARAMS["IP"] ],
					"port" => $aServerObj[ self::$SERVER_PARAMS["PORT"] ]
			);
		}//end loop

		return $aFormattedServerList;
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//CACHE DATABASE


	//added by Oliver Chong - March 17, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::clearData()
	 */
	public function clearData( $bFlushAll )
	{
		if ( $bFlushAll )
		{
			return $this->m_cCacheObj->flushall();
		}
		else
		{
			return $this->m_cCacheObj->flushdb();
		}
	}


	//added by Oliver Chong - March 16, 2015
	/**
	 * Selects a database in the cache
	 *
	 * @param boolean $nDatabaseIndex : the database index
	 */
	public function selectDatabase( $nDatabaseIndex )
	{
		return $this->m_cCacheObj->select( $nDatabaseIndex );
	}


	//added by Oliver Chong - March 16, 2015
	/**
	 * Gets the maximum allowed number of database instances in the cache
	 *
	 * @return unsigned : the maximum allowed number of database instances in the cache
	 */
	public function getMaxDatabaseCount()
	{
		//this will read from the Redis config file
		$aDatabases = $this->m_cCacheObj->config( "GET", "databases" );

		return intval( $aDatabases["databases"] );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//PUB / SUB


	//added by Oliver Chong - April 9, 2015
	/**
	 * Subscribe to a channel
	 *
	 * @param string $sChannelName : the channel name
	 */
	public function subscribe( $sChannelName )
	{
		$this->m_cCacheObj->pubSubLoop()->subscribe( $sChannelName );
	}


	//added by Oliver Chong - April 9, 2015
	/**
	 * Unsubscribe from a channel
	 *
	 * @param string $sChannelName : the channel name
	 */
	public function unsubscribe( $sChannelName )
	{
		$this->m_cCacheObj->pubSubLoop()->unsubscribe( $sChannelName );
	}


	//added by Oliver Chong - April 9, 2015
	/**
	 * Publish a message to the channel
	 *
	 * @param string $sChannelName : the channel name
	 * @param string $sMsg : the message to be published to the channel
	 * @return unsigned : the number of subscribers of the channel
	 */
	public function publish( $sChannelName, $sMsg )
	{
		return $this->m_cCacheObj->publish( $sChannelName, $sMsg );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//GENERIC


	//added by Oliver Chong - March 17, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::exists()
	 */
	public function exists( $szKey )
	{
		return $this->m_cCacheObj->exists( $szKey );
	}


	//added by Oliver Chong - March 17, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::delete()
	 */
	public function delete( $szKey )
	{
		return $this->m_cCacheObj->del( $szKey );
	}


	//added by Oliver Chong - March 17, 2015
	/**
	 * (non-PHPdoc)
	 * @see CacheInterface::get()
	 */
	public function get( $szKey, $nType = null )
	{
		$nType = ( $nType == null ) ? $this->m_cCacheObj->type( $szKey ) : $nType;

		switch( $nType )
		{
			default:
			case CacheDataType::STRING:
				return $this->m_cCacheObj->get( $szKey );
				break;
			case CacheDataType::HASH:
				return $this->m_cCacheObj->hgetall( $szKey );
				break;
			case CacheDataType::LISTS:
				return $this->m_cCacheObj->lrange( $szKey, 0, -1 );
				break;
			case CacheDataType::SORTED_SET:
				return $this->m_cCacheObj->zrange( $szKey, 0, -1, array( "withscores" => true ) );
				break;
		}
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//STRINGS / NUMBERS


	//added by Oliver Chong - March 17, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::set()
	 */
	public function set( $szKey, $value, $nExpireTimeInSecs = null, $bIsDuration = true )
	{
		$this->m_cCacheObj->set( $szKey, $value );
		if ( $nExpireTimeInSecs )
		{
			if ( $bIsDuration )
			{
				$this->m_cCacheObj->expire( $szKey, $nExpireTimeInSecs );
			}
			else
			{
				$this->m_cCacheObj->expireat( $szKey, $nExpireTimeInSecs );
			}
		}

		return $this->m_cCacheObj->ttl( $szKey );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//MULTIPLE STRINGS / NUMBERS
	//DOES NOT WORK IN REDIS CLUSTER MODE


	//added by Oliver Chong - March 18, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::setMultiple()
	 */
	public function setMultiple( array $aKeyValuePairs, $nExpireTimeInSecs = null, $bIsDuration = true )
	{
		$this->m_cCacheObj->mset( $aKeyValuePairs );
		if ( $nExpireTimeInSecs )
		{
			if ( $bIsDuration )
			{
				foreach ( $aKeyValuePairs as $szKey => $value )
				{
					$this->m_cCacheObj->expire( $szKey, $nExpireTimeInSecs );
				}//end loop
			}
			else
			{
				foreach ( $aKeyValuePairs as $szKey => $value )
				{
					$this->m_cCacheObj->expireat( $szKey, $nExpireTimeInSecs );
				}//end loop
			}
		}

		return $this->m_cCacheObj->ttl( $szKey );
	}


	//added by Oliver Chong - March 18, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::getMultiple()
	 */
	public function getMultiple( array $aKeys )
	{
		return $this->m_cCacheObj->mget( $aKeys );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//COUNTER


	//added by Oliver Chong - March 17, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::incr()
	 */
	public function incr( $szKey, $nIncrementVal = 1 )
	{
		return $this->m_cCacheObj->incrby( $szKey, $nIncrementVal );
	}


	//added by Oliver Chong - March 17, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::decr()
	 */
	public function decr( $szKey, $nDecrementVal = 1 )
	{
		return $this->m_cCacheObj->decrby( $szKey, $nDecrementVal );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//PROCESS RECORDS (HASH SET / TABLE)


	//added by Oliver Chong - April 14, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::processRecords()
	 */
	public function processRecords( array $aCacheRecords, $nMode = CacheTransactionType::MODE_TRANSACTION )
	{
		$aResponse = null;

		switch ( $nMode )
		{
			case CacheTransactionType::MODE_TRANSACTION:
				$aResponse = $this->processRecordsTrans( $aCacheRecords );
				break;

			case CacheTransactionType::MODE_PIPELINE:
				$aResponse = $this->processRecordsPipe( $aCacheRecords );
				break;
		}

		return $aResponse;
	}


	//added by Oliver Chong - March 19, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::deleteRecord()
	 */
	public function deleteRecord( CacheRecord $cCacheRecord, ClientContextInterface $pipeTrans = null )
	{
		$nNoOfRecordsDeleted = 0;

		$nId = $cCacheRecord->getTableName() .":". $cCacheRecord->getId();

		if ( !$nId )
		{
			throw new \Exception( "Must provide unique key/id to delete the record" );
		}
		else
		{
			$nNoOfRecordsDeleted = $pipeTrans->del( $nId );
		}

		return $nNoOfRecordsDeleted;
	}


	//added by Oliver Chong - March 19, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::addRecord()
	 */
	public function addRecord( CacheRecord $cCacheRecord, ClientContextInterface $pipeTrans = null )
	{
		if ( $pipeTrans instanceof Pipeline )
		{
			return $this->upsertPipe( $pipeTrans, $cCacheRecord );
		}
		else if ( $pipeTrans instanceof MultiExec )
		{
			return $this->upsertTrans( $pipeTrans, $cCacheRecord );
		}
	}


	//added by Oliver Chong - March 19, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::updateRecord()
	 */
	public function updateRecord( CacheRecord $cCacheRecord, ClientContextInterface $pipeTrans = null )
	{
		if ( $pipeTrans instanceof Pipeline )
		{
			return $this->upsertPipe( $pipeTrans, $cCacheRecord );
		}
		else if ( $pipeTrans instanceof MultiExec )
		{
			return $this->upsertTrans( $pipeTrans, $cCacheRecord );
		}
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// TRANSACTION


	//added by Oliver Chong - April 14, 2015
	//if you want to learn more about Redis transactions, please read the following:
	//https://github.com/StackExchange/StackExchange.Redis/blob/master/Docs/Transactions.md
	//http://redis.io/topics/transactions
	//https://redis-docs.readthedocs.org/en/latest/MultiExecCommand.html
	/**
	 * Handles placing the function call within a transaction
	 *
	 * @param Closure $fCallback : the callback function in the form of a closure
	 * @param array $aParams : the parameters to be passed to the callback function
	 */
	public function transaction( \Closure $fCallback, array $aParams = array() )
	{
		//IMPORTANT NOTE: if CAS is enabled
		//1. we need to call MULTI before the WATCH( key ) commands
		//2. explicit DISCARD call does not work since it will be delegated to the transaction EXEC command if the key being WATCHed was changed midway through the transaction
		//3. transaction will not return a response

		$aOptions = array(
				// Initialize with support for CAS (optimistic check and set) operations
				'cas'   => true,
				// Number of retries on aborted transactions, after which the client bails out with an exception.
				'retry' => Config::getRedisTransactionRetryCount(),
		);

		$aResponses = null;

		try
		{
			//transactions in Predis library do not return values
			$aResponses = $this->m_cCacheObj->transaction( $aOptions, function( MultiExec $tx ) use ( $fCallback, $aParams ) {

				if ( is_callable( $fCallback ) )
				{
					if ( is_array( $aParams ) )
					{
						//add transaction object to the beginning of the element
						array_unshift( $aParams, $tx );
					}

					//invoke the callback function
					call_user_func_array( $fCallback, $aParams );
				}
			} );
		}
		catch ( \Exception $e )
		{
			$this->logException( $e );
			exit;
		}

		return $aResponses;
	}


	//added by Oliver Chong - April 14, 2015
	/**
	 * Processes the transaction based on the provided records through a transaction
	 *
	 * @param array $aCacheRecords : the instances of CacheRecord class that will contain the fields and data to be inserted/updated/deleted
	 */
	private function processRecordsTrans( array $aCacheRecords )
	{
		//the callback function
		$fCallback = function( MultiExec $tx, CacheComponentRedis $cCacheObj, array $aCacheRecords )
		{
			//go through the tables to be processed
			foreach( $aCacheRecords as $szKey => $cCacheRecord )
			{
				//watch the keys for changes
				//if the keys change, Predis will auto-rollback and retry the transaction
				if ( $cCacheRecord instanceof CacheRecord )
				{
					$nId = $cCacheRecord->getId();

					//insert transaction
					if ( !$nId && $cCacheRecord->getCacheTransactionType() == CacheTransactionType::INSERT )
					{
						$szTableName = $cCacheRecord->getTableName();
						//this stores the auto-increment id that will serve as the key for new records to be inserted
						$szIdCounterKey = $szTableName . ":id";
						$tx->watch( $szIdCounterKey );
					}
					//update transaction
					else if ( $nId && $cCacheRecord->getCacheTransactionType() == CacheTransactionType::UPDATE )
					{
						$tx->watch( $nId );
					}
					//update/delete transaction without providing the id
					else if ( !$nId &&  ( $cCacheRecord->getCacheTransactionType() == CacheTransactionType::UPDATE || $cCacheRecord->getCacheTransactionType() == CacheTransactionType::DELETE ) )
					{
						throw new \Exception( "Must provide unique key/id to update/delete the record" );
					}
				}
			}//end loop

			//if CAS (check and set mode), the transaction should start with MULTI
			$tx->multi();

			$bBreakLoop = false;

			//go through the tables to be processed
			foreach ( $aCacheRecords as $szKey => $cCacheRecord )
			{
				if ( $bBreakLoop )
				{
					break;
				}

				if ( $cCacheRecord instanceof CacheRecord )
				{
					//check the transaction type
					switch( $cCacheRecord->getCacheTransactionType() )
					{
						case CacheTransactionType::INSERT:
							if ( !$cCacheObj->addRecord( $cCacheRecord, $tx ) )
							{
								$tx->discard();
								$bBreakLoop = true;
							}
							break;

						case CacheTransactionType::UPDATE:
							if ( !$cCacheObj->updateRecord( $cCacheRecord, $tx ) )
							{
								$tx->discard();
								$bBreakLoop = true;
							}
							break;

						case CacheTransactionType::DELETE:
							$nNoOfRecordsDeleted = $cCacheObj->deleteRecord( $cCacheRecord, $tx );
							break;

						default:
							throw new \Exception( "Process transaction can only support INSERT / UPDATE / DELETE operations" );
							$bBreakLoop = true;
							break;

					}//end switch
				}
				else
				{
					throw new \Exception( "The contents of the array should be instances of CacheRecord class" );
					$bBreakLoop = true;
					break;
				}
			}//end

		};//end callback

		//execute the callback function through the pipeline/transaction
		return $this->transaction( $fCallback, array( /*RedisCache*/ $this, $aCacheRecords ) );
	}


	//added by Oliver Chong - April 14, 2015
	/**
	 * Handles inserting and updating records in the cache as an atomic transaction
	 *
	 * @param Predis\Transaction\MultiExec $tx : the Predis API transaction object that supports transactions
	 * @param CacheRecord $cCacheRecord : the instance of the CacheRecord class that contains the field and data
	 * @throws Exception : if there is no unique key/id provided when updating a record
	 */
	private function upsertTrans( MultiExec $tx, CacheRecord &$cCacheRecord )
	{
		$szTableName = $cCacheRecord->getTableName();
		$aProps = $cCacheRecord->getAllFields();
		$nId = $cCacheRecord->getId();
		$nExpireSecs = $cCacheRecord->getExpireSecs();

		//depending on the transaction, check if the record id has been provided
		if ( !$nId && $cCacheRecord->getCacheTransactionType() == CacheTransactionType::UPDATE )
		{
			throw new \Exception( "Must provide unique key/id to update the record" );
		}
		else if ( !$nId && $cCacheRecord->getCacheTransactionType() == CacheTransactionType::INSERT )
		{
			//generate the id of the record
			$szIdCounterKey = $szTableName . ":id";
			$nId = $tx->incr( $szIdCounterKey );
		}

		$szKey = $szTableName.":".$nId;

		$bSuccess = false;

		//go through the fields of the table
		foreach ( $aProps as $szFieldName => $value )
		{
			$bSuccess = $tx->hset( $szKey, $szFieldName, $value );
			if ( !$bSuccess )
			{
				throw new \Exception( "Transaction failed midway for hash key:".$szKey." field:".$szFieldName." value:".$value );
				break;
			}
		}//end loop

		if ( $nExpireSecs )
		{
			$tx->expire( $szKey, $nExpireSecs );
		}

		return $bSuccess;
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// PIPELINE


	//added by Oliver Chong - March 19, 2015
	/**
	 * Handles placing the function call within a pipeline
	 *
	 * @param boolean $bAtomicTransaction : if true, the pipeline will become an atomic transaction
	 * @param Closure $fCallback : the callback function in the form of a closure
	 * @param array $aParams : the parameters to be passed to the callback function
	 */
	public function pipeline( $bAtomicTransaction, \Closure $fCallback, array $aParams = array() )
	{
		$aResponses = null;

		try
		{
			//https://github.com/nrk/predis/releases
			//atomic: wraps the pipeline in a MULTI / EXEC transaction (Predis\Pipeline\Atomic).
			//fire-and-forget: does not read back responses (Predis\Pipeline\FireAndForget).
			$aOptions = $bAtomicTransaction ? array( "atomic" ) : null;

			$aResponses = $this->m_cCacheObj->pipeline( $aOptions, function( Pipeline $pipe ) use ( $fCallback, $aParams ) {

				if ( is_callable( $fCallback ) )
				{
					if ( is_array( $aParams ) )
					{
						//add pipe object to the beginning of the element
						array_unshift( $aParams, $pipe );
					}

					//invoke the callback function
					call_user_func_array( $fCallback, $aParams );
				}
			} );

			//var_dump( "pipeline ------------- response", $aResponses );
		}
		catch ( \Exception $e )
		{
			$this->logException( $e );
			exit;
		}

		return $aResponses;
	}


	//added by Oliver Chong - March 19, 2015
	/**
	 * Processes the transaction based on the provided records through a pipeline
	 *
	 * @param array $aCacheRecords : the instances of CacheRecord class that will contain the fields and data to be inserted/updated/deleted
	 * @param boolean $bShowResponse : if true, show the response once the pipeline completes
	 */
	private function processRecordsPipe( array $aCacheRecords, $bShowResponse = true )
	{
		//the callback function
		$fCallback = function( Pipeline $pipe, CacheComponentRedis $cCacheObj, array $aCacheRecords, $bShowResponse )
		{
			$bBreakLoop = false;

			//go through the tables to be processed
			foreach( $aCacheRecords as $szKey => $cCacheRecord )
			{
				if ( $bBreakLoop )
				{
					break;
				}

				if ( $cCacheRecord instanceof CacheRecord )
				{
					//check the transaction type
					switch( $cCacheRecord->getCacheTransactionType() )
					{
						case CacheTransactionType::INSERT:
							$cCacheObj->addRecord( $cCacheRecord, $pipe );
							break;

						case CacheTransactionType::UPDATE:
							$cCacheObj->updateRecord( $cCacheRecord, $pipe );
							break;

						case CacheTransactionType::DELETE:
							$cCacheObj->deleteRecord( $cCacheRecord, $pipe );
							break;

						default:
							throw new \Exception( "Process transaction can only support INSERT / UPDATE / DELETE operations" );
							$bBreakLoop = true;
							break;

					}//end switch

					if ( $bShowResponse )
					{
						//this is to get the stored values
						$pipe->hgetall( $szKey );
					}
				}
				else
				{
					throw new \Exception( "The contents of the array should be instances of CacheRecord class" );
					$bBreakLoop = true;
					break;
				}
			}//end

		};//end callback

		//execute the callback function through the pipeline/transaction
		return $this->pipeline( true, $fCallback, array( /*RedisCache*/ $this, $aCacheRecords, $bShowResponse ) );
	}


	//added by Oliver Chong - March 19, 2015
	/**
	 * Handles inserting and updating records in the cache
	 *
	 * @param Predis\Pipeline\Pipeline $pipe : the Predis API pipe object that supports pipelining and transactions
	 * @param CacheRecord $cCacheRecord : the instance of the CacheRecord class that contains the field and data
	 * @throws Exception : if there is no unique key/id provided when updating a record
	 */
	private function upsertPipe( Pipeline $pipe, CacheRecord $cCacheRecord )
	{
		$szTableName = $cCacheRecord->getTableName();
		$aProps = $cCacheRecord->getProperties();
		$nId = $cCacheRecord->getId();
		$nExpireSecs = $cCacheRecord->getExpireSecs();

		//depending on the transaction, check if the record id has been provided
		if ( !$nId && $cCacheRecord->getCacheTransactionType() == CacheTransactionType::UPDATE )
		{
			throw new \Exception( "Must provide unique key/id to update the record" );
		}
		else if ( !$nId && $cCacheRecord->getCacheTransactionType() == CacheTransactionType::INSERT )
		{
			//generate the id of the record
			$nId = $this->m_cCacheObj->incr( $szTableName . ":id" );
		}

		$szKey = $szTableName.":".$nId;

		//go through the fields of the table
		foreach ( $aProps as $szFieldName => $value )
		{
			$pipe->hset( $szKey, $szFieldName, $value );
		}//end loop

		if ( $nExpireSecs )
		{
			$pipe->expire( $szKey, $nExpireSecs );
		}
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//QUERY KEYS / TYPES / VALUES


	//added by Oliver Chong - May 21, 2015
	/**
	 * The helper function to get the list of keys in the cache database
	 * @param unknown_type $sSearchParam : the search parameter (with wildcard search support) to find the matching keys
	 * @param boolean $bUseScan : if true, use the Redis SCAN instead of the slower and blocking KEYS
	 * @return array : the list of keys in the cache database that macthed the search criteria
	 */
	private function getKeysHelper( $sSearchParam, $bUseScan = true )
	{
		$aKeys = array();

		if ( $bUseScan )
		{
			//http://redis.io/commands/scan
			//https://github.com/nrk/predis/blob/v1.0/examples/redis_collections_iterators.php
			foreach ( new Keyspace( $this->m_cCacheObj, $sSearchParam ) as $szKey )
			{
				array_push( $aKeys, $szKey );
			}//end loop
		}
		else
		{
			return $this->m_cCacheObj->keys( $sSearchParam );
		}

		return $aKeys;
	}


	//added by Oliver Chong - March 24, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::getKeys()
	 */
	public function getKeys( $sSearchParam, $nDataType = null )
	{
		$aKeys = $this->getKeysHelper( $sSearchParam );

		if ( !$nDataType )
		{
			return $aKeys;
		}
		//search for the keys filtered by data type
		else
		{
			$aFilteredKeys = array();

			//the callback function
			$fCallback = function( Pipeline $pipe, array $aKeys )
			{
				//get the corresponding data types based on the keys
				for ( $index = 0, $nLen = count( $aKeys ); $index < $nLen; ++$index )
				{
					$pipe->type( $aKeys[ $index ] );
				}//end loop
			};//end callback

			//execute the callback function through the pipeline/transaction
			$aResponse = $this->pipeline( false, $fCallback, array( $aKeys ) );

			//get the response that contains the type
			for ( $i = 0, $nLen = count( $aResponse ); $i < $nLen; ++$i )
			{
				//instance of Predis\Response\Status
				$cStatus = $aResponse[ $i ];

				if ( $cStatus instanceof Status )
				{
					//if the data type matches
					if ( $nDataType == $cStatus->getPayload() )
					{
						array_push( $aFilteredKeys, $aKeys[ $i ] );
					}
				}
				else
				{
					throw new \Exception( "Not an instance of Predis\Response\Status" );
					break;
				}
			}//end loop

			return $aFilteredKeys;
		}
	}


	//added by Oliver Chong - March 25, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::getKeysAndTypes()
	 */
	public function getKeysAndTypes( $sSearchParam )
	{
		$aKeysAndTypes = array();

		//get the registered keys
		$aKeys = $this->getKeys( $sSearchParam );

		//the callback function
		$fCallback = function( Pipeline $pipe, array $aKeys )
		{
			//get the corresponding data types based on the keys
			for ( $index = 0, $nLen = count( $aKeys ); $index < $nLen; ++$index )
			{
				$pipe->type( $aKeys[ $index ] );
			}//end loop
		};//end callback

		//execute the callback function through the pipeline/transaction
		$aResponse = $this->pipeline( false, $fCallback, array( $aKeys ) );

		//get the response that contains the type
		for ( $i = 0, $nLen = count( $aResponse ); $i < $nLen; ++$i )
		{
			//instance of Predis\Response\Status
			$cStatus = $aResponse[ $i ];

			if ( $cStatus instanceof Status )
			{
				$aKeysAndTypes[ $aKeys[ $i ] ] = $cStatus->getPayload();
			}
			else
			{
				throw new \Exception( "Not an instance of Predis\Response\Status" );
				break;
			}
		}//end loop

		return $aKeysAndTypes;
	}


	//added by Oliver Chong - March 25, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::getKeysAndValues()
	 */
	public function getKeysAndValues( $sSearchParam, $szDataType = null )
	{
		$aKeysAndValues = array();

		//get the list of registered keys
		$aKeysAndTypes = $this->getKeysAndTypes( $sSearchParam );
		$aKeysAndTypesFiltered = array();

		//the callback function
		$fCallback = function( Pipeline $pipe, array $aKeysAndTypes, array &$aKeysAndTypesFiltered, $szDataType )
		{
			//get the corresponding values based on the key and data type
			foreach ( $aKeysAndTypes as $szKey => $szCacheDataType )
			{
				//skip the non-matching data types (if provided)
				if ( $szDataType && $szDataType != $szCacheDataType )
				{
					continue;
				}

				$aKeysAndTypesFiltered[ $szKey ] = $szCacheDataType;

				switch ( $szCacheDataType )
				{
					case CacheDataType::HASH:
						$pipe->hgetall( $szKey );
						break;
					case CacheDataType::STRING:
						$pipe->get( $szKey );
						break;
					case CacheDataType::LISTS:
						$pipe->lrange( $szKey, 0, -1 );
						break;
					case CacheDataType::SORTED_SET:
						$pipe->zrange( $szKey, 0, -1, array( "withscores" => true ) );
						break;
				}
			}//end loop
		};//end callback

		//execute the callback function through the pipeline/transaction
		$aResponse = $this->pipeline( false, $fCallback, array( $aKeysAndTypes, &$aKeysAndTypesFiltered, $szDataType ) );

		//get the response that contains the type
		for ( $i = 0, $nLen = count( $aResponse ); $i < $nLen; ++$i )
		{
			$aKeysAndValues[ key( $aKeysAndTypesFiltered ) ] = $aResponse[ $i ];

			next( $aKeysAndTypesFiltered );
		}//end loop

		return $aKeysAndValues;
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//LISTS


	//added by Oliver Chong - March 31, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::addToList()
	 */
	public function addToList( $szKey, array $aValues, $nCap, $nExpireSecs, $bAddToEnd /* supports more arguments [ key, array, nCap, nExpireSecs, bAddToEnd ... ] */ )
	{
		$aArgs = func_get_args();
		$nArgLen = count( $aArgs );

		//the callback function
		$fCallback = function( Pipeline $pipe, array $aArgs, $nArgLen )
		{
			$nNumOfArgumentTypes = 5;

			$szKey = null;
			$aValues = null;
			$nCap = null;
			$nExpireSecs = null;
			$bAddToEnd = null;

			//go through the arguments alternating between the first, second and third argument types
			for ( $index = 0; $index < $nArgLen; ++$index )
			{
				$nArgIndex = $index % $nNumOfArgumentTypes;

				switch ( $nArgIndex )
				{
					//first argument
					case 0:
						//the unique key
						$szKey = $aArgs[ $index ];
						if ( !is_string( $szKey ) )
						{
							throw new \Exception( "(list: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be a string that will serve as the unique key." );
						}
						break;
						//second argument
					case 1:
						//the list values
						$aValues = $aArgs[ $index ];
						if ( !is_array( $aValues ) )
						{
							throw new \Exception( "(list: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be an array that contains the values." );
						}
						break;
						//third argument
					case 2:
						//the cap to limit the list size
						$nCap = $aArgs[ $index ];
						if ( !is_numeric( $nCap ) )
						{
							throw new \Exception( "(list: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be a number that limits the list size." );
						}
						break;
						//fourth argument
					case 3:
						//the expire time duration in seconds
						$nExpireSecs = $aArgs[ $index ];
						if ( !is_numeric( $nExpireSecs ) )
						{
							throw new \Exception( "(list: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be a number that sets the expire time duration in seconds." );
						}
						break;
						//fifth argument
					case 4:
						//check if the values will be added to the end / front of the list
						$bAddToEnd = $aArgs[ $index ];
						if ( !is_bool( $bAddToEnd ) )
						{
							throw new \Exception( "(list: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be a boolean indicating whether to push the values to the left or right of the list." );
						}
						else
						{
							for ( $x = 0, $nValuesLen = count( $aValues ); $x < $nValuesLen; ++$x )
							{
								//add to the cache list
								if ( $bAddToEnd )
								{
									$pipe->rpush( $szKey, $aValues[ $x ] );
								}
								else
								{
									$pipe->lpush( $szKey, $aValues[ $x ] );
								}
							}//end loop

							//cap the list size
							$pipe->ltrim( $szKey, 0, ( $nCap - 1 ) );

							//expire time
							$pipe->expire( $szKey, $nExpireSecs );

							if ( $this->m_bFormatPipelineOutput )
							{
								//show the key
								$pipe->echo( $szKey );

								//show the values of the newly added list
								$pipe->lrange( $szKey, 0, -1 );
							}

							unset( $szKey );
							unset( $aValues );
							unset( $nCap );
							unset( $nExpireSecs );
							unset( $bAddToEnd );
						}
						break;
				}

			}//end loop

		};//end callback

		//execute the callback function through the pipeline/transaction
		$aResponse = $this->pipeline( true, $fCallback, array( $aArgs, $nArgLen ) );

		if ( !$this->m_bFormatPipelineOutput )
		{
			return $aResponse;
		}
		else
		{
			$aFormattedResponse = array();
			$szKey = "";

			for ( $i = 0, $nLen = count( $aResponse ); $i < $nLen; ++$i )
			{
				$mResponseVal = $aResponse[ $i ];

				//key
				if ( is_string( $mResponseVal ) )
				{
					$szKey = $mResponseVal;
				}
				//list values
				else if ( is_array( $mResponseVal ) )
				{
					$aFormattedResponse[ $szKey ] = $mResponseVal;
				}
			}//end loop

			//var_dump( "addToList ---------------- aFormattedResponse", $aFormattedResponse );

			return $aFormattedResponse;
		}
	}


	//added by Oliver Chong - March 31, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::popFromList()
	 */
	public function popFromList( $szKey, $nIndex, $bPopRight /* supports more arguments [ key, nIndex, bPopRight ... ] */ )
	{
		$aArgs = func_get_args();
		$nArgLen = count( $aArgs );

		//the callback function
		$fCallback = function( Pipeline $pipe, array $aArgs, $nArgLen )
		{
			$nNumOfArgumentTypes = 3;

			$szKey = null;
			$nIndex = null;
			$bPopRight = null;

			//go through the arguments alternating between the first, second and third argument types
			for ( $index = 0; $index < $nArgLen; ++$index )
			{
				$nArgIndex = $index % $nNumOfArgumentTypes;

				//first argument type
				if ( $nArgIndex == 0 )
				{
					//the unique key
					$szKey = $aArgs[ $index ];
					if ( !is_string( $szKey ) )
					{
						throw new \Exception( "(list: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be a string that will serve as the unique key." );
					}
				}
				//second argument type
				else if ( $nArgIndex == 1 )
				{
					//if the flag to pop right is on, the index will signify the starting element (until the last element) to be popped from the list
					//if the flag to pop right is off (pop left), the index will signify the ending element (from the first element) to be popped from the list
					$nIndex = $aArgs[ $index ];
					if ( !is_numeric( $nIndex ) )
					{
						throw new \Exception( "(list: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be a number that determines the starting/ending index to pop in the list." );
					}
				}
				//last argument type
				else
				{
					//check if the values will be removed from the end / front of the list
					$bPopRight = $aArgs[ $index ];
					if ( !is_bool( $bPopRight ) )
					{
						throw new \Exception( "(list: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be a boolean indicating whether to pop the values from the left or right of the list." );
					}
					else
					{
						if ( $this->m_bFormatPipelineOutput )
						{
							$pipe->echo( $szKey );

							//show the values to be popped from the list
							if ( $bPopRight )
							{
								$pipe->lrange( $szKey, ( $nIndex + 1 ), -1 );
							}
							else
							{
								$pipe->lrange( $szKey, 0, $nIndex );
							}
						}

						//remove the values from the list
						if ( $bPopRight )
						{
							$pipe->ltrim( $szKey, 0, $nIndex );
						}
						else
						{
							$pipe->ltrim( $szKey, ( $nIndex + 1 ), -1 );
						}

						unset( $szKey );
						unset( $nIndex );
						unset( $bPopRight );
					}
				}
			}//end loop

		};//end callback

		//execute the callback function through the pipeline/transaction
		$aResponse = $this->pipeline( true, $fCallback, array( $aArgs, $nArgLen ) );

		if ( !$this->m_bFormatPipelineOutput )
		{
			return $aResponse;
		}
		else
		{
			$aPoppedValues = array();
			$szKey = "";
			$aPoppedVal = array();
			$nNumOfResponseTypes = 3;

			//formats the response
			for ( $i = 0, $nLen = count( $aResponse ); $i < $nLen; ++$i )
			{
				$mResponseVal = $aResponse[ $i ];

				//key
				if ( $i % $nNumOfResponseTypes == 0 )
				{
					$szKey = $mResponseVal;
				}
				//popped values
				else if ( $i % $nNumOfResponseTypes == 1 )
				{
					$aPoppedVal = $mResponseVal;
				}
				//status
				else
				{
					if ( $mResponseVal instanceof Status )
					{
						if ( $mResponseVal->getPayload() == "OK" )
						{
							$aPoppedValues[ $szKey ] = $aPoppedVal;
						}
						else
						{
							throw new \Exception( $mResponseVal->getPayload() );
							break;
						}
					}
					else
					{
						throw new \Exception( "Not an instance of Predis\Response\Status" );
						break;
					}
				}
			}//end loop

			//var_dump( "popFromList ---------------- aPoppedValues", $aPoppedValues );

			return $aPoppedValues;
		}
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//SORTED SETS


	//added by Oliver Chong - May 4, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::upsertSortedSet()
	 */
	public function upsertSortedSet( $szKey, array $aValues, $nExpireSecs /* supports more arguments [ key, array, nExpireSecs, ... ] */ )
	{
		$aArgs = func_get_args();
		$nArgLen = count( $aArgs );

		//the callback function
		$fCallback = function( Pipeline $pipe, array $aArgs, $nArgLen )
		{
			$nNumOfArgumentTypes = 3;

			$szKey = null;
			$aValues = null;
			$nExpireSecs = null;

			//go through the arguments alternating between the first, second and third argument types
			for ( $index = 0; $index < $nArgLen; ++$index )
			{
				$nArgIndex = $index % $nNumOfArgumentTypes;

				switch ( $nArgIndex )
				{
					//first argument
					case 0:
						//the unique key
						$szKey = $aArgs[ $index ];
						if ( !is_string( $szKey ) )
						{
							throw new \Exception( "(sorted_set: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be a string that will serve as the unique key." );
						}
						break;
					//second argument
					case 1:
						//the list values
						$aValues = $aArgs[ $index ];
						if ( !is_array( $aValues ) )
						{
							throw new \Exception( "(sorted_set: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be an array that contains the values." );
						}
						break;
					//third argument
					case 2:
						//the expire time duration in seconds
						$nExpireSecs = $aArgs[ $index ];
						if ( !is_numeric( $nExpireSecs ) )
						{
							throw new \Exception( "(sorted_set: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be a number that sets the expire time duration in seconds." );
						}

						foreach ( $aValues as $keyValue => $nScore )
						{
							$pipe->zadd( $szKey, $nScore, $keyValue );
						}//end loop

						//expire time
						$pipe->expire( $szKey, $nExpireSecs );

						if ( $this->m_bFormatPipelineOutput )
						{
							//show the key
							$pipe->echo( $szKey );

							//show the values of the newly added list
							$pipe->zrange( $szKey, 0, -1, array( "withscores" => true ) ); //'limit' => array(1, 2),  // [0]: offset / [1]: count
						}

						unset( $szKey );
						unset( $aValues );
						unset( $nExpireSecs );

						break;
				}

			}//end loop

		};//end callback

		//execute the callback function through the pipeline/transaction
		$aResponse = $this->pipeline( true, $fCallback, array( $aArgs, $nArgLen ) );

		if ( !$this->m_bFormatPipelineOutput )
		{
			return $aResponse;
		}
		else
		{
			$aFormattedResponse = array();
			$szKey = "";

			for ( $i = 0, $nLen = count( $aResponse ); $i < $nLen; ++$i )
			{
				$mResponseVal = $aResponse[ $i ];

				//key
				if ( is_string( $mResponseVal ) )
				{
					$szKey = $mResponseVal;
				}
				//list values
				else if ( is_array( $mResponseVal ) )
				{
					$aFormattedResponse[ $szKey ] = $mResponseVal;
				}
			}//end loop

			//var_dump( "upsertSortedSet ---------------- aFormattedResponse", $aFormattedResponse );

			return $aFormattedResponse;
		}
	}


	//added by Oliver Chong - May 5, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::removeFromSortedSetByIndex()
	 */
	public function removeFromSortedSetByIndex( $szKey, $nStartingIndex, $nEndingIndex /* supports more arguments [ key, nStartingIndex, nEndingIndex ... ] */ )
	{
		$aArgs = func_get_args();
		$nArgLen = count( $aArgs );

		//the callback function
		$fCallback = function( Pipeline $pipe, array $aArgs, $nArgLen )
		{
			$nNumOfArgumentTypes = 3;

			$szKey = null;
			$nStartingIndex = null;
			$nEndingIndex = null;

			//go through the arguments alternating between the first, second and third argument types
			for ( $index = 0; $index < $nArgLen; ++$index )
			{
				$nArgIndex = $index % $nNumOfArgumentTypes;

				//first argument type
				if ( $nArgIndex == 0 )
				{
					//the unique key
					$szKey = $aArgs[ $index ];
					if ( !is_string( $szKey ) )
					{
						throw new \Exception( "(list: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be a string that will serve as the unique key." );
					}
				}
				//second argument type
				else if ( $nArgIndex == 1 )
				{
					$nStartingIndex = $aArgs[ $index ];
					if ( !is_numeric( $nStartingIndex ) )
					{
						throw new \Exception( "(list: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be a number that determines the starting index to pop in the list." );
					}
				}
				//last argument type
				else
				{
					$nEndingIndex = $aArgs[ $index ];
					if ( !is_numeric( $nEndingIndex ) )
					{
						throw new \Exception( "(list: $szKey) Argument " . ( $nArgIndex + 1 ) ." has to be a number that determines the ending index to pop in the list." );
					}
					else
					{
						if ( $this->m_bFormatPipelineOutput )
						{
							$pipe->echo( $szKey );

							//show the values to be popped from the sorted set
							$pipe->zrange( $szKey, $nStartingIndex, $nEndingIndex, array( "withscores" => true ) );
						}

						//pop the values from the sorted set
						$pipe->zremrangebyrank( $szKey, $nStartingIndex, $nEndingIndex );

						unset( $szKey );
						unset( $nStartingIndex );
						unset( $nEndingIndex );
					}
				}
			}//end loop

		};//end callback

		//execute the callback function through the pipeline/transaction
		$aResponse = $this->pipeline( true, $fCallback, array( $aArgs, $nArgLen ) );

		if ( !$this->m_bFormatPipelineOutput )
		{
			return $aResponse;
		}
		else
		{
			$aPoppedValues = array();
			$szKey = "";
			$aPoppedVal = array();
			$nNumOfResponseTypes = 3;

			//formats the response
			for ( $i = 0, $nLen = count( $aResponse ); $i < $nLen; ++$i )
			{
				$mResponseVal = $aResponse[ $i ];

				//key
				if ( $i % $nNumOfResponseTypes == 0 )
				{
					$szKey = $mResponseVal;
				}
				//popped values
				else if ( $i % $nNumOfResponseTypes == 1 )
				{
					$aPoppedVal = $mResponseVal;
				}
				//number of removed elements
				else
				{
					if ( $mResponseVal > 0 )
					{
						$aPoppedValues[ $szKey ] = $aPoppedVal;
					}
				}
			}//end loop

			//var_dump( "removeFromSortedSetByIndex ---------------- aPoppedValues", $aPoppedValues );

			return $aPoppedValues;
		}
	}


	//added by Oliver Chong - May 5, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\CacheMgr.CacheInterface::removeFromSortedSet()
	 */
	public function removeFromSortedSet( $szKey, array $nElementKeys )
	{
		//the callback function
		$fCallback = function( Pipeline $pipe, $szKey, array $nElementKeys )
		{
			//append the sorted set key and the element keys into a single array
			$aParams = $nElementKeys;
			array_unshift( $aParams, $szKey );

			//remove the specified elements from the sorted set
			call_user_func_array( array( $pipe, "zrem" ), $aParams );

		};//end callback

		//execute the callback function through the pipeline/transaction
		$aResponse = $this->pipeline( true, $fCallback, array( $szKey, $nElementKeys ) );

		//var_dump( "removeFromSortedSet ----------------", $aResponse );

		//the number of elements removed
		return $aResponse[ 0 ];
	}

}//end class


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>