<?php

namespace OilyBird\CacheMgr;


use OilyBird\Common\ContainerBase;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * This is the cache record that can have dynamic properties on the fly
 * @author oliver.chong
 *
 */
class CacheRecord extends ContainerBase
{
	//the cache transaction type
	private $m_nTransactionType;

	//the id
	private $m_sId;

	//the expire time in seconds
	private $m_nExpireSecs;


	/**
	 * The constructor of the CacheTable class
	 * @param string $szTableName : the table name
	 * @param unsigned $nDataType : the cache data type (refer to CacheDataType)
	 * @param unsigned $nTransactionType : the cache data type (refer to CacheTranscationType)
	 * @param string $sIdOverride : the id value to override the auto-increment id value
	 */
	public function __construct( $szTableName, $nTransactionType, $sIdOverride = null )
	{
		parent::__construct( $szTableName );

		$this->m_nTransactionType = $nTransactionType;
		$this->m_sId = $sIdOverride;
	}


	/**
	 * Gets the cache transaction type
	 * @return unsigned : the cache data type (refer to CacheTranscationType)
	 */
	public function getCacheTransactionType()
	{
		return $this->m_nTransactionType;
	}


	/**
	 * Gets the id
	 * @return string : the id
	 */
	public function getId()
	{
		return $this->m_sId;
	}


	/**
	 * Gets the expiry duration in seconds
	 * @return unsigned : the expiry duration in seconds
	 */
	public function getExpireSecs()
	{
		return $this->m_nExpireSecs;
	}


	/**
	 * Sets the expiry duration in seconds
	 * @param unsigned $nExpireSecs : the expiry duration in seconds
	 */
	public function setExpireSecs( $nExpireSecs )
	{
		$this->m_nExpireSecs = $nExpireSecs;
	}

}//end class


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>