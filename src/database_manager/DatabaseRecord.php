<?php

namespace OilyBird\DatabaseMgr;


use OilyBird\Common\ContainerBase;
use OilyBird\DatabaseMgr\Constants\DatabaseSqlOperators;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * This is the DatabaseRecord class
 * @author oliver.chong
 *
 */
abstract class DatabaseRecord extends ContainerBase
{
	//the array of fields that will be used as conditions (WHERE, AND) in query, update and delete statements
	protected $m_aConditionFields;

	//the comparison operators that will be used as conditions (WHERE, AND) in query, update and delete statements
	protected $m_aConditionOperators;

	//the array of fields that will determine the sort order
	protected $m_aSortFields;

	//the limit to the number of records fetched
	protected $m_nLimit;


	/**
	 * The constructor of the DatabaseRecord class
	 * @param string $szTableName : the table name
	 */
	protected function __construct( $szTableName )
	{
		parent::__construct( $szTableName );
	}


	/**
	 * Gets the list of all the condition fields (name and value) of this object
	 * @return array : the array of condition fields needed for query, update and delete statements
	 */
	public function getWhereCondition()
	{
		return $this->m_aConditionFields;
	}


	/**
	 * Gets the list of all the comparison operators of this object
	 * @return array : the array of comparison operators needed for query, update and delete statements
	 */
	public function getWhereConditionOperators()
	{
		return $this->m_aConditionOperators;
	}


	/**
	 * Adds a condition field (key-value pair) to this record needed for query, update and delete statements
	 * @param string $szKey : the key that will serve as the field name
	 * @param mixed $value : the field value
	 * @param string $szComparisonOperator : the SQL comparison operator
	 */
	public function whereCondition( $szKey, $value, $szComparisonOperator = DatabaseSqlOperators::EQUAL )
	{
		$this->m_aConditionFields[ $szKey ] = $value;
		$this->m_aConditionOperators[ $szKey ] = $szComparisonOperator;
	}


	/**
	 * Gets the list of all the sort fields (name and value) of this object
	 * @return array : the array of sort fields for sorting
	 */
	public function getOrderBy()
	{
		return $this->m_aSortFields;
	}


	/**
	 * Adds a sort field (key-value pair) to this record needed for sorting in ascending order
	 * @param string $szKey : the key that will serve as the field name
	 * @param mixed $value : the field value
	 */
	public function orderByAsc( $szKey )
	{
		$this->m_aSortFields[ $szKey ] = true;
	}


	/**
	 * Adds a sort field (key-value pair) to this record needed for sorting in descending order
	 * @param string $szKey : the key that will serve as the field name
	 * @param mixed $value : the field value
	 */
	public function orderByDesc( $szKey )
	{
		$this->m_aSortFields[ $szKey ] = false;
	}


	/**
	 * Gets the record limit that will control the maximum number of records being fetched
	 * @return unsigned : the limit to the maximum number of records being fetched
	 */
	public function getLimit()
	{
		return $this->m_nLimit;
	}


	/**
	 * Limits the maximum number of records being fetched
	 * @param unsigned $nLimitRecordCnt : the limit to the maximum number of records being fetched
	 */
	public function limit( $nLimitRecordCnt )
	{
		$this->m_nLimit = $nLimitRecordCnt;
	}

}//end class


?>