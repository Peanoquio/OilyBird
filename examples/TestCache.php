<?php


use OilyBird\CacheMgr\Constants\CacheDataType;
use OilyBird\CacheMgr\Constants\CacheTransactionType;
use OilyBird\CacheMgr\Component\CacheComponentRedis;
use OilyBird\CacheMgr\CacheManager;
use OilyBird\CacheMgr\CacheRecord;


require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * This provides examples on how to use the cache manager to store, update, delete and retrieve values (through pipeline and transactions)
 * @author oliver.chong
 *
 */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


// INIT

echo "<span style='color:green;'>";
echo "<br>===============================================================================";
echo "<br>INIT";
echo "<br>===============================================================================";
echo "</span>";


//init
$cCache = new CacheComponentRedis( "", null, true );
CacheManager::initialize( $cCache );

echo "<br>===============================================================================";
echo "<br> Cache Database Count <br>";
var_dump( CacheManager::getMaxDatabaseCount() );

//clear all the data from the cache
CacheManager::clearData( true );

//select the database in the cache
CacheManager::selectDatabase( 5 );

echo "<br>===============================================================================";
echo "<br> Current Cache Database <br>";
var_dump( CacheManager::getCurrentDatabase() );


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// PUB / SUB

/*
echo "<span style='color:green;'>";
echo "<br>===============================================================================";
echo "<br>PUB / SUB";
echo "<br>===============================================================================";
echo "</span>";

echo "<br>===============================================================================";
echo "<br> Pub / Sub <br>";

var_dump( "subscribe to channel...", $cCache->subscribe( Constants::$CHANNELS["CHAT"] ) );
var_dump( "subscribe to channel...", $cCache->subscribe( Constants::$CHANNELS["GAME"] ) );

var_dump( "publish message to channel...", $cCache->publish( Constants::$CHANNELS["CHAT"], "hi" ) );
var_dump( "publish message to channel...", $cCache->publish( Constants::$CHANNELS["GAME"], "hello" ) );
var_dump( "publish message to channel...", $cCache->publish( Constants::$CHANNELS["GAME"], "hey there" ) );
var_dump( "publish message to channel...", $cCache->publish( Constants::$CHANNELS["GAME"], "ooseno" ) );
*/


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// sorted sets

echo "<span style='color:green;'>";
echo "<br>===============================================================================";
echo "<br>SORTED SETS";
echo "<br>===============================================================================";
echo "</span>";


echo "<br>===============================================================================";
echo "<br> Add to the sorted set <br>";

//add to the sorted set
var_dump( CacheManager::upsertSortedSet( "sortedset_ranking_1", array( "a" => 100, "b" => 50, "c" => 75, "d" => 50 ), 60,
								"sortedset_ranking_2", array( "x" => 100, "y" => 50, "z" => 75, "d" => 150 ), 60 ) );

//update the sorted set
var_dump( CacheManager::upsertSortedSet( "sortedset_ranking_1", array( "a" => 200, "b" => 250, "e" => 220 ), 60 ) );


echo "<br>===============================================================================";
echo "<br> Query from the sorted set <br>";

var_dump( CacheManager::getKeysAndValues( "*sortedset*" ) );


echo "<br>===============================================================================";
echo "<br> Remove from the sorted set <br>";

//remove from the sorted set
var_dump( CacheManager::removeFromSortedSet( "sortedset_ranking_2", array( "y", "z" ) ) );

var_dump( CacheManager::removeFromSortedSetByIndex( "sortedset_ranking_1", 1, 2 ) );


echo "<br>===============================================================================";
echo "<br> Query from the sorted set <br>";

var_dump( CacheManager::getKeysAndValues( "sortedset_ranking_1", CacheDataType::SORTED_SET ) );
var_dump( CacheManager::getKeysAndValues( "sortedset_ranking_2", CacheDataType::SORTED_SET ) );


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// lists

echo "<span style='color:green;'>";
echo "<br>===============================================================================";
echo "<br>LISTS";
echo "<br>===============================================================================";
echo "</span>";


echo "<br>===============================================================================";
echo "<br> Add to the list <br>";

//add to the list
var_dump( CacheManager::addToList( "list1", array( "a", "b", "c" ), 5, 60, true,
							"list2", array( "a", "b", "c" ), 5, 60, false,
							"list3", array( "x", "y", "z" ), 4, 30, true ) );

var_dump( CacheManager::addToList( "list1", array( "4", "5", "6" ), 5, 60, true,
							"list2", array( "1", "2", "3" ), 5, 60, false,
							"list3", array( "11", "22", "33" ), 4, 30, true ) );


echo "<br>===============================================================================";
echo "<br> Remove from the list <br>";

//remove from the list
var_dump( CacheManager::popFromList( "list1", 2, true, "list3", 2, false ) );


echo "<br>===============================================================================";
echo "<br> Query from the list <br>";

var_dump( CacheManager::getKeysAndValues( "*list*" ) );


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


echo "<span style='color:green;'>";
echo "<br>===============================================================================";
echo "<br>HASH SETS";
echo "<br>===============================================================================";
echo "</span>";


echo "<br>===============================================================================";
echo "<br> Create tables <br>";

//player table (with the id value override)
$cPlayerRecord = new CacheRecord( "player_table", CacheTransactionType::INSERT/*, "123"*/ );
$cPlayerRecord->name = "Random Dude";
$cPlayerRecord->gender = "M";
$cPlayerRecord->age = 21;
$cPlayerRecord->setExpireSecs( 30 );

$cPlayerRecord2 = new CacheRecord( "player_table", CacheTransactionType::INSERT/*, "321"*/ );
$cPlayerRecord2->name = "Hot Chick";
$cPlayerRecord2->gender = "F";
$cPlayerRecord2->age = 18;

