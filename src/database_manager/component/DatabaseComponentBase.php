<?php

namespace OilyBird\DatabaseMgr\Component;

use OilyBird\Conf\Constants;
use OilyBird\Common\Util;
use OilyBird\DatabaseMgr\DatabaseRecord;

use PDO;
use PDOStatement;
use PDOException;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The base class that handles the database through PDO
 * @author oliver.chong
 *
 */
abstract class DatabaseComponentBase
{
	//the handle to the PDO database object
	protected $m_cDbHandle;

	//the handle to the prepared statement
	protected $m_cStmt;


	/**
	 * The constructor of DatabaseComponentBase class
	 */
	protected function __construct( $szDbSchemaName )
	{
		$this->openConn( $szDbSchemaName );
	}


	/**
	 * The destructor of DatabaseComponentBase class
	 */
	protected function __destruct()
	{
		$this->closeConn();
	}


	//added by Oliver Chong - May 8, 2015
	/**
	 * Open the database connection
	 * This should be implemented based on the database driver used (example: PDO MySQL)
	 *
	 * @param string $szDbSchemaName : the database schema name
	 */
	abstract protected function openConn( $szDbSchemaName );


	//added by Oliver Chong - May 8, 2015
	/**
	 * Close the database connection
	 */
	protected function closeConn()
	{
		$this->m_cStmt = null;
		$this->m_cDbHandle = null;
	}


	//added by Oliver Chong - May 12, 2015
	/**
	 * Select the database schema to use
	 * @param string $szDbSchemaName : the database schema name
	 */
	public function useSchema( $szDbSchemaName )
	{
		return $this->m_cDbHandle->exec( "USE $szDbSchemaName" );
	}


	//added by Oliver Chong - May 12, 2015
	/**
	 * Begins the transaction
	 * @return boolean : if successful, return true
	 */
	public function beginTransaction()
	{
		return $this->m_cDbHandle->beginTransaction();
	}


	//added by Oliver Chong - May 12, 2015
	/**
	 * Commits the transaction
	 * @return boolean : if successful, return true
	 */
	public function commit()
	{
		return $this->m_cDbHandle->commit();
	}


	//added by Oliver Chong - May 12, 2015
	/**
	 * Rolls back the transaction
	 * @return boolean : if successful, return true
	 */
	public function rollBack()
	{
		return $this->m_cDbHandle->rollBack();
	}


	//added by Oliver Chong - May 10, 2015
	/**
	 * Selects the records from the database
	 *
	 * @param DatabaseRecord $cDbRecord : the instance of DatabaseRecord
	 * @param boolean $bLockRecord : if true, lock the record for updating
	 * @param boolean $bStoreResultInClass : if true, stores the query results in an array of DatabaseRecord objects otherwise store in an associative array
	 * @return array : an array of DatabaseRecord objects or an associative array (depending on $bStoreResultInClass)
	 */
	public function select( DatabaseRecord $cDbRecord, $bLockRecord, $bStoreResultInClass = true )
	{
		$aConditionFields = $cDbRecord->getWhereCondition();
		$aConditionOperators = $cDbRecord->getWhereConditionOperators();
		$aSortFields = $cDbRecord->getOrderBy();
		$nLimit = $cDbRecord->getLimit();

		//create the prepared statement
		$szPreparedStmt = "SELECT *";
		$szPreparedStmt .= " FROM ".$cDbRecord->getTableName();
		$szPreparedStmt .= $this->generateWhereConditionStatement( $aConditionFields, $aConditionOperators );
		$szPreparedStmt .= $this->generateOrderByStatement( $aSortFields );
		if ( $nLimit )
		{
			$szPreparedStmt .= " LIMIT $nLimit";
		}
		if ( $bLockRecord )
		{
			$szPreparedStmt .= $this->generateLockRecordStatement();
		}

		if ( $bStoreResultInClass )
		{
			return $this->executeStatementFetch( $aConditionFields, $szPreparedStmt, $cDbRecord );
		}
		else
		{
			return $this->executeStatementFetch( $aConditionFields, $szPreparedStmt );
		}
	}


	//added by Oliver Chong - May 8, 2015
	/**
	 * Inserts the record to the database
	 *
	 * @param DatabaseRecord $cDbRecord : the instance of DatabaseRecord
	 * @return unsigned : if successful, return the number of affected rows
	 */
	public function insert( DatabaseRecord $cDbRecord )
	{
		$aFields = $cDbRecord->getAllFields();

		if ( !empty( $aFields ) )
		{
			//create the prepared statement
			$szPreparedStmt = "INSERT INTO ".$cDbRecord->getTableName();
			$szPreparedStmt .= "(".implode( ", ", array_keys( $aFields ) ).")";
			$szPreparedStmt .= " VALUES (:" . implode( ", :", array_keys( $aFields ) ) . ")";

			return $this->executeStatement( $aFields, $szPreparedStmt );
		}
		else
		{
			return 0;
		}
	}


