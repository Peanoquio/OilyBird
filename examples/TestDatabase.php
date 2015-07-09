<?php

use OilyBird\DatabaseMgr\Constants\DatabaseSqlOperators;

use OilyBird\Conf\Config;
use OilyBird\Common\Util;
use OilyBird\DatabaseMgr\DatabaseManager;
use OilyBird\DatabaseMgr\DatabaseRecord;
use OilyBird\DatabaseMgr\Component\DatabaseComponentMySQL;


require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * This provides examples on how to use the database manager to store, update, delete and retrieve values from the database
 * @author oliver.chong
 *
 */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * The PlayerRecord class
 * @author oliver.chong
 *
 */
class PlayerRecord extends DatabaseRecord
{
	public function __construct()
	{
		parent::__construct( "player" );
	}


	public function getColumnName()
	{
		return "Name";
	}


	public function getColumnAge()
	{
		return "Age";
	}


	public function getName()
	{
		return $this->m_aFields[ $this->getColumnName() ];
	}


	public function setName( $szName )
	{
		$this->m_aFields[ $this->getColumnName() ] = $szName;
	}


	public function getAge()
	{
		return $this->m_aFields[ $this->getColumnAge() ];
	}


	public function setAge( $nAge )
	{
		$this->m_aFields[ $this->getColumnAge() ] = $nAge;
	}

}//end class


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//sample usage of SQL transaction
/**
 * Process the SQL transaction
 */
function ProcessTransaction()
{
	echo "<span style='color:green;'>";
	echo "<br>===============================================================================";
	echo "<br>INITIALIZE DATABASE CONNECTION";
	echo "<br>===============================================================================";
	echo "</span>";

	//INITIALIZE DATABASE CONNECTION
	DatabaseManager::initialize( new DatabaseComponentMySQL( Config::getDatabaseDefaultSchema() ) );

	//SELECT DB SCHEMA
	DatabaseManager::useSchema( Config::getDatabaseDefaultSchema() );

	try
	{
		echo "<span style='color:green;'>";
		echo "<br>===============================================================================";
		echo "<br>BEGIN TRANSACTION";
		echo "<br>===============================================================================";
		echo "</span>";

		//BEGIN TRANSACTION
		DatabaseManager::beginTransaction();

		echo "<span style='color:green;'>";
		echo "<br>===============================================================================";
		echo "<br>INSERT";
		echo "<br>===============================================================================";
		echo "</span>";

		//INSERT
		$cPlayerRecord = new PlayerRecord();
		$cPlayerRecord->Name = "player6";
		//$cPlayerRecord->setField( "Name", "player4" );
		//$cPlayerRecord->setName( "player4" );
		$cPlayerRecord->setField( "Age", 5 );

		$nAffectedRows = DatabaseManager::insert( $cPlayerRecord );
		var_dump( "INSERT nAffectedRows-----", $nAffectedRows );


		echo "<span style='color:green;'>";
		echo "<br>===============================================================================";
		echo "<br>UPDATE";
		echo "<br>===============================================================================";
		echo "</span>";

		//UPDATE
		$cPlayerRecord = new PlayerRecord();
		$cPlayerRecord->setName( "player3" );
		$cPlayerRecord->setField( "Age", 5555 );
		$cPlayerRecord->whereCondition( "Age", 5 );
		$cPlayerRecord->whereCondition( "Id", 6 );

		$nAffectedRows = DatabaseManager::update( $cPlayerRecord );
		var_dump( "UPDATE nAffectedRows-----", $nAffectedRows );


		echo "<span style='color:green;'>";
		echo "<br>===============================================================================";
		echo "<br>DELETE";
		echo "<br>===============================================================================";
		echo "</span>";

		//DELETE
		$cPlayerRecord = new PlayerRecord();
		$cPlayerRecord->whereCondition( "Name", "player4" );

		$nAffectedRows = DatabaseManager::delete( $cPlayerRecord );
		var_dump( "DELETE nAffectedRows-----", $nAffectedRows );


		echo "<span style='color:green;'>";
		echo "<br>===============================================================================";
		echo "<br>SELECT";
		echo "<br>===============================================================================";
		echo "</span>";

		//SELECT
		$cPlayerRecord = new PlayerRecord();
		//$cPlayerRecord->whereCondition( $cPlayerRecord->getColumnName(), array( "player1", "player2" ) );
		$cPlayerRecord->whereCondition( $cPlayerRecord->getColumnName(), "player%" );
		$cPlayerRecord->whereCondition( $cPlayerRecord->getColumnAge(), array( 5, 55 ) );
		$cPlayerRecord->whereCondition( "Id", 10, DatabaseSqlOperators::LESSER_AND_EQUAL );
		$cPlayerRecord->orderByAsc( $cPlayerRecord->getColumnName() );
		$cPlayerRecord->orderByDesc( $cPlayerRecord->getColumnAge() );
		$cPlayerRecord->limit( 10 );

		$aQueryResult = DatabaseManager::select( $cPlayerRecord, true );
		var_dump( "SELECT -----", $aQueryResult );


		echo "<span style='color:green;'>";
		echo "<br>===============================================================================";
		echo "<br>CUSTOM QUERY";
		echo "<br>===============================================================================";
		echo "</span>";

		//CUSTOM QUERY
		$aQueryResult = DatabaseManager::customQuery( array( "Name_0"=>"player3", "Age_0"=>5, "Age_1"=>5555 ),
				"SELECT * FROM player WHERE Name = :Name_0 AND Age IN ( :Age_0, :Age_1 ) ORDER BY Name ASC, Age DESC" );
		var_dump( "CUSTOM SELECT -----", $aQueryResult );

		//LIKE condition
		$aQueryResult = DatabaseManager::customQuery( array( "Name_0"=>"player%", "Age_0"=>5, "Age_1"=>5555 ),
				"SELECT * FROM player WHERE Name LIKE :Name_0 AND Age IN ( :Age_0, :Age_1 ) ORDER BY Name ASC, Age DESC" );
		var_dump( "CUSTOM SELECT USING LIKE-----", $aQueryResult );

		$nAffectedRows = DatabaseManager::customQuery( array( "Name"=>"player3", "Age"=>5, "Age_0"=>555 ),
				"UPDATE player SET Name = :Name, Age = :Age WHERE Age = :Age_0" );
		var_dump( "CUSTOM UPDATE -----", $nAffectedRows );


		echo "<span style='color:green;'>";
		echo "<br>===============================================================================";
		echo "<br>COMMIT";
		echo "<br>===============================================================================";
		echo "</span>";

		//COMMIT
		DatabaseManager::commit();
	}
	catch ( PDOException $e )
	{
		echo Util::formatException( $e );

		echo "<span style='color:green;'>";
		echo "<br>===============================================================================";
		echo "<br>ROLLBACK";
		echo "<br>===============================================================================";
		echo "</span>";

		//ROLLBACK
		DatabaseManager::rollBack();
	}
}

//execute the transaction
ProcessTransaction();

?>