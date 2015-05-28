<?php

namespace OilyBird\DatabaseMgr;

use OilyBird\Conf\Constants;
use OilyBird\Common\Util;
use OilyBird\Common\Singleton;
use OilyBird\DatabaseMgr\DatabaseRecord;
use OilyBird\DatabaseMgr\Component\DatabaseComponentBase;

use PDO;
use PDOStatement;
use PDOException;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The class that handles the database
 * @author oliver.chong
 *
 */
class DatabaseManager extends Singleton
{
	//the specific database component class
	private $m_cDbComponent;


	/**
	 * The constructor of the DatabaseManager
	 * @param DatabaseComponentBase $cDbComponent : an instance of the class that inherits from the DatabaseComponentBase base class
	 */
	public function __construct( DatabaseComponentBase $cDbComponent )
	{
		$this->m_cDbComponent = $cDbComponent;
	}


	/**
	 * Initializes the DatabaseManager with the database component object being passed (it will be handled polymorphically)
	 * @param DatabaseComponentBase $cDbComponent : an instance of the class that inherits from the DatabaseComponentBase base class
	 */
	public static function initialize( DatabaseComponentBase $cDbComponent )
	{
		return self::getInstance( $cDbComponent );
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	/**
	 * Select the database schema to use
	 * @param string $szDbSchemaName : the database schema name
	 */
	public static function useSchema( $szDbSchemaName )
	{
		return self::getInstance()->m_cDbComponent->useSchema( $szDbSchemaName );
	}


	/**
	 * Begins the transaction
	 * @return boolean : if successful, return true
	 */
	public static function beginTransaction()
	{
		return self::getInstance()->m_cDbComponent->beginTransaction();
	}


	/**
	 * Commits the transaction
	 * @return boolean : if successful, return true
	 */
	public static function commit()
	{
		return self::getInstance()->m_cDbComponent->commit();
	}


	/**
	 * Rolls back the transaction
	 * @return boolean : if successful, return true
	 */
	public static function rollBack()
	{
		return self::getInstance()->m_cDbComponent->rollBack();
	}


	/**
	 * Selects the records from the database
	 *
	 * @param DatabaseRecord $cDbRecord : the instance of DatabaseRecord
	 * @param boolean $bLockRecord : if true, lock the record for updating
	 * @param boolean $bStoreResultInClass : if true, stores the query results in an array of DatabaseRecord objects otherwise store in an associative array
	 * @return array : an array of DatabaseRecord objects or an associative array (depending on $bStoreResultInClass)
	 */
	public static function select( DatabaseRecord $cDbRecord, $bLockRecord, $bStoreResultInClass = true )
	{
		return self::getInstance()->m_cDbComponent->select( $cDbRecord, $bLockRecord, $bStoreResultInClass );
	}


	/**
	 * Inserts the record to the database
	 *
	 * @param DatabaseRecord $cDbRecord : the instance of DatabaseRecord
	 * @return unsigned : if successful, return the number of affected rows
	 */
	public static function insert( DatabaseRecord $cDbRecord )
	{
		return self::getInstance()->m_cDbComponent->insert( $cDbRecord );
	}


	/**
	 * Updates the record in the database
	 *
	 * @param DatabaseRecord $cDbRecord : the instance of DatabaseRecord
	 * @return unsigned : if successful, return the number of affected rows
	 */
	public static function update( DatabaseRecord $cDbRecord )
	{
		return self::getInstance()->m_cDbComponent->update( $cDbRecord );
	}


	/**
	 * Deletes the record from the database
	 *
	 * @param DatabaseRecord $cDbRecord : the instance of DatabaseRecord
	 * @return unsigned : if successful, return the number of affected rows
	 */
	public static function delete( DatabaseRecord $cDbRecord )
	{
		return self::getInstance()->m_cDbComponent->delete( $cDbRecord );
	}


	/**
	 * Execute the custom query
	 *
	 * @param array $aFields : the array of the fields of the record
	 * @param string $szPreparedStmt : the prepared statement string
	 * @return mixed : if successful, either the return the number of rows affected
	 * 					or return an associative array of data is SELECT statement is used
	 */
	public static function customQuery( array $aFields, $szPreparedStmt )
	{
		return self::getInstance()->m_cDbComponent->customQuery( $aFields, $szPreparedStmt );
	}

}//end class


?>