	//added by Oliver Chong - May 11, 2015
	/**
	 * Updates the record in the database
	 *
	 * @param DatabaseRecord $cDbRecord : the instance of DatabaseRecord
	 * @return unsigned : if successful, return the number of affected rows
	 */
	public function update( DatabaseRecord $cDbRecord )
	{
		$aFields = $cDbRecord->getAllFields();
		$aConditionFields = $cDbRecord->getWhereCondition();
		$aConditionOperators = $cDbRecord->getWhereConditionOperators();

		if ( !empty( $aFields ) && !empty( $aConditionFields ) )
		{
			//create the prepared statement
			$szPreparedStmt = "UPDATE ".$cDbRecord->getTableName();

			$nIndex = 0;
			foreach ( $aFields as $szFieldName => $fieldValue )
			{
				$szPreparedStmt .= ( $nIndex == 0 ) ? " SET " : ", ";
				$szPreparedStmt .= $szFieldName . " = :" . $szFieldName;

				++$nIndex;
			}//end loop

			$szPreparedStmt .= $this->generateWhereConditionStatement( $aConditionFields, $aConditionOperators );

			$aFields = array_merge( $aFields, $aConditionFields );

			return $this->executeStatement( $aFields, $szPreparedStmt );
		}
		else
		{
			return 0;
		}
	}


	//added by Oliver Chong - May 11, 2015
	/**
	 * Deletes the record from the database
	 *
	 * @param DatabaseRecord $cDbRecord : the instance of DatabaseRecord
	 * @return unsigned : if successful, return the number of affected rows
	 */
	public function delete( DatabaseRecord $cDbRecord )
	{
		$aConditionFields = $cDbRecord->getWhereCondition();
		$aConditionOperators = $cDbRecord->getWhereConditionOperators();

		if ( !empty( $aConditionFields ) )
		{
			//create the prepared statement
			$szPreparedStmt = "DELETE FROM ".$cDbRecord->getTableName();
			$szPreparedStmt .= $this->generateWhereConditionStatement( $aConditionFields, $aConditionOperators );

			return $this->executeStatement( $aConditionFields, $szPreparedStmt );
		}
		else
		{
			return 0;
		}
	}


	//added by Oliver Chong - May 15, 2015
	/**
	 * Execute the custom query
	 *
	 * @param array $aFields : the array of the fields of the record
	 * @param string $szPreparedStmt : the prepared statement string
	 * @return mixed : if successful, either the return the number of rows affected
	 * 					or return an associative array of data is SELECT statement is used
	 */
	public function customQuery( array $aFields, $szPreparedStmt )
	{
		if ( strpos( $szPreparedStmt, "SELECT" ) !== false )
		{
			return $this->executeStatementFetch( $aFields, $szPreparedStmt );
		}
		else
		{
			return $this->executeStatement( $aFields, $szPreparedStmt );
		}
	}


	//added by Oliver Chong - May 10, 2015
	/**
	 * Execute the prepared statement
	 *
	 * @param array $aFields : the array of the fields of the record
	 * @param string $szPreparedStmt : the prepared statement string
	 * @return unsigned : if successful, return the number of rows affected
	 */
	protected function executeStatement( $aFields, $szPreparedStmt )
	{
		try
		{
			//create the handle to the prepared statement
			$this->m_cStmt = $this->m_cDbHandle->prepare( $szPreparedStmt );

			//var_dump( "executeStatement =====", $this->m_cStmt, $aFields );

			if ( !empty( $aFields ) )
			{
				//bind the parameters
				foreach ( $aFields as $szFieldName => $fieldValue )
				{
					//if the field contains an array of values
					if ( is_array( $fieldValue ) )
					{
						//go through the values and generate a bind parameter for each
						foreach ( $fieldValue as $key => $value )
						{
							$szNewFieldName = $szFieldName."_".$key;

							//bind the parameter
							$this->m_cStmt->bindParam( ":".$szNewFieldName, $szFieldName );

							//add the binded fields individually
							$aFields[ $szNewFieldName ] = $value;
						}//end loop

						//remove the original field with the array
						//since the PDOStatement execute function can only take arrays/objects having a single level hierarchy
						unset( $aFields[ $szFieldName ] );
					}
					else
					{
						$this->m_cStmt->bindParam( ":".$szFieldName, $szFieldName );
					}
				}//end loop

				$this->m_cStmt->execute( $aFields );
			}
			else
			{
				$this->m_cStmt->execute();
			}

			//var_dump( "executeStatement FORMATTED =====", $this->m_cStmt, $aFields );

			return $this->m_cStmt->rowCount();
		}
		catch ( PDOException $e )
		{
			echo Util::formatException( $e );
			return 0;
		}
	}


