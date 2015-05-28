<?php

namespace OilyBird\Common;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * This is the ContainerBase class
 * @author oliver.chong
 *
 */
abstract class ContainerBase
{
	//the array of properties that pertains to fields in a table
	protected  $m_aFields;

	//the table name
	protected $m_szTableName;


	/**
	 * The constructor of the DatabaseRecord class
	 * @param string $szTableName : the table name
	 */
	protected function __construct( $szTableName )
	{
		$this->m_szTableName = $szTableName;
	}


	/**
	 * Gets the table name
	 * @return string : the table name
	 */
	public function getTableName()
	{
		return $this->m_szTableName;
	}


	/**
	 * This will be store the non-existing/inaccessible property into the array
	 * @param string|unsigned $key : the key
	 * @param mixed $value : the value to be stored
	 */
	public function __set( $key, $value )
	{
		$this->m_aFields[ $key ] = $value;
	}


	/**
	 * Gets the value of the non-existing/inaccessible property
	 * @param string|unsigned $key : the key
	 * @return mixed : the stored value
	 */
	public function __get( $key )
	{
		return $this->m_aFields[ $key ];
	}


	/**
	 * Gets the list of all the fields (name and value) of this object
	 * @return array : the array of fields
	 */
	public function getAllFields()
	{
		return $this->m_aFields;
	}


	/**
	 * Adds a field (key-value pair) to this record
	 * @param string $szKey : the key that will serve as the field name
	 * @param mixed $value : the field value
	 */
	public function setField( $szKey, $value )
	{
		$this->m_aFields[ $szKey ] = $value;
	}


	/**
	 * Gets the field value of this record
	 * @param string $szKey : the key that will serve as the field name
	 * return mixed : the field value
	 */
	public function getField( $szKey )
	{
		return $this->m_aFields[ $szKey ];
	}

}//end class


?>