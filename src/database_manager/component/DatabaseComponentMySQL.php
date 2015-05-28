<?php

namespace OilyBird\DatabaseMgr\Component;

use OilyBird\Conf\Config;
use OilyBird\Common\Util;
use OilyBird\DatabaseMgr\DatabaseRecord;

use PDO;
use PDOStatement;
use PDOException;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The class that handles the MySQL database through PDO
 * @author oliver.chong
 *
 */
class DatabaseComponentMySQL extends DatabaseComponentBase
{
	/**
	 * The constructor of DatabaseComponentMySQL class
	 */
	public function __construct( $szDbSchemaName )
	{
		parent::__construct( $szDbSchemaName );
	}


	/**
	 * The destructor of DatabaseComponentMySQL class
	 */
	public function __destruct()
	{
		parent::__destruct();
	}


	//added by Oliver Chong - May 8, 2015
	/**
	 * (non-PHPdoc)
	 * @see OilyBird\DatabaseMgr.DatabaseComponentBase::openConn()
	 */
	protected function openConn( $szDbSchemaName )
	{
		try
		{
			//datasource name
			$szDsn = "mysql:host=".Config::getDatabaseIP().";port=".Config::getDatabasePort().";dbname=".$szDbSchemaName;

			$aOptions = array(
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
			);

			//instantiate the handle to MySQL through PDO
			$this->m_cDbHandle = new PDO( $szDsn, Config::getDatabaseUsername(), Config::getDatabasePassword() );

			//set the error mode
			$this->m_cDbHandle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			//http://stackoverflow.com/questions/10113562/pdo-mysql-use-pdoattr-emulate-prepares-or-not
			$this->m_cDbHandle->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
		}
		catch ( PDOException $e )
		{
			echo Util::formatException( $e );
		}
	}

}//end class


?>