	//added by Oliver Chong - May 10, 2015
	/**
	 * Executes the statement followed by a fetch that stores the queried data in an array of the specified class (instance of DatabaseRecord)
	 * If there is no specified class (instance of DatabaseRecord is null), it will stored the queried data in an associative array instead
	 *
	 * @param array $aFields : the array of the fields of the record
	 * @param string $szPreparedStmt : the prepared statement string
	 * @param DatabaseRecord $cDbRecord : the instance of DatabaseRecord
	 * @return array : if successful, return an array of the specified class (instance of DatabaseRecord)
	 */
	protected function executeStatementFetch( $aFields, $szPreparedStmt, DatabaseRecord $cDbRecord = null )
	{
		if ( $this->executeStatement( $aFields, $szPreparedStmt ) )
		{
			if ( $cDbRecord )
			{
				//return the search results and store it in the specified class
				return $this->m_cStmt->fetchAll( PDO::FETCH_CLASS, get_class( $cDbRecord ) );
			}
			else
			{
				//return the search results and store it in an associative array
				return $this->m_cStmt->fetchAll( PDO::FETCH_ASSOC );
			}
		}
		else
		{
			return null;
		}
	}


	//added by Oliver Chong - May 11, 2015
	/**
	 * Generates the SQL WHERE/AND clause condition statement needed for query, update and delete statements
	 *
	 * @param array $aConditionFields : the array of fields that will be part of the WHERE/AND clause condition statement
	 * @return string : the SQL WHERE/AND clause condition statement needed for query, update and delete statements
	 */
	protected function generateWhereConditionStatement( &$aConditionFields, $aConditionOperators )
	{
		$szPreparedStmt = "";

		if ( !empty( $aConditionFields ) && !empty( $aConditionOperators ) )
		{
			//this will store the formatted condition fields
			$aConditionFieldsFormatted = array();

			$nIndex = 0;
			foreach ( $aConditionFields as $szFieldName => $fieldValue )
			{
				//if the field contains an array of values
				if ( is_array( $fieldValue ) )
				{
					//create the bind variables individually
					$szInCondition = ":".$szFieldName."_".implode( ", :".$szFieldName."_", array_keys( $fieldValue ) );
					switch ( $nIndex )
					{
						case 0:
							$szPreparedStmt .= " WHERE $szFieldName IN ( $szInCondition )";
							break;
						default:
							$szPreparedStmt .= " AND $szFieldName IN ( $szInCondition )";
							break;
					}

					$aConditionFieldsFormatted[ $szFieldName ] = $fieldValue;
				}
				else
				{
					//this is to support the scenario if the same field is present in both the field array and the condition field array
					$szNewFieldName = $szFieldName."_".$nIndex;

					//add the binded fields individually
					$aConditionFieldsFormatted[ $szNewFieldName ] = $fieldValue;

					//operator
					$szOperator = "=";
					//if wildcard search
					if ( strpos( $fieldValue, "%" ) !== false )
					{
						$szOperator = "LIKE";
					}
					else
					{
						$szOperator = $aConditionOperators[ $szFieldName ];
					}

					switch ( $nIndex )
					{
						case 0:
							$szPreparedStmt .= " WHERE $szFieldName $szOperator :$szNewFieldName";
							break;
						default:
							$szPreparedStmt .= " AND $szFieldName $szOperator :$szNewFieldName";
							break;
					}
				}

				++$nIndex;
			}//end loop

			$aConditionFields = $aConditionFieldsFormatted;
		}

		return $szPreparedStmt;
	}


	//added by Oliver Chong - May 11, 2015
	/**
	 * Generates the SQL ORDER BY condition statement needed for sorting
	 *
	 * @param array $aSortFields : the array of fields that will be part of the ORDER BY statement
	 * @return string : the SQL ORDER BY condition statement needed for sorting
	 */
	protected function generateOrderByStatement( $aSortFields )
	{
		$szPreparedStmt = "";

		if ( !empty( $aSortFields ) )
		{
			$nIndex = 0;
			foreach ( $aSortFields as $szFieldName => $fieldValue )
			{
				$szPreparedStmt .= ( $nIndex == 0 ) ? " ORDER BY " : ", ";
				$szPreparedStmt .= $szFieldName;
				$szPreparedStmt .= $fieldValue ? " ASC" : " DESC";

				++$nIndex;
			}//end loop
		}

		return $szPreparedStmt;
	}


	//added by Oliver Chong - May 20, 2105
	/**
	 * Generates the SQL lock statement
	 *
	 * @param boolean $bLockForUpdate : if true, use lock FOR UPDATE otherwise use LOCK IN SHARE MODE
	 * @return string : the SQL lock statement
	 */
	protected function generateLockRecordStatement( $bLockForUpdate = true )
	{
		//https://dev.mysql.com/doc/refman/5.0/en/innodb-locking-reads.html
		//http://www.xpertdeveloper.com/2011/11/row-locking-with-mysql/
		$szPreparedStmt = $bLockForUpdate ? " FOR UPDATE" : " LOCK IN SHARE MODE";

		return $szPreparedStmt;
	}

}//end class


?>