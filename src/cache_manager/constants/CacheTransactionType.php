<?php

namespace OilyBird\CacheMgr\Constants;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * This is the enumeration for the supported transaction types in the cache
 * @author oliver.chong
 *
 */
abstract class CacheTransactionType
{
	const SELECT = 1;
	const INSERT = 2;
	const UPDATE = 3;
	const DELETE = 4;

	const MODE_PIPELINE = 998;
	const MODE_TRANSACTION = 999;
}

?>