//kingdom table
$cKingdomGiRecord = new CacheRecord( "kingdom_table", CacheTransactionType::INSERT );
$cKingdomGiRecord->name = "魏";
$cKingdomGoRecord = new CacheRecord( "kingdom_table", CacheTransactionType::INSERT );
$cKingdomGoRecord->name = "呉";
$cKingdomShokuRecord = new CacheRecord( "kingdom_table", CacheTransactionType::INSERT );
$cKingdomShokuRecord->name = "蜀";

//create the tables
CacheManager::processRecords( array( $cPlayerRecord, $cPlayerRecord2, $cKingdomGiRecord, $cKingdomGoRecord, $cKingdomShokuRecord ), CacheTransactionType::MODE_TRANSACTION );

//add general 1
$cGeneralRecord1 = new CacheRecord( "general_table", CacheTransactionType::INSERT );
$cGeneralRecord1->player_id = $cPlayerRecord->getId();
$cGeneralRecord1->kingdom_id = 1;
$cGeneralRecord1->name = "張遼";
$cGeneralRecord1->attack = 150;
$cGeneralRecord1->defense = 130;

//add general 2
$cGeneralRecord2 = new CacheRecord( "general_table", CacheTransactionType::INSERT );
$cGeneralRecord2->player_id = $cPlayerRecord->getId();
$cGeneralRecord2->kingdom_id = 3;
$cGeneralRecord2->name = "関羽";
$cGeneralRecord2->attack = 160;
$cGeneralRecord2->defense = 150;

//perform the transaction
var_dump( CacheManager::processRecords( array( $cGeneralRecord1, $cGeneralRecord2 ), CacheTransactionType::MODE_TRANSACTION ) );


echo "<br>===============================================================================";
echo "<br>Query from tables <br>";

//retrieve values
var_dump( CacheManager::getKeys( "*table*" ) );
var_dump( CacheManager::getKeysAndTypes( "*table*" ) );
var_dump( CacheManager::getKeysAndValues( "*table*" ) );

/*
var_dump( CacheManager::get( "player_table:".$cPlayerRecord->getId() ) );
var_dump( CacheManager::get( "kingdom_table:1" ) );
var_dump( CacheManager::get( "kingdom_table:2" ) );
var_dump( CacheManager::get( "kingdom_table:3" ) );
var_dump( CacheManager::get( "general_table:1" ) );
var_dump( CacheManager::get( "general_table:2" ) );
*/


echo "<br>===============================================================================";
echo "<br> Update and delete records from tables <br>";

//update
$cPlayerRecord = new CacheRecord( "player_table", CacheTransactionType::UPDATE, "3" );
$cPlayerRecord->name = "The Man";

//delete
$cGeneralRecord2 = new CacheRecord( "general_table", CacheTransactionType::DELETE, "2" );

//perform the transaction
var_dump( CacheManager::processRecords( array( $cPlayerRecord, $cGeneralRecord2 ), CacheTransactionType::MODE_TRANSACTION ) );


echo "<br>===============================================================================";
echo "<br>Query from tables <br>";

var_dump( CacheManager::getKeysAndValues( "*table*" ) );


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


echo "<span style='color:green;'>";
echo "<br>===============================================================================";
echo "<br>STRINGS AND NUMBERS";
echo "<br>===============================================================================";
echo "</span>";

echo "<br>===============================================================================";
echo "<br> set and get (can store/retrieve number/string/array/object <br>";

//var_dump( CacheManager::set( "key", "test", 2 ) );
//var_dump( CacheManager::set( "key", array( "a"=>"test1", "b"=>"test2", "c"=>"test3" ), 2 ) );
var_dump( CacheManager::set( "key", $cPlayerRecord, 2 ) );
var_dump( CacheManager::exists( "key" ) );
var_dump( CacheManager::get( "key" ) );


echo "<br>===============================================================================";
echo "<br> multiple set and get (does not work when in cluster mode) <br>";

$aArray = array( "1a" => "a", "2b" => "b", "3c" => "c" );
var_dump( CacheManager::setMultiple( $aArray, 2 ) );
var_dump( CacheManager::getMultiple( array_keys( $aArray ) ) );


echo "<br>===============================================================================";
echo "<br> counter <br>";

CacheManager::delete( "counter" );
var_dump( CacheManager::incr( "counter", 10 ) );
var_dump( CacheManager::decr( "counter" ) );


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//PERFORMANCE TEST (pipeline transaction)

/*
 $fCallback = function( Predis\Pipeline\Pipeline $pipe, $key, $value )
 {
$pipe->flushdb();

$start = time();

for ( $i = 0; $i < 10; ++$i )
{
$pipe->set( $key, $value );
$pipe->set( "hi", "hello" );
$pipe->mget( array( $key, "hi" ) );
$pipe->incrby('counter', 10);
$pipe->incrby('counter', 30);
$pipe->exists('counter');
$pipe->exists('nothing');
}//end loop

$end = time();
$duration = $end - $start;

var_dump( $start, $end );
var_dump( "------------ CALLBACK duration : ", $duration );
};


var_dump( is_callable( $fCallback ), $fCallback );

$cCache->pipeline( true, $fCallback, array( "test", 123 ) );


$start = time();

$key = "test";
$value = 123;

for ( $i = 0; $i < 100; ++$i )
{
	$cCache->set( $key, $value );
	$cCache->set( "hi", "hello" );
	$cCache->getMultiple( array( $key, "hi" ) );
	$cCache->incr('counter', 10);
	$cCache->incr('counter', 30);
	$cCache->exists('counter');
	$cCache->exists('nothing');
}//end loop

$end = time();
$duration = $end - $start;

var_dump( $start, $end );
var_dump( "------------ duration : ", $duration );
*/